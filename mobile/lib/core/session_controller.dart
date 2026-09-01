import 'package:flutter/foundation.dart';

import 'api_client.dart';
import 'local_store.dart';
import 'models.dart';

class SessionController extends ChangeNotifier {
  SessionController({required this.api, required this.store});

  final ApiClient api;
  final LocalStore store;

  MobileUser? user;
  bool isLoading = false;
  String? errorMessage;

  bool get isAuthenticated => user != null && store.token != null;

  Future<void> restore() async {
    if (store.token == null) return;
    try {
      user = await api.me();
    } catch (_) {
      await store.clearToken();
      user = null;
    }
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();
    try {
      user = await api.login(email: email.trim(), password: password);
      return true;
    } on ApiException catch (error) {
      errorMessage = error.message;
      return false;
    } catch (_) {
      errorMessage = 'Tidak dapat terhubung ke server SIPDT.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await api.logout();
    user = null;
    notifyListeners();
  }
}
