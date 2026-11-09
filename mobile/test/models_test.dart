import 'package:flutter_test/flutter_test.dart';
import 'package:sipdt_mobile/core/models.dart';

void main() {
  group('RumahRecord', () {
    test('fromJson parses semua field dengan benar', () {
      final r = RumahRecord.fromJson({
        'id': 42,
        'desa_id': 3,
        'dusun_id': 7,
        'kode_rumah': 'R-001',
        'alamat_lengkap': 'Jl. Merdeka No. 5',
        'latitude': 3.55,
        'longitude': 125.50,
        'status_rumah': 'Milik Sendiri',
        'keluarga_count': 2,
      });
      expect(r.id, 42);
      expect(r.desaId, 3);
      expect(r.dusunId, 7);
      expect(r.kodeRumah, 'R-001');
      expect(r.alamat, 'Jl. Merdeka No. 5');
      expect(r.latitude, 3.55);
      expect(r.longitude, 125.50);
      expect(r.status, 'Milik Sendiri');
      expect(r.keluargaCount, 2);
    });

    test('fromJson toleransi field hilang dengan default aman', () {
      final r = RumahRecord.fromJson({});
      expect(r.id, 0);
      expect(r.desaId, 0);
      expect(r.dusunId, null);
      expect(r.kodeRumah, '-');
      expect(r.alamat, '-');
      expect(r.latitude, 0);
      expect(r.longitude, 0);
      expect(r.keluargaCount, 0);
    });

    test('toJson menghasilkan field dengan key snake_case', () {
      const r = RumahRecord(
        id: 1, desaId: 2, dusunId: 3, kodeRumah: 'R-1',
        alamat: 'Alamat', latitude: 1.0, longitude: 2.0,
        status: 'Kontrak', keluargaCount: 1,
      );
      final json = r.toJson();
      expect(json['kode_rumah'], 'R-1');
      expect(json['alamat_lengkap'], 'Alamat');
      expect(json['status_rumah'], 'Kontrak');
      expect(json['latitude'], 1.0);
      expect(json['longitude'], 2.0);
    });
  });

  group('DraftRecord', () {
    test('fromJson dan toJson round-trip mempertahankan semua field', () {
      final original = DraftRecord(
        localId: 'local-123',
        operationId: 'op-456',
        type: DraftType.keluarga,
        title: 'KK 1234567890123456',
        payload: {'nomor_kk': '1234567890123456', 'jumlah_anggota': 4},
        syncState: 'pending',
        createdAt: DateTime.parse('2026-10-19T10:00:00Z'),
        serverId: 99,
        batchId: 5,
        batchStatus: 'menunggu_verifikasi',
        batchStatusLabel: 'Menunggu Verifikasi',
        errorMessage: null,
      );
      final json = original.toJson();
      final restored = DraftRecord.fromJson(json);
      expect(restored.localId, original.localId);
      expect(restored.operationId, original.operationId);
      expect(restored.type, original.type);
      expect(restored.title, original.title);
      expect(restored.payload, original.payload);
      expect(restored.syncState, original.syncState);
      expect(restored.createdAt, original.createdAt);
      expect(restored.serverId, original.serverId);
      expect(restored.batchId, original.batchId);
      expect(restored.batchStatus, original.batchStatus);
      expect(restored.batchStatusLabel, original.batchStatusLabel);
      expect(restored.errorMessage, null);
    });

    test('copyWith hanya mengubah field yang disebutkan', () {
      final base = DraftRecord(
        localId: 'a',
        type: DraftType.rumah,
        title: 't',
        payload: const {},
        syncState: 'queued',
        createdAt: DateTime(2026, 1, 1),
      );
      final updated = base.copyWith(
        syncState: 'pending',
        batchId: 7,
        clearErrorMessage: true,
      );
      expect(updated.localId, 'a');
      expect(updated.syncState, 'pending');
      expect(updated.batchId, 7);
      expect(updated.errorMessage, null);
      // Field lain tidak berubah
      expect(updated.type, DraftType.rumah);
      expect(updated.createdAt, DateTime(2026, 1, 1));
    });

    test('DraftType.fromKey map key yang dikenal, fallback ke rumah', () {
      expect(DraftType.fromKey('rumah'), DraftType.rumah);
      expect(DraftType.fromKey('keluarga'), DraftType.keluarga);
      expect(DraftType.fromKey('penduduk'), DraftType.penduduk);
      expect(DraftType.fromKey('unknown'), DraftType.rumah);
      expect(DraftType.fromKey(null), DraftType.rumah);
    });
  });

  group('MobileBootstrap', () {
    test('fromJson parsing struktur lengkap', () {
      final b = MobileBootstrap.fromJson({
        'version': '1.0.0',
        'datasets': [
          {'key': 'rumah', 'label': 'Rumah', 'offline_enabled': true},
        ],
        'reference': {
          'kecamatan': [
            {'id': 1, 'nama': 'Tabukan Utara', 'kecamatan_id': null, 'desa_id': null, 'kode': 'TUT'}
          ],
          'desa': [],
          'dusun': [],
        },
        'records': {
          'rumah': [
            {
              'id': 1, 'desa_id': 1, 'dusun_id': null,
              'kode_rumah': 'R-1', 'alamat_lengkap': 'Alamat',
              'latitude': 1.0, 'longitude': 2.0,
              'status_rumah': 'Milik Sendiri', 'keluarga_count': 0,
            }
          ],
          'keluarga': [],
          'penduduk': [],
        },
        'user': {'desa_id': 5},
      });
      expect(b.version, '1.0.0');
      expect(b.datasets, hasLength(1));
      expect(b.datasets.first.key, 'rumah');
      expect(b.kecamatan, hasLength(1));
      expect(b.kecamatan.first.name, 'Tabukan Utara');
      expect(b.rumah, hasLength(1));
      expect(b.rumah.first.kodeRumah, 'R-1');
      expect(b.keluarga, isEmpty);
      expect(b.penduduk, isEmpty);
      expect(b.userDesaId, 5);
    });

    test('fromJson toleransi section hilang', () {
      final b = MobileBootstrap.fromJson({});
      expect(b.version, '-');
      expect(b.datasets, isEmpty);
      expect(b.kecamatan, isEmpty);
      expect(b.desa, isEmpty);
      expect(b.dusun, isEmpty);
      expect(b.rumah, isEmpty);
      expect(b.keluarga, isEmpty);
      expect(b.penduduk, isEmpty);
      expect(b.userDesaId, null);
    });
  });

  group('MobileUser', () {
    test('isAdminDesa true jika roles mengandung admin_desa', () {
      const u = MobileUser(id: 1, name: 'A', email: 'a@x', roles: ['admin_desa', 'viewer']);
      expect(u.isAdminDesa, isTrue);
    });

    test('isAdminDesa false jika roles tidak mengandung', () {
      const u = MobileUser(id: 1, name: 'A', email: 'a@x', roles: ['admin_opd']);
      expect(u.isAdminDesa, isFalse);
    });

    test('isAdminDesa false jika roles kosong', () {
      const u = MobileUser(id: 1, name: 'A', email: 'a@x', roles: []);
      expect(u.isAdminDesa, isFalse);
    });
  });
}
