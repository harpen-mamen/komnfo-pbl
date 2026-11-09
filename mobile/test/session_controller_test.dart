import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sipdt_mobile/core/api_client.dart';
import 'package:sipdt_mobile/core/local_store.dart';
import 'package:sipdt_mobile/core/session_controller.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
  });

  test('enterOfflineMode menyetel state untuk tampilan UI offline', () async {
    final store = LocalStore();
    await store.init();
    final api = ApiClient(store: store);
    final session = SessionController(api: api, store: store);

    expect(session.user, null);
    expect(session.offlineMode, false);
    expect(session.isAuthenticated, false);

    session.enterOfflineMode();

    expect(session.offlineMode, true);
    expect(session.isLoading, false);
    expect(session.noConnection, false);
    expect(session.serverError, false);
    // Placeholder user di-set agar dashboard/profile tidak crash
    expect(session.user, isNotNull);
    expect(session.user!.name, contains('Demo'));
    expect(session.user!.isAdminDesa, isTrue);
  });

  test('enterOfflineMode setelah error state -> state error ter-reset', () async {
    final store = LocalStore();
    await store.init();
    final api = ApiClient(store: store);
    final session = SessionController(api: api, store: store);

    // Simulasikan state error
    session.noConnection = true;
    session.serverError = true;
    session.connectionError = 'test error';

    session.enterOfflineMode();

    expect(session.noConnection, false);
    expect(session.serverError, false);
    expect(session.connectionError, null);
  });

  test('logout juga reset offlineMode', () async {
    final store = LocalStore();
    await store.init();
    final api = ApiClient(store: store);
    final session = SessionController(api: api, store: store);

    session.enterOfflineMode();
    expect(session.offlineMode, true);

    await session.logout();
    expect(session.offlineMode, false);
    expect(session.user, null);
  });
}
