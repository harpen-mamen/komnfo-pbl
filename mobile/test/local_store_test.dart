import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sipdt_mobile/core/local_store.dart';
import 'package:sipdt_mobile/core/models.dart';

DraftRecord _mkDraft(String id, {String state = 'queued'}) => DraftRecord(
      localId: id,
      type: DraftType.rumah,
      title: 'Draft $id',
      payload: {'kode_rumah': id},
      syncState: state,
      createdAt: DateTime(2026, 1, 1),
    );

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() async {
    // Mock SharedPreferences agar setiap test punya state bersih.
    SharedPreferences.setMockInitialValues({});
  });

  test('saveDraft lalu drafts() mengembalikan draft yang sama', () async {
    final store = LocalStore();
    await store.init();
    final d = _mkDraft('d1');
    await store.saveDraft(d);
    final list = store.drafts();
    expect(list, hasLength(1));
    expect(list.first.localId, 'd1');
    expect(list.first.title, 'Draft d1');
  });

  test('saveDraft dengan localId sama akan replace, bukan duplicate', () async {
    final store = LocalStore();
    await store.init();
    await store.saveDraft(_mkDraft('d1', state: 'queued'));
    await store.saveDraft(_mkDraft('d1', state: 'failed'));
    final list = store.drafts();
    expect(list, hasLength(1));
    expect(list.first.syncState, 'failed');
  });

  test('removeDraft menghapus draft berdasarkan localId', () async {
    final store = LocalStore();
    await store.init();
    await store.saveDraft(_mkDraft('d1'));
    await store.saveDraft(_mkDraft('d2'));
    await store.saveDraft(_mkDraft('d3'));
    expect(store.drafts(), hasLength(3));
    await store.removeDraft('d2');
    final ids = store.drafts().map((d) => d.localId).toList();
    expect(ids, ['d3', 'd1']); // d2 hilang, urutan insert-0
  });

  test('drafts() membuang entry dengan JSON corrupt dan localId kosong', () async {
    SharedPreferences.setMockInitialValues({
      'sipdt.drafts': [
        '{"local_id":"good","type":"rumah","title":"t","payload":{},"sync_state":"queued","created_at":"2026-01-01T00:00:00.000"}',
        '{not valid json',
        '{"type":"x","title":"t","payload":{},"sync_state":"queued","created_at":"2026-01-01T00:00:00.000"}',
      ],
    });
    final store = LocalStore();
    await store.init();
    final list = store.drafts();
    expect(list, hasLength(1));
    expect(list.first.localId, 'good');
  });

  test('saveBootstrap dan bootstrap() round-trip', () async {
    final store = LocalStore();
    await store.init();
    const bs = MobileBootstrap(
      version: '1.0.0',
      datasets: [],
      kecamatan: [],
      desa: [],
      dusun: [],
      rumah: [],
      keluarga: [],
      penduduk: [],
      userDesaId: 7,
    );
    await store.saveBootstrap(bs);
    final loaded = store.bootstrap();
    expect(loaded, isNotNull);
    expect(loaded!.version, '1.0.0');
    expect(loaded.userDesaId, 7);
  });

  test('bootstrap() return null jika belum pernah disimpan', () async {
    final store = LocalStore();
    await store.init();
    expect(store.bootstrap(), isNull);
  });

  test('saveToken dan clearToken', () async {
    final store = LocalStore();
    await store.init();
    expect(store.token, isNull);
    await store.saveToken('abc123');
    expect(store.token, 'abc123');
    await store.clearToken();
    expect(store.token, isNull);
  });

  test('saveDraft concurrent call diserialize (tidak hilang)', () async {
    final store = LocalStore();
    await store.init();
    // 20 saveDraft bersamaan dari localId berbeda
    final futures = <Future<void>>[];
    for (var i = 0; i < 20; i++) {
      futures.add(store.saveDraft(_mkDraft('d$i')));
    }
    await Future.wait(futures);
    expect(store.drafts(), hasLength(20));
  });

  test('pullCursor default 0, setelah savePullCursor berubah', () async {
    final store = LocalStore();
    await store.init();
    expect(store.pullCursor, 0);
    await store.savePullCursor(42);
    expect(store.pullCursor, 42);
  });
}
