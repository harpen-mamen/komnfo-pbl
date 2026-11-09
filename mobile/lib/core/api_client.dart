import 'dart:convert';

import 'package:http/http.dart' as http;

import 'config.dart';
import 'local_store.dart';
import 'models.dart';

class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode});

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient({required this.store});

  final LocalStore store;

  // ============ AUTH ============

  /// Auto-login TANPA PASSWORD untuk admin_lapangan/admin_desa.
  /// Server cek device_id di tabel device_assignments.
  /// Return 404 jika device belum diaktivasi admin OPD.
  Future<MobileUser> autoLogin({String platform = 'android'}) async {
    final response = await _request(
      'POST',
      '/mobile/auto-login',
      body: {
        'device_id': await store.deviceId(),
        'platform': platform,
      },
      authenticated: false,
    );
    await store.saveToken(response['token'] as String);
    return MobileUser.fromJson(response['user'] as Map<String, dynamic>);
  }

  Future<MobileUser> me() async {
    final response = await _request('GET', '/mobile/me');
    return MobileUser.fromJson(response['user'] as Map<String, dynamic>);
  }

  Future<void> logout() async {
    try {
      await _request('POST', '/mobile/logout');
    } finally {
      await store.clearToken();
    }
  }

  // ============ BOOTSTRAP ============

  Future<MobileBootstrap> bootstrap() async {
    final response = await _request('GET', '/mobile/bootstrap');
    return MobileBootstrap.fromJson(response);
  }

  Future<List<Map<String, dynamic>>> fieldPoints() async {
    final response = await _request('GET', '/mobile/field-points');
    return (response['data'] as List<dynamic>? ?? const [])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList(growable: false);
  }

  Future<List<MobileFamilyOption>> families({required int desaId, String query = ''}) async {
    final params = <String, String>{'desa_id': '$desaId'};
    if (query.trim().isNotEmpty) params['q'] = query.trim();
    final suffix = Uri(queryParameters: params).query;
    final response = await _request('GET', '/mobile/master/keluarga?$suffix');
    return (response['data'] as List<dynamic>? ?? const [])
        .map((item) => MobileFamilyOption.fromJson(Map<String, dynamic>.from(item as Map)))
        .toList(growable: false);
  }

  // ============ DRAFT OPERATIONS ============

  Future<List<Map<String, dynamic>>> pushDrafts(List<DraftRecord> drafts) async {
    if (drafts.isEmpty) return const [];
    if (drafts.any((draft) => draft.type != DraftType.rumah && draft.type != DraftType.infrastruktur)) {
      throw const ApiException('Mobile hanya dapat mengirim data Rumah dan Infrastruktur. Data KK/Penduduk dipilih dari sumber OPD.');
    }
    if (drafts.any((draft) => draft.type == DraftType.rumah || draft.type == DraftType.infrastruktur)) {
      final response = await _request('POST', '/mobile/field-points', body: {
        'operations': drafts.map((draft) => {
          'operation_id': draft.operationId ?? draft.localId,
          'desa_id': draft.payload['desa_id'],
          'jenis': draft.type == DraftType.rumah ? 'rumah' : (draft.payload['jenis'] ?? 'lainnya'),
          'nama': draft.payload['nama'] ?? draft.title,
          'kode': draft.payload['kode_rumah'] ?? draft.payload['kode'],
          'alamat': draft.payload['alamat_lengkap'] ?? draft.payload['alamat'],
          'latitude': draft.payload['latitude'],
          'longitude': draft.payload['longitude'],
          'attributes': draft.payload,
          'keluarga_ids': draft.payload['keluarga_ids'] ?? const <int>[],
        }).toList(),
      });
      return (response['results'] as List<dynamic>? ?? const [])
          .map((item) => Map<String, dynamic>.from(item as Map)..['local_id'] = drafts.firstWhere((draft) => draft.operationId == item['operation_id'] || draft.localId == item['operation_id']).localId)
          .toList(growable: false);
    }
    return const [];
  }

  // ============ BATCH STATUS ============

  /// Fetch batch statuses for a set of batch IDs.
  /// Used to check if AdminKecamatan has approved pending batches.
  Future<Map<int, Map<String, dynamic>>> fetchBatchStatuses(Set<int> batchIds) async {
    if (batchIds.isEmpty) return {};
    final ids = batchIds.join(',');
    final response = await _request('GET', '/mobile/field-point-batch-statuses?ids=$ids');
    final batches = (response['batches'] as List<dynamic>? ?? const []);
    return {
      for (final b in batches)
        (b['id'] as num).toInt(): Map<String, dynamic>.from(b as Map)
    };
  }

  Future<Map<String, dynamic>> _request(
    String method,
    String path, {
    Map<String, dynamic>? body,
    bool authenticated = true,
  }) async {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (authenticated && store.token != null) {
      headers['Authorization'] = 'Bearer ${store.token}';
    }

    final baseUrl = AppConfig.apiBaseUrl.replaceFirst(RegExp(r'/+$'), '');
    final normalizedPath = path.startsWith('/') ? path : '/$path';
    final uri = Uri.parse('$baseUrl$normalizedPath');
    final request = http.Request(method, uri)..headers.addAll(headers);
    if (body != null) request.body = jsonEncode(body);

    final streamed = await request.send().timeout(AppConfig.apiTimeout);
    final response = await http.Response.fromStream(streamed);
    Map<String, dynamic> decoded;
    try {
      final value = response.body.isEmpty ? <String, dynamic>{} : jsonDecode(response.body);
      decoded = value is Map<String, dynamic>
          ? value
          : <String, dynamic>{'data': value};
    } on FormatException {
      decoded = <String, dynamic>{};
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      final errors = decoded['errors'];
      final validationMessage = errors is Map
          ? errors.values.expand((value) => value is List ? value : [value]).firstOrNull?.toString()
          : null;
      final message = decoded['message'] as String? ?? validationMessage ??
          (response.statusCode >= 500
              ? 'Server Laravel mengalami gangguan (${response.statusCode}).'
              : 'Request gagal (${response.statusCode}).');
      throw ApiException(message, statusCode: response.statusCode);
    }
    return decoded;
  }
}
