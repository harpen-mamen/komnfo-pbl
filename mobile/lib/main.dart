import 'package:flutter/material.dart';

import 'app.dart';
import 'core/api_client.dart';
import 'core/local_store.dart';
import 'core/session_controller.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final store = LocalStore();
  await store.init();

  final api = ApiClient(store: store);
  final session = SessionController(api: api, store: store);
  await session.restore();

  runApp(SipdtApp(session: session, api: api, store: store));
}
