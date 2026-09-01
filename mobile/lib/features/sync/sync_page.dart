import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';
import '../../core/sync_service.dart';
import '../capture/draft_page.dart';

class SyncPage extends StatefulWidget {
  const SyncPage({super.key, required this.store, required this.api, required this.userDesaId, this.bootstrap, required this.onChanged});

  final LocalStore store;
  final ApiClient api;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final VoidCallback onChanged;

  @override
  State<SyncPage> createState() => _SyncPageState();
}

class _SyncPageState extends State<SyncPage> {
  String _filter = 'semua';
  late final SyncService _syncService;

  @override
  void initState() {
    super.initState();
    _syncService = SyncService(api: widget.api, store: widget.store);
    _refreshStatuses();
  }

  Future<void> _refreshStatuses() async {
    await _syncService.refreshBatchStatuses();
    if (mounted) setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final all = widget.store.drafts();
    final drafts = _filter == 'semua' ? all : all.where((d) => d.syncState == _filter).toList();
    final pending = all.where((d) => d.syncState != 'accepted' && d.syncState != 'conflict').length;

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
          child: Row(
            children: [
              Icon(pending == 0 ? Icons.check_circle : Icons.cloud_upload_outlined, size: 20, color: pending == 0 ? Colors.green : Colors.orange),
              const SizedBox(width: 8),
              Expanded(child: Text(pending == 0 ? 'Semua data sudah terkirim' : '$pending draft belum dikirim', style: TextStyle(fontSize: 13, color: pending == 0 ? Colors.green.shade700 : Colors.orange.shade700, fontWeight: FontWeight.w600))),
              if (pending > 0)
                FilledButton.icon(
                  onPressed: () => _sendAll(context),
                  icon: const Icon(Icons.send, size: 18),
                  label: const Text('Kirim Semua'),
                  style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8)),
                ),
            ],
          ),
        ),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            children: [
              _filterChip('Semua', 'semua', all.length),
              const SizedBox(width: 8),
              _filterChip('Menunggu', 'queued', all.where((d) => d.syncState == 'queued').length),
              const SizedBox(width: 8),
              _filterChip('Verifikasi', 'pending', all.where((d) => d.syncState == 'pending').length),
              const SizedBox(width: 8),
              _filterChip('Gagal', 'failed', all.where((d) => d.syncState == 'failed').length),
              const SizedBox(width: 8),
              _filterChip('Disetujui', 'accepted', all.where((d) => d.syncState == 'accepted').length),
            ],
          ),
        ),
        const SizedBox(height: 8),
        Expanded(
          child: drafts.isEmpty
              ? Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
                  Icon(Icons.inbox_outlined, size: 56, color: Colors.grey.shade300),
                  const SizedBox(height: 12),
                  Text('Belum ada draft', style: TextStyle(color: Colors.grey.shade500, fontSize: 15)),
                ]))
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                  itemCount: drafts.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, index) => _draftTile(context, drafts[index]),
                ),
        ),
      ],
    );
  }

  Widget _filterChip(String label, String value, int count) {
    final isSelected = _filter == value;
    return FilterChip(
      label: Text('$label ($count)'),
      selected: isSelected,
      onSelected: (_) => setState(() => _filter = value),
      selectedColor: Colors.teal.shade100,
      checkmarkColor: Colors.teal.shade700,
      labelStyle: TextStyle(fontSize: 12, fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500, color: isSelected ? Colors.teal.shade700 : Colors.grey.shade700),
    );
  }

  Widget _draftTile(BuildContext context, DraftRecord draft) {
    final info = _stateInfo(draft);
    final hasBatch = draft.batchId != null;
    return Card(
      child: Column(
        children: [
          ListTile(
            leading: Container(padding: const EdgeInsets.all(8), decoration: BoxDecoration(color: info.color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)), child: Icon(info.icon, color: info.color, size: 22)),
            title: Text(draft.title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
            subtitle: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const SizedBox(height: 2),
              Text(DateFormat('dd MMM yyyy, HH:mm').format(draft.createdAt), style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
              if (draft.errorMessage != null) ...[
                const SizedBox(height: 4),
                Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(6), border: Border.all(color: Colors.red.shade200)), child: Text(draft.errorMessage!, style: TextStyle(fontSize: 11, color: Colors.red.shade700), maxLines: 2, overflow: TextOverflow.ellipsis)),
              ],
            ]),
            trailing: PopupMenuButton<String>(
              icon: Icon(Icons.more_vert, color: Colors.grey.shade500),
              onSelected: (v) { if (v == 'edit') _editDraft(context, draft); if (v == 'delete') _deleteDraft(context, draft); },
              itemBuilder: (_) => [
                if (draft.syncState != 'accepted') const PopupMenuItem(value: 'edit', child: Row(children: [Icon(Icons.edit, size: 18), SizedBox(width: 8), Text('Edit')])),
                const PopupMenuItem(value: 'delete', child: Row(children: [Icon(Icons.delete, size: 18, color: Colors.red), SizedBox(width: 8), Text('Hapus', style: TextStyle(color: Colors.red))])),
              ],
            ),
          ),
          if (draft.syncState == 'queued' || draft.syncState == 'failed') ...[
            const Divider(height: 1),
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 4, 8, 8),
              child: Row(children: [
                const SizedBox(width: 8),
                Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4), decoration: BoxDecoration(color: info.color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20)), child: Text(info.label, style: TextStyle(fontSize: 11, color: info.color, fontWeight: FontWeight.w600))),
                const Spacer(),
                TextButton.icon(
                  onPressed: () => _sendOne(context, draft),
                  icon: const Icon(Icons.send, size: 16),
                  label: const Text('Kirim'),
                  style: TextButton.styleFrom(foregroundColor: Colors.teal.shade700, padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4)),
                ),
              ]),
            ),
          ],
          if (draft.syncState == 'accepted' || draft.batchStatus == 'disetujui') ...[
            const Divider(height: 1),
            Padding(padding: const EdgeInsets.fromLTRB(16, 0, 16, 12), child: Row(children: [
              Icon(Icons.check_circle, size: 16, color: Colors.green.shade600),
              const SizedBox(width: 6),
              Text('Terverifikasi oleh Admin Kecamatan', style: TextStyle(fontSize: 12, color: Colors.green.shade600, fontWeight: FontWeight.w500)),
            ])),
          ],
        ],
      ),
    );
  }

  ({IconData icon, Color color, String label}) _stateInfo(DraftRecord draft) {
    // Priority: batch workflow status > draft syncState
    final batchStatus = draft.batchStatus;
    if (batchStatus != null) {
      if (batchStatus == 'disetujui') return (icon: Icons.check_circle, color: Colors.green, label: 'Disetujui');
      if (batchStatus == 'ditolak') return (icon: Icons.cancel, color: Colors.red, label: 'Ditolak');
      if (batchStatus == 'sedang_diverifikasi') return (icon: Icons.pending, color: Colors.orange, label: 'Sedang Diverifikasi');
      if (batchStatus == 'perlu_revisi') return (icon: Icons.edit_note, color: Colors.orange, label: 'Perlu Revisi');
      if (batchStatus == 'menunggu_verifikasi') return (icon: Icons.hourglass_top, color: Colors.blue, label: 'Menunggu Verifikasi Kecamatan');
    }
    switch (draft.syncState) {
      case 'accepted': return (icon: Icons.check_circle, color: Colors.green, label: 'Disetujui');
      case 'failed': return (icon: Icons.error, color: Colors.red, label: 'Gagal');
      case 'conflict': return (icon: Icons.warning, color: Colors.orange, label: 'Konflik');
      case 'pending': return (icon: Icons.hourglass_top, color: Colors.blue, label: 'Menunggu Verifikasi Kecamatan');
      default: return (icon: Icons.cloud_upload, color: Colors.blue, label: 'Menunggu');
    }
  }

  Future<void> _sendAll(BuildContext context) async {
    final pending = widget.store.drafts().where((d) => d.syncState != 'accepted').toList();
    if (pending.isEmpty) return;

    final ok = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Kirim Semua Draft?'),
      content: Text('${pending.length} draft akan dikirim ke server.\nPastikan koneksi internet stabil.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kirim')),
      ],
    ));
    if (ok != true) return;

    if (!context.mounted) return;
    final overlay = _LoadingOverlay.show(context, 'Mengirim ${pending.length} draft...');
    try {
      final result = await _syncService.sync();
      overlay.remove();
      widget.onChanged();
      setState(() {});
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('${result.accepted} berhasil, ${result.failed} gagal'), backgroundColor: result.failed > 0 ? Colors.orange : Colors.green));
      }
    } catch (e) {
      overlay.remove();
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red));
    }
  }

  Future<void> _sendOne(BuildContext context, DraftRecord draft) async {
    final overlay = _LoadingOverlay.show(context, 'Mengirim...');
    try {
      final ok = await _syncService.retryDraft(draft.localId);
      overlay.remove();
      widget.onChanged();
      setState(() {});
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ok ? '${draft.title} berhasil dikirim.' : 'Gagal mengirim ${draft.title}'), backgroundColor: ok ? Colors.green : Colors.red));
    } catch (e) {
      overlay.remove();
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: $e'), backgroundColor: Colors.red));
    }
  }

  void _editDraft(BuildContext context, DraftRecord draft) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => DraftPage(store: widget.store, userDesaId: widget.userDesaId, bootstrap: widget.bootstrap, initialDataset: draft.type.key, editDraft: draft, onSaved: () { widget.onChanged(); setState(() {}); })));
  }

  Future<void> _deleteDraft(BuildContext context, DraftRecord draft) async {
    final ok = await showDialog<bool>(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Hapus Draft?'),
      content: Text('Hapus "${draft.title}"? Data yang dihapus tidak dapat dikembalikan.'),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
        FilledButton(onPressed: () => Navigator.pop(ctx, true), style: FilledButton.styleFrom(backgroundColor: Colors.red), child: const Text('Hapus')),
      ],
    ));
    if (ok != true) return;
    await _syncService.deleteDraft(draft.localId);
    widget.onChanged();
    setState(() {});
  }
}

class _LoadingOverlay {
  static _LoadingOverlay show(BuildContext context, String message) {
    final entry = OverlayEntry(builder: (_) => Material(color: Colors.black.withValues(alpha: 0.4), child: Center(child: Container(padding: const EdgeInsets.all(24), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)), child: Column(mainAxisSize: MainAxisSize.min, children: [const CircularProgressIndicator(), const SizedBox(height: 16), Text(message)])))));
    Overlay.of(context).insert(entry);
    return _LoadingOverlay._(entry);
  }

  final OverlayEntry _entry;
  _LoadingOverlay._(this._entry);
  void remove() => _entry.remove();
}