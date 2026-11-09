import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sipdt_mobile/core/api_client.dart';
import 'package:sipdt_mobile/core/local_store.dart';
import 'package:sipdt_mobile/core/models.dart';
import 'package:sipdt_mobile/core/sync_service.dart';

/// Fake ApiClient yang hanya meng-override pushDrafts dan
/// fetchBatchStatuses — yang dipakai SyncService.
class _FakeApiClient extends ApiClient {
  _FakeApiClient(LocalStore store) : super(store: store);

  /// Respons untuk pushDrafts(): list per-local_id dengan status dll.
  List<Map<String, dynamic>> pushDraftsResult = const [];

  /// Jika diisi, pushDrafts() akan throw exception ini.
  Object? pushDraftsError;

  /// Respons untuk fetchBatchStatuses(): map dari batch_id ke info.
  Map<int, Map<String, dynamic>> batchStatusesResult = const {};
  final List<List<String>> pushedGroups = [];

  @override
  Future<List<Map<String, dynamic>>> pushDrafts(List<DraftRecord> drafts) async {
    if (pushDraftsError != null) throw pushDraftsError!;
    pushedGroups.add(drafts.map((draft) => draft.localId).toList());
    return pushDraftsResult.where((result) => drafts.any((draft) => draft.localId == result['local_id'])).toList();
  }

  @override
  Future<Map<int, Map<String, dynamic>>> fetchBatchStatuses(Set<int> batchIds) async {
    return batchStatusesResult;
  }
}

DraftRecord _mkDraft(String id, {String state = 'queued', int? batchId, DraftType type = DraftType.rumah}) => DraftRecord(
      localId: id,
      operationId: 'op-$id',
      type: type,
      title: 'Draft $id',
      payload: const {'kode_rumah': 'X'},
      syncState: state,
      createdAt: DateTime(2026, 1, 1),
      batchId: batchId,
    );

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  late LocalStore store;
  late _FakeApiClient api;
  late SyncService svc;

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    store = LocalStore();
    await store.init();
    api = _FakeApiClient(store);
    svc = SyncService(api: api, store: store);
  });

  group('getPendingDrafts', () {
    test('mencakup queued, failed, pending; mengecualikan accepted & conflict', () async {
      await store.saveDraft(_mkDraft('a', state: 'queued'));
      await store.saveDraft(_mkDraft('b', state: 'pending'));
      await store.saveDraft(_mkDraft('c', state: 'failed'));
      await store.saveDraft(_mkDraft('d', state: 'accepted'));
      await store.saveDraft(_mkDraft('e', state: 'conflict'));
      final pending = svc.getPendingDrafts();
      final ids = pending.map((d) => d.localId).toSet();
      expect(ids, {'a', 'b', 'c'});
    });
  });

  group('syncAll', () {
    test('return SyncResult(0,0) jika tidak ada draft', () async {
      final result = await svc.syncAll();
      expect(result.accepted, 0);
      expect(result.failed, 0);
    });

    test('status accepted dari server -> draft jadi accepted, counter naik', () async {
      await store.saveDraft(_mkDraft('a'));
      api.pushDraftsResult = [
        {'local_id': 'a', 'status': 'accepted', 'server_id': 10, 'batch_id': 1, 'batch_status': 'disetujui'}
      ];
      final result = await svc.syncAll();
      expect(result.accepted, 1);
      expect(result.failed, 0);
      final after = store.drafts().firstWhere((d) => d.localId == 'a');
      expect(after.syncState, 'accepted');
      expect(after.serverId, 10);
      expect(after.batchId, 1);
    });

    test('status failed dari server -> draft jadi failed + errorMessage', () async {
      await store.saveDraft(_mkDraft('a'));
      api.pushDraftsResult = [
        {'local_id': 'a', 'status': 'failed', 'message': 'NIK duplikat'}
      ];
      final result = await svc.syncAll();
      expect(result.accepted, 0);
      expect(result.failed, 1);
      final after = store.drafts().firstWhere((d) => d.localId == 'a');
      expect(after.syncState, 'failed');
      expect(after.errorMessage, 'NIK duplikat');
    });

    test('mixed: status accepted dan pending dihitung, gagal tetap dihitung', () async {
      await store.saveDraft(_mkDraft('a'));
      await store.saveDraft(_mkDraft('b'));
      await store.saveDraft(_mkDraft('c'));
      api.pushDraftsResult = [
        {'local_id': 'a', 'status': 'accepted', 'server_id': 1},
        {'local_id': 'b', 'status': 'failed', 'message': 'invalid'},
        {'local_id': 'c', 'status': 'pending', 'batch_id': 5},
      ];
      final result = await svc.syncAll();
      expect(result.accepted, 2);
      expect(result.failed, 1);
      expect(store.drafts().firstWhere((d) => d.localId == 'a').syncState, 'accepted');
      expect(store.drafts().firstWhere((d) => d.localId == 'b').syncState, 'failed');
      expect(store.drafts().firstWhere((d) => d.localId == 'c').syncState, 'pending');
    });

    test('mengirim rumah dan infrastruktur tanpa mengirim draft keluarga', () async {
      await store.saveDraft(DraftRecord(
        localId: 'rumah-1', type: DraftType.rumah, title: 'Rumah',
        payload: const {'desa_id': 3, 'kode_rumah': 'R-1'}, syncState: 'queued', createdAt: DateTime(2026),
      ));
      await store.saveDraft(DraftRecord(
        localId: 'infra-1', type: DraftType.infrastruktur, title: 'Jembatan',
        payload: const {'desa_id': 3, 'jenis': 'jembatan'}, syncState: 'queued', createdAt: DateTime(2026),
      ));
      await store.saveDraft(DraftRecord(
        localId: 'kk-1', type: DraftType.keluarga, title: 'KK lama',
        payload: const {}, syncState: 'queued', createdAt: DateTime(2026),
      ));
      api.pushDraftsResult = [
        {'local_id': 'rumah-1', 'status': 'pending', 'server_id': 101},
        {'local_id': 'infra-1', 'status': 'pending', 'server_id': 202},
      ];

      await svc.syncAll();

      expect(api.pushedGroups, [['rumah-1'], ['infra-1']]);
      expect(store.drafts().firstWhere((draft) => draft.localId == 'kk-1').syncState, 'queued');
    });

    test('pushDrafts throw -> exception propagate, draft TIDAK ditandai failed (regression #4)', () async {
      await store.saveDraft(_mkDraft('a', state: 'queued'));
      await store.saveDraft(_mkDraft('b', state: 'queued'));
      api.pushDraftsError = Exception('SocketException: network unreachable');
      expect(
        () => svc.syncAll(),
        throwsA(isA<Exception>()),
      );
      // State draft tidak boleh ter-overwrite ke 'failed'
      final list = store.drafts();
      for (final d in list) {
        expect(d.syncState, 'queued', reason: '${d.localId} seharusnya tetap queued saat network error');
        expect(d.errorMessage, null);
      }
    });
  });

  group('refreshBatchStatuses', () {
    test('batch disetujui -> draft pending jadi accepted', () async {
      await store.saveDraft(_mkDraft('a', state: 'pending', batchId: 1));
      api.batchStatusesResult = {
        1: {'id': 1, 'status': 'disetujui', 'status_label': 'Disetujui'},
      };
      await svc.refreshBatchStatuses();
      final after = store.drafts().firstWhere((d) => d.localId == 'a');
      expect(after.syncState, 'accepted');
      expect(after.batchStatus, 'disetujui');
    });

    test('batch ditolak -> draft pending jadi failed dengan pesan', () async {
      await store.saveDraft(_mkDraft('a', state: 'pending', batchId: 1));
      api.batchStatusesResult = {
        1: {'id': 1, 'status': 'ditolak', 'status_label': 'Ditolak'},
      };
      await svc.refreshBatchStatuses();
      final after = store.drafts().firstWhere((d) => d.localId == 'a');
      expect(after.syncState, 'failed');
      expect(after.errorMessage, contains('ditolak'));
    });

    test('hanya draft dengan batchId dan syncState=pending yang di-refresh', () async {
      await store.saveDraft(_mkDraft('queued-no-batch', state: 'queued'));
      await store.saveDraft(_mkDraft('accepted-with-batch', state: 'accepted', batchId: 1));
      await store.saveDraft(_mkDraft('pending-with-batch', state: 'pending', batchId: 1));
      api.batchStatusesResult = {
        1: {'id': 1, 'status': 'disetujui', 'status_label': 'Disetujui'},
      };
      await svc.refreshBatchStatuses();
      // Yang punya batchId + pending jadi accepted
      final after = store.drafts().firstWhere((d) => d.localId == 'pending-with-batch');
      expect(after.syncState, 'accepted');
      // Yang accepted tidak berubah
      final accepted = store.drafts().firstWhere((d) => d.localId == 'accepted-with-batch');
      expect(accepted.syncState, 'accepted');
      // Yang queued tidak berubah
      final queued = store.drafts().firstWhere((d) => d.localId == 'queued-no-batch');
      expect(queued.syncState, 'queued');
    });
  });

  group('getStats', () {
    test('menghitung per state dan per type', () async {
      await store.saveDraft(_mkDraft('1', state: 'queued'));
      await store.saveDraft(_mkDraft('2', state: 'pending'));
      await store.saveDraft(_mkDraft('3', state: 'accepted'));
      await store.saveDraft(_mkDraft('4', state: 'failed'));
      // Draft lama KK/Penduduk tidak ikut statistik pengiriman mobile.
      await store.saveDraft(DraftRecord(
        localId: 'k1', type: DraftType.keluarga, title: 'KK',
        payload: const {}, syncState: 'queued',
        createdAt: DateTime(2026, 1, 1),
      ));
      await store.saveDraft(DraftRecord(
        localId: 'p1', type: DraftType.penduduk, title: 'Penduduk',
        payload: const {}, syncState: 'queued',
        createdAt: DateTime(2026, 1, 1),
      ));
      final stats = svc.getStats();
      expect(stats['total'], 4);
      expect(stats['queued'], 1);
      expect(stats['pending'], 1);
      expect(stats['accepted'], 1);
      expect(stats['failed'], 1);
      expect(stats['rumah'], 4);
      expect(stats['infrastruktur'], 0);
    });
  });
}
