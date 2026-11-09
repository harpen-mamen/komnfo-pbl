import 'package:flutter/material.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';
import 'rumah_capture_page.dart';
import 'infrastruktur_capture_page.dart';

class DraftPage extends StatelessWidget {
  const DraftPage({
    super.key,
    required this.store,
    required this.api,
    required this.userDesaId,
    this.bootstrap,
    this.initialDataset,
    this.editDraft,
    this.onSaved,
  });

  final LocalStore store;
  final ApiClient api;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final String? initialDataset;
  final DraftRecord? editDraft;
  final VoidCallback? onSaved;

  @override
  Widget build(BuildContext context) {
    final saved = onSaved ?? () {};
    if (initialDataset == 'infrastruktur' || editDraft?.type == DraftType.infrastruktur) {
      return InfrastrukturCapturePage(store: store, userDesaId: userDesaId, bootstrap: bootstrap, editDraft: editDraft, onSaved: saved);
    }
    return RumahCapturePage(store: store, api: api, userDesaId: userDesaId, bootstrap: bootstrap, editDraft: editDraft, onSaved: saved);
  }
}
