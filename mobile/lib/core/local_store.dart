import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

import 'models.dart';

class LocalStore {
  static const _draftsKey = 'sipdt.drafts';
  static const _tokenKey = 'sipdt.token';
  static const _deviceKey = 'sipdt.device_id';
  static const _bootstrapKey = 'sipdt.bootstrap';
  static const _pullCursorKey = 'sipdt.pull_cursor';

  late SharedPreferences _preferences;

  Future<void> init() async {
    _preferences = await SharedPreferences.getInstance();
  }

  String? get token => _preferences.getString(_tokenKey);

  Future<void> saveToken(String token) async => _preferences.setString(_tokenKey, token);

  Future<void> clearToken() async => _preferences.remove(_tokenKey);

  MobileBootstrap? bootstrap() {
    final raw = _preferences.getString(_bootstrapKey);
    if (raw == null) return null;
    try {
      return MobileBootstrap.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  Future<void> saveBootstrap(MobileBootstrap value) async {
    await _preferences.setString(_bootstrapKey, jsonEncode(value.toJson()));
  }

  int get pullCursor => _preferences.getInt(_pullCursorKey) ?? 0;

  Future<void> savePullCursor(int cursor) async => _preferences.setInt(_pullCursorKey, cursor);

  Future<String> deviceId() async {
    final current = _preferences.getString(_deviceKey);
    if (current != null && current.isNotEmpty) return current;
    const uuid = Uuid();
    final created = uuid.v4();
    await _preferences.setString(_deviceKey, created);
    return created;
  }

  List<DraftRecord> drafts() {
    final raw = _preferences.getStringList(_draftsKey) ?? const [];
    return raw.map((item) {
      try {
        return DraftRecord.fromJson(jsonDecode(item) as Map<String, dynamic>);
      } catch (_) {
        return null;
      }
    }).whereType<DraftRecord>().where((draft) => draft.localId.isNotEmpty).toList(growable: true);
  }

  Future<void> saveDraft(DraftRecord draft) async {
    final records = drafts();
    final index = records.indexWhere((item) => item.localId == draft.localId);
    if (index == -1) {
      records.insert(0, draft);
    } else {
      records[index] = draft;
    }
    await _preferences.setStringList(
      _draftsKey,
      records.map((item) => jsonEncode(item.toJson())).toList(),
    );
  }

  Future<void> removeDraft(String localId) async {
    final records = drafts()..removeWhere((item) => item.localId == localId);
    await _preferences.setStringList(
      _draftsKey,
      records.map((item) => jsonEncode(item.toJson())).toList(),
    );
  }
}
