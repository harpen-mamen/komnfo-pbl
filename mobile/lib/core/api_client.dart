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

  Future<MobileUser> login({required String email, required String password}) async {
    final response = await _request(
      'POST',
      '/mobile/login',
      body: {
        'email': email,
        'password': password,
        'device_id': await store.deviceId(),
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

  // ============ DRAFT OPERATIONS ============

  Future<List<Map<String, dynamic>>> pushDrafts(List<DraftRecord> drafts) async {
    final response = await _request(
      'POST',
      '/mobile/sync/push',
      body: {
        'device_id': await store.deviceId(),
        'operations': drafts.map((draft) => {
          'operation_id': draft.operationId ?? draft.localId,
          'dataset': draft.type.key,
          'local_id': draft.localId,
          'base_version': 0,
          'payload': draft.payload,
        }).toList(),
      },
    );
    return (response['results'] as List<dynamic>? ?? response['accepted'] as List<dynamic>? ?? const [])
        .map((item) => Map<String, dynamic>.from(item as Map))
        .toList();
  }

  // ============ BATCH STATUS ============

  /// Fetch batch statuses for a set of batch IDs.
  /// Used to check if AdminKecamatan has approved pending batches.
  Future<Map<int, Map<String, dynamic>>> fetchBatchStatuses(Set<int> batchIds) async {
    if (batchIds.isEmpty) return {};
    final ids = batchIds.join(',');
    final response = await _request('GET', '/mobile/sync/batch-statuses?ids=$ids');
    final batches = (response['batches'] as List<dynamic>? ?? const []);
    return {
      for (final b in batches)
        (b['id'] as num).toInt(): Map<String, dynamic>.from(b as Map)
    };
  }

  Future<Map<String, dynamic>> pull({required int cursor}) => _request('GET', '/mobile/sync/pull?cursor=$cursor');

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

    final uri = Uri.parse('${AppConfig.apiBaseUrl}$path');
    final request = http.Request(method, uri)..headers.addAll(headers);
    if (body != null) request.body = jsonEncode(body);

    final streamed = await request.send().timeout(AppConfig.apiTimeout);
    final response = await http.Response.fromStream(streamed);
    final decoded = response.body.isEmpty
        ? <String, dynamic>{}
        : jsonDecode(response.body) as Map<String, dynamic>;

    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = decoded['message'] as String? ??
          'Request gagal (${response.statusCode}).';
      throw ApiException(message, statusCode: response.statusCode);
    }
    return decoded;
  }
}
