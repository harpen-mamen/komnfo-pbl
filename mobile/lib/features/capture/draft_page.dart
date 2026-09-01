import 'package:flutter/material.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';
import 'keluarga_capture_page.dart';
import 'penduduk_capture_page.dart';
import 'rumah_capture_page.dart';

class DraftPage extends StatelessWidget {
  const DraftPage({
    super.key,
    required this.store,
    required this.userDesaId,
    this.bootstrap,
    this.initialDataset,
    this.editDraft,
    this.onSaved,
  });

  final LocalStore store;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final String? initialDataset;
  final DraftRecord? editDraft;
  final VoidCallback? onSaved;

  @override
  Widget build(BuildContext context) {
    final saved = onSaved ?? () {};
    return switch (initialDataset) {
      'keluarga' => KeluargaCapturePage(store: store, userDesaId: userDesaId, bootstrap: bootstrap, editDraft: editDraft, onSaved: saved),
      'penduduk' => PendudukCapturePage(store: store, userDesaId: userDesaId, bootstrap: bootstrap, editDraft: editDraft, onSaved: saved),
      _ => RumahCapturePage(store: store, userDesaId: userDesaId, bootstrap: bootstrap, editDraft: editDraft, onSaved: saved),
    };
  }
}