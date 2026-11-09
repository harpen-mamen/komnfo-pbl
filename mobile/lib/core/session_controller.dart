import 'dart:async';
import 'dart:io';

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

  // Mode offline: user memilih "Lanjut Offline" dari halaman error.
  // Dalam mode ini, app skip auto-login dan tampilkan UI langsung.
  // Data bootstrap akan kosong (user lihat UI dengan state kosong).
  // Draft yang dibuat akan tersimpan lokal dan bisa dikirim saat online.
  bool offlineMode = false;

  // State untuk aktivasi device (perlu tindakan admin OPD)
  bool needsActivation = false;
  String? activationError;

  // State untuk error koneksi / server (TIDAK butuh admin OPD)
  // Dipisah dari needsActivation agar user tidak salah menghubungi admin.
  bool noConnection = false;
  bool serverError = false;
  String? connectionError;

  bool get isAuthenticated => user != null && store.token != null;

  /// Restore session dari local storage. Coba auto-login by device_id.
  Future<void> restore() async {
    // Sudah ada token valid di local
    if (store.token != null) {
      try {
        user = await api.me();
        return;
      } catch (_) {
        // Token invalid/expired, hapus dan lanjut ke auto-login
        await store.clearToken();
        user = null;
      }
    }

    // Coba auto-login by device_id (admin lapangan TIDAK PERLU LOGIN)
    await _tryAutoLogin();
    notifyListeners();
  }

  /// Auto-login tanpa input user - khusus admin_lapangan/admin_desa
  /// yang device-nya sudah diaktivasi admin OPD di web panel.
  ///
  /// Klasifikasi error:
  /// - 401/403/404 "belum diaktivasi" -> needsActivation (butuh admin OPD)
  /// - 5xx                                  -> serverError (server gangguan)
  /// - TimeoutException/SocketException    -> noConnection (jaringan putus)
  /// - Exception lainnya                   -> noConnection (default aman)
  Future<void> _tryAutoLogin() async {
    isLoading = true;
    // Reset semua state error agar transisi bersih
    needsActivation = false;
    activationError = null;
    noConnection = false;
    serverError = false;
    connectionError = null;
    notifyListeners();

    try {
      user = await api.autoLogin(platform: 'android');
    } on ApiException catch (e) {
      user = null;
      final code = e.statusCode;
      if (code == 404 || code == 401 || code == 403) {
        // Gagal auth / device belum terasosiasi -> butuh admin OPD
        needsActivation = true;
        activationError = e.message;
      } else if (code != null && code >= 500) {
        // Server down / error internal
        serverError = true;
        connectionError = e.message;
      } else {
        // Error API lain (seperti 400/422) -> perlakukan sebagai activation
        // karena paling aman mengarahkan user ke admin.
        needsActivation = true;
        activationError = e.message;
      }
    } on TimeoutException {
      user = null;
      noConnection = true;
      connectionError = 'Server tidak merespons. Periksa koneksi Anda.';
    } on SocketException catch (e) {
      user = null;
      noConnection = true;
      connectionError = 'Tidak dapat terhubung ke server: ${e.message}';
    } catch (e) {
      // Exception lain (termasuk http.ClientException) -> anggap jaringan
      user = null;
      noConnection = true;
      connectionError = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
    } finally {
      isLoading = false;
    }
  }

  /// Retry auto-login (mis. setelah aktivasi device oleh admin OPD,
  /// atau setelah user kembali ke area dengan koneksi).
  Future<void> retryAutoLogin() async {
    await _tryAutoLogin();
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await api.logout();
    } catch (_) {}
    await store.clearToken();
    user = null;
    needsActivation = false;
    activationError = null;
    noConnection = false;
    serverError = false;
    connectionError = null;
    offlineMode = false;
    notifyListeners();
  }

  /// Masuk ke mode offline. Dipanggil saat user memilih "Lanjut Offline"
  /// dari halaman ConnectionErrorPage. App akan menampilkan HomePage
  /// dengan bootstrap kosong (jika belum pernah sync) sehingga user bisa
  /// menjelajahi UI dan membuat draft lokal tanpa koneksi server.
  void enterOfflineMode() {
    // Placeholder user agar dashboard/profile bisa render tanpa crash.
    // Field identitas dikosongkan (nama default "Admin Desa Demo").
    user = const MobileUser(
      id: 0,
      name: 'Admin Desa (Demo)',
      email: '-',
      roles: ['admin_desa'],
      roleLabel: 'Admin Desa',
    );
    offlineMode = true;
    isLoading = false;
    noConnection = false;
    serverError = false;
    connectionError = null;
    notifyListeners();
  }
}
