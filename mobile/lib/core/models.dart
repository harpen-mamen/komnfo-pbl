class MobileUser {
  const MobileUser({
    required this.id,
    required this.name,
    required this.email,
    required this.roles,
    this.roleLabel,
    this.kecamatanId,
    this.desaId,
    this.kecamatan,
    this.desa,
  });

  final int id;
  final String name;
  final String email;
  final List<String> roles;
  final String? roleLabel;
  final int? kecamatanId;
  final int? desaId;
  final String? kecamatan;
  final String? desa;

  factory MobileUser.fromJson(Map<String, dynamic> json) => MobileUser(
        id: (json['id'] as num?)?.toInt() ?? 0,
        name: json['name'] as String? ?? '-',
        email: json['email'] as String? ?? '-',
        roles: (json['roles'] as List<dynamic>? ?? const []).map((role) => role.toString()).toList(),
        roleLabel: json['role_label'] as String? ?? json['role'] as String?,
        kecamatanId: (json['kecamatan_id'] as num?)?.toInt(),
        desaId: (json['desa_id'] as num?)?.toInt(),
        kecamatan: json['kecamatan'] as String?,
        desa: json['desa'] as String?,
      );

  bool get isAdminDesa => roles.contains('admin_desa') || roles.contains('admin_lapangan');
}

class ReferenceOption {
  const ReferenceOption({required this.id, required this.name, this.kecamatanId, this.desaId, this.code});

  final int id;
  final String name;
  final int? kecamatanId;
  final int? desaId;
  final String? code;

  factory ReferenceOption.fromJson(Map<String, dynamic> json) => ReferenceOption(
        id: (json['id'] as num?)?.toInt() ?? 0,
        name: json['nama'] as String? ?? '-',
        kecamatanId: (json['kecamatan_id'] as num?)?.toInt(),
        desaId: (json['desa_id'] as num?)?.toInt(),
        code: json['kode'] as String?,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'nama': name,
        'kecamatan_id': kecamatanId,
        'desa_id': desaId,
        'kode': code,
      };
}

class MobileDataset {
  const MobileDataset({required this.key, required this.label, this.offlineEnabled = true});

  final String key;
  final String label;
  final bool offlineEnabled;

  factory MobileDataset.fromJson(Map<String, dynamic> json) => MobileDataset(
        key: json['key'] as String? ?? 'rumah',
        label: json['label'] as String? ?? 'Dataset',
        offlineEnabled: json['offline_enabled'] as bool? ?? true,
      );

  Map<String, dynamic> toJson() => {'key': key, 'label': label, 'offline_enabled': offlineEnabled};
}

class MobileFamilyOption {
  const MobileFamilyOption({required this.id, required this.nomorKk, required this.namaKepala, this.jumlahAnggota});
  final int id;
  final String nomorKk;
  final String namaKepala;
  final int? jumlahAnggota;

  factory MobileFamilyOption.fromJson(Map<String, dynamic> json) => MobileFamilyOption(
        id: (json['id'] as num).toInt(),
        nomorKk: json['nomor_kk'] as String? ?? '',
        namaKepala: json['nama_kepala_keluarga'] as String? ?? '',
        jumlahAnggota: (json['jumlah_anggota'] as num?)?.toInt(),
      );
}

class RumahRecord {
  const RumahRecord({required this.id, required this.desaId, this.dusunId, required this.kodeRumah, required this.alamat, required this.latitude, required this.longitude, required this.status, this.keluargaCount = 0, this.isLocal = false, this.isVerified = false, this.operationId});

  final int id;
  final int desaId;
  final int? dusunId;
  final String kodeRumah;
  final String alamat;
  final double latitude;
  final double longitude;
  final String status;
  final int keluargaCount;
  final bool isLocal;
  final bool isVerified;
  final String? operationId;

  factory RumahRecord.fromJson(Map<String, dynamic> json) => RumahRecord(
        id: (json['id'] as num?)?.toInt() ?? 0,
        desaId: (json['desa_id'] as num?)?.toInt() ?? 0,
        dusunId: (json['dusun_id'] as num?)?.toInt(),
        kodeRumah: json['kode_rumah'] as String? ?? '-',
        alamat: json['alamat_lengkap'] as String? ?? '-',
        latitude: (json['latitude'] as num?)?.toDouble() ?? 0,
        longitude: (json['longitude'] as num?)?.toDouble() ?? 0,
        status: json['status_rumah'] as String? ?? '-',
        keluargaCount: (json['keluarga_count'] as num?)?.toInt() ?? 0,
        isLocal: json['is_local'] as bool? ?? false,
        isVerified: json['is_verified'] as bool? ?? false,
        operationId: json['operation_id'] as String?,
      );

  Map<String, dynamic> toJson() => {'id': id, 'desa_id': desaId, 'dusun_id': dusunId, 'kode_rumah': kodeRumah, 'alamat_lengkap': alamat, 'latitude': latitude, 'longitude': longitude, 'status_rumah': status, 'keluarga_count': keluargaCount, 'is_local': isLocal, 'is_verified': isVerified, 'operation_id': operationId};
}

class InfrastructureRecord {
  const InfrastructureRecord({required this.id, required this.desaId, required this.jenis, required this.nama, required this.latitude, required this.longitude, this.kode = '', this.alamat = '', this.isLocal = false, this.isVerified = false, this.operationId});

  final int id;
  final int desaId;
  final String jenis;
  final String nama;
  final String kode;
  final String alamat;
  final double latitude;
  final double longitude;
  final bool isLocal;
  final bool isVerified;
  final String? operationId;

  factory InfrastructureRecord.fromJson(Map<String, dynamic> json) => InfrastructureRecord(
        id: (json['id'] as num?)?.toInt() ?? 0,
        desaId: (json['desa_id'] as num?)?.toInt() ?? 0,
        jenis: json['jenis'] as String? ?? 'lainnya',
        nama: json['nama'] as String? ?? '-',
        kode: json['kode'] as String? ?? '',
        alamat: json['alamat'] as String? ?? '',
        latitude: (json['latitude'] as num?)?.toDouble() ?? 0,
        longitude: (json['longitude'] as num?)?.toDouble() ?? 0,
        isLocal: json['is_local'] as bool? ?? false,
        isVerified: json['is_verified'] as bool? ?? false,
        operationId: json['operation_id'] as String?,
      );

  Map<String, dynamic> toJson() => {'id': id, 'desa_id': desaId, 'jenis': jenis, 'nama': nama, 'kode': kode, 'alamat': alamat, 'latitude': latitude, 'longitude': longitude, 'is_local': isLocal, 'is_verified': isVerified, 'operation_id': operationId};
}

class KeluargaRecord {
  const KeluargaRecord({required this.id, required this.rumahId, required this.nomorKk, required this.kepala, required this.jumlahAnggota, required this.statusEkonomi});

  final int id;
  final int rumahId;
  final String nomorKk;
  final String kepala;
  final int jumlahAnggota;
  final String statusEkonomi;

  factory KeluargaRecord.fromJson(Map<String, dynamic> json) => KeluargaRecord(
        id: (json['id'] as num?)?.toInt() ?? 0,
        rumahId: (json['rumah_id'] as num?)?.toInt() ?? 0,
        nomorKk: json['nomor_kk'] as String? ?? '-',
        kepala: json['nama_kepala_keluarga'] as String? ?? '-',
        jumlahAnggota: (json['jumlah_anggota'] as num?)?.toInt() ?? 0,
        statusEkonomi: json['status_ekonomi'] as String? ?? '-',
      );

  Map<String, dynamic> toJson() => {'id': id, 'rumah_id': rumahId, 'nomor_kk': nomorKk, 'nama_kepala_keluarga': kepala, 'jumlah_anggota': jumlahAnggota, 'status_ekonomi': statusEkonomi};
}

class PendudukRecord {
  const PendudukRecord({required this.id, required this.keluargaId, required this.nik, required this.nama, required this.jenisKelamin});

  final int id;
  final int keluargaId;
  final String nik;
  final String nama;
  final String jenisKelamin;

  factory PendudukRecord.fromJson(Map<String, dynamic> json) => PendudukRecord(
        id: (json['id'] as num?)?.toInt() ?? 0,
        keluargaId: (json['keluarga_id'] as num?)?.toInt() ?? 0,
        nik: json['nik'] as String? ?? '-',
        nama: json['nama_lengkap'] as String? ?? '-',
        jenisKelamin: json['jenis_kelamin'] as String? ?? '-',
      );

  Map<String, dynamic> toJson() => {'id': id, 'keluarga_id': keluargaId, 'nik': nik, 'nama_lengkap': nama, 'jenis_kelamin': jenisKelamin};
}

class MobileBootstrap {
  const MobileBootstrap({required this.version, required this.datasets, required this.kecamatan, required this.desa, required this.dusun, required this.rumah, required this.keluarga, required this.penduduk, this.infrastruktur = const [], this.userDesaId, this.kecamatanId});

  final String version;
  final List<MobileDataset> datasets;
  final List<ReferenceOption> kecamatan;
  final List<ReferenceOption> desa;
  final List<ReferenceOption> dusun;
  final List<RumahRecord> rumah;
  final List<KeluargaRecord> keluarga;
  final List<PendudukRecord> penduduk;
  final List<InfrastructureRecord> infrastruktur;
  final int? userDesaId;
  final int? kecamatanId;

  factory MobileBootstrap.fromJson(Map<String, dynamic> json) {
    final reference = Map<String, dynamic>.from(json['reference'] as Map? ?? {});
    final records = Map<String, dynamic>.from(json['records'] as Map? ?? {});
    final user = Map<String, dynamic>.from(json['user'] as Map? ?? {});
    return MobileBootstrap(
      version: json['version'] as String? ?? '-',
      datasets: (json['datasets'] as List<dynamic>? ?? const []).map((item) => MobileDataset.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false),
      kecamatan: _options(reference['kecamatan']),
      desa: _options(reference['desa']),
      dusun: _options(reference['dusun']),
      rumah: (records['rumah'] as List<dynamic>? ?? const []).map((item) => RumahRecord.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false),
      keluarga: (records['keluarga'] as List<dynamic>? ?? const []).map((item) => KeluargaRecord.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false),
      penduduk: (records['penduduk'] as List<dynamic>? ?? const []).map((item) => PendudukRecord.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false),
      infrastruktur: (records['infrastruktur'] as List<dynamic>? ?? const []).map((item) => InfrastructureRecord.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false),
      userDesaId: (user['desa_id'] as num?)?.toInt(),
      kecamatanId: (user['kecamatan_id'] as num?)?.toInt(),
    );
  }

  static List<ReferenceOption> _options(dynamic value) => (value as List<dynamic>? ?? const []).map((item) => ReferenceOption.fromJson(Map<String, dynamic>.from(item as Map))).toList(growable: false);

  Map<String, dynamic> toJson() => {
        'version': version,
        'user': {'desa_id': userDesaId, 'kecamatan_id': kecamatanId},
        'datasets': datasets.map((item) => item.toJson()).toList(),
        'reference': {'kecamatan': kecamatan.map((item) => item.toJson()).toList(), 'desa': desa.map((item) => item.toJson()).toList(), 'dusun': dusun.map((item) => item.toJson()).toList()},
        'records': {'rumah': rumah.map((item) => item.toJson()).toList(), 'keluarga': keluarga.map((item) => item.toJson()).toList(), 'penduduk': penduduk.map((item) => item.toJson()).toList(), 'infrastruktur': infrastruktur.map((item) => item.toJson()).toList()},
      };
}

enum DraftType {
  rumah('rumah', 'Rumah'),
  keluarga('keluarga', 'Kartu Keluarga'),
  penduduk('penduduk', 'Penduduk'),
  infrastruktur('infrastruktur', 'Infrastruktur');

  const DraftType(this.key, this.label);
  final String key;
  final String label;

  static DraftType fromKey(String? key) {
    for (final type in values) {
      if (type.key == key) return type;
    }
    return DraftType.rumah;
  }
}

class DraftRecord {
  const DraftRecord({
    required this.localId,
    required this.type,
    required this.title,
    required this.payload,
    required this.syncState,
    required this.createdAt,
    this.operationId,
    this.serverId,
    this.errorMessage,
    this.batchId,
    this.batchStatus,
    this.batchStatusLabel,
  });

  final String localId;
  final DraftType type;
  final String title;
  final Map<String, dynamic> payload;
  final String syncState;
  final DateTime createdAt;
  final String? operationId;
  final int? serverId;
  final String? errorMessage;
  final int? batchId;
  final String? batchStatus;
  final String? batchStatusLabel;

  Map<String, dynamic> toJson() => {
        'local_id': localId,
        'type': type.key,
        'title': title,
        'payload': payload,
        'sync_state': syncState,
        'created_at': createdAt.toIso8601String(),
        'operation_id': operationId,
        'server_id': serverId,
        'error_message': errorMessage,
        'batch_id': batchId,
        'batch_status': batchStatus,
        'batch_status_label': batchStatusLabel,
      };

  factory DraftRecord.fromJson(Map<String, dynamic> json) {
    final localId = json['local_id'] as String? ?? '';
    return DraftRecord(
      localId: localId,
      type: DraftType.fromKey(json['type'] as String? ?? json['dataset'] as String?),
      title: json['title'] as String? ?? 'Draft',
      payload: Map<String, dynamic>.from(json['payload'] as Map? ?? {}),
      syncState: json['sync_state'] as String? ?? 'draft',
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
      operationId: json['operation_id'] as String? ?? localId,
      serverId: (json['server_id'] as num?)?.toInt(),
      errorMessage: json['error_message'] as String?,
      batchId: (json['batch_id'] as num?)?.toInt(),
      batchStatus: json['batch_status'] as String?,
      batchStatusLabel: json['batch_status_label'] as String?,
    );
  }

  DraftRecord copyWith({
    String? syncState,
    String? errorMessage,
    int? serverId,
    bool clearErrorMessage = false,
    int? batchId,
    String? batchStatus,
    String? batchStatusLabel,
  }) =>
      DraftRecord(
        localId: localId,
        type: type,
        title: title,
        payload: payload,
        syncState: syncState ?? this.syncState,
        createdAt: createdAt,
        operationId: operationId,
        serverId: serverId ?? this.serverId,
        errorMessage: clearErrorMessage ? null : errorMessage ?? this.errorMessage,
        batchId: batchId ?? this.batchId,
        batchStatus: batchStatus ?? this.batchStatus,
        batchStatusLabel: batchStatusLabel ?? this.batchStatusLabel,
      );
}
