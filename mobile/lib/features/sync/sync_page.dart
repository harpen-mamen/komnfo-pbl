import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';
import '../../core/sync_service.dart';
import '../capture/draft_page.dart';

class SyncPage extends StatefulWidget {
  const SyncPage({
    super.key,
    required this.store,
    required this.api,
    required this.userDesaId,
    this.bootstrap,
    required this.onChanged,
  });

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
  List<DraftRecord> _drafts = const [];

  @override
  void initState() {
    super.initState();
    _syncService = SyncService(api: widget.api, store: widget.store);
    _loadDrafts();
    _refreshStatuses();
  }

  void _loadDrafts() {
    if (!mounted) return;
    setState(() => _drafts = widget.store.drafts());
  }

  Future<void> _refreshStatuses() async {
    await _syncService.refreshBatchStatuses();
    _loadDrafts();
  }

  @override
  Widget build(BuildContext context) {
    final all = _drafts;
    final drafts = _filter == 'semua' ? all : all.where((d) => d.syncState == _filter).toList();
    final pending = all.where((d) => (d.type == DraftType.rumah || d.type == DraftType.infrastruktur) && d.syncState != 'accepted' && d.syncState != 'conflict').length;

    return Column(
      children: [
        // Top Banner Status
        Container(
          margin: const EdgeInsets.fromLTRB(16, 16, 16, 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: pending == 0 ? const Color(0xffe8f5e9) : const Color(0xfffff8e7),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: pending == 0 ? const Color(0xffa5d6a7) : const Color(0xffe2c46c),
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: pending == 0 ? const Color(0xff2e7d32) : const Color(0xff0b3c4d),
                  borderRadius: BorderRadius.circular(12),
                ),
                alignment: Alignment.center,
                child: Icon(
                  pending == 0 ? Icons.check_circle_rounded : Icons.cloud_upload_rounded,
                  size: 22,
                  color: pending == 0 ? Colors.white : const Color(0xffd4af37),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      pending == 0 ? 'Semua Data Terkirim' : '$pending Draft Menunggu',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: pending == 0 ? const Color(0xff1b5e20) : const Color(0xff3b2e04),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      pending == 0
                          ? 'Tersimpan aman di server Sangihe'
                          : 'Kirim saat koneksi internet tersedia',
                      style: TextStyle(
                        fontSize: 12,
                        color: pending == 0 ? const Color(0xff2e7d32) : const Color(0xff614c07),
                      ),
                    ),
                  ],
                ),
              ),
              if (pending > 0)
                FilledButton.icon(
                  onPressed: () => _sendAll(context),
                  icon: const Icon(Icons.send_rounded, size: 16),
                  label: const Text('Kirim Semua'),
                  style: FilledButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
            ],
          ),
        ),

        // Filter Horizontal Chips
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Row(
            children: [
              _filterChip('Semua', 'semua', all.length),
              const SizedBox(width: 8),
              _filterChip('Menunggu', 'queued', all.where((d) => (d.type == DraftType.rumah || d.type == DraftType.infrastruktur) && d.syncState == 'queued').length),
              const SizedBox(width: 8),
              _filterChip('Verifikasi', 'pending', all.where((d) => (d.type == DraftType.rumah || d.type == DraftType.infrastruktur) && d.syncState == 'pending').length),
              const SizedBox(width: 8),
              _filterChip('Gagal', 'failed', all.where((d) => (d.type == DraftType.rumah || d.type == DraftType.infrastruktur) && d.syncState == 'failed').length),
              const SizedBox(width: 8),
              _filterChip('Disetujui', 'accepted', all.where((d) => (d.type == DraftType.rumah || d.type == DraftType.infrastruktur) && d.syncState == 'accepted').length),
            ],
          ),
        ),
        const SizedBox(height: 12),

        // Draft List
        Expanded(
          child: drafts.isEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: const Color(0xff0b3c4d).withValues(alpha: 0.08),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(Icons.inbox_outlined, size: 48, color: Colors.grey.shade400),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Belum Ada Draft Pada Kategori Ini',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 14, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                  itemCount: drafts.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (context, index) => _draftTile(context, drafts[index]),
                ),
        ),
      ],
    );
  }

  Widget _filterChip(String label, String value, int count) {
    final isSelected = _filter == value;
    final primaryColor = Theme.of(context).colorScheme.primary;

    return ChoiceChip(
      label: Text('$label ($count)'),
      selected: isSelected,
      onSelected: (_) => setState(() => _filter = value),
      selectedColor: primaryColor.withValues(alpha: 0.15),
      backgroundColor: Colors.white,
      side: BorderSide(
        color: isSelected ? primaryColor : Colors.grey.shade300,
        width: isSelected ? 1.5 : 1,
      ),
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
        color: isSelected ? primaryColor : Colors.grey.shade700,
      ),
    );
  }

  Widget _draftTile(BuildContext context, DraftRecord draft) {
    final info = _stateInfo(draft);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(4),
        child: Column(
          children: [
            ListTile(
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              leading: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: info.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(14),
                ),
                alignment: Alignment.center,
                child: Icon(info.icon, color: info.color, size: 22),
              ),
              title: Text(
                draft.title,
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              subtitle: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(Icons.access_time_rounded, size: 12, color: Colors.grey.shade500),
                      const SizedBox(width: 4),
                      Text(
                        DateFormat('dd MMM yyyy, HH:mm').format(draft.createdAt),
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                  if (draft.errorMessage != null) ...[
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xfffde8e8),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xfff8b4b4)),
                      ),
                      child: Text(
                        draft.errorMessage!,
                        style: const TextStyle(fontSize: 11, color: Color(0xff9b1c1c), fontWeight: FontWeight.w600),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ],
              ),
              trailing: PopupMenuButton<String>(
                icon: Icon(Icons.more_vert_rounded, color: Colors.grey.shade600),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                onSelected: (v) {
                  if (v == 'edit') _editDraft(context, draft);
                  if (v == 'delete') _deleteDraft(context, draft);
                },
                itemBuilder: (_) => [
                  if ((draft.type == DraftType.rumah || draft.type == DraftType.infrastruktur) && (draft.syncState == 'queued' || draft.syncState == 'failed'))
                    const PopupMenuItem(
                      value: 'edit',
                      child: Row(
                        children: [
                          Icon(Icons.edit_rounded, size: 18),
                          SizedBox(width: 10),
                          Text('Edit Draft'),
                        ],
                      ),
                    ),
                  const PopupMenuItem(
                    value: 'delete',
                    child: Row(
                      children: [
                        Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xffa4463b)),
                        SizedBox(width: 10),
                        Text('Hapus Draft', style: TextStyle(color: Color(0xffa4463b))),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            if (draft.type == DraftType.keluarga || draft.type == DraftType.penduduk)
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 0, 16, 12),
                child: Text('Draft lama tidak lagi dikirim. Pilih data keluarga OPD saat mencatat Rumah.', style: TextStyle(fontSize: 11, color: Color(0xff7b4a00))),
              ),
            if ((draft.type == DraftType.rumah || draft.type == DraftType.infrastruktur) && (draft.syncState == 'queued' || draft.syncState == 'failed')) ...[
              const Divider(height: 1),
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 8, 12, 10),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: info.color.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        info.label,
                        style: TextStyle(fontSize: 11, color: info.color, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const Spacer(),
                    FilledButton.icon(
                      onPressed: () => _sendOne(context, draft),
                      icon: const Icon(Icons.send_rounded, size: 14),
                      label: const Text('Kirim'),
                      style: FilledButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            if (draft.syncState == 'accepted' || draft.batchStatus == 'disetujui') ...[
              const Divider(height: 1),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 10, 16, 12),
                child: Row(
                  children: [
                    const Icon(Icons.verified_rounded, size: 16, color: Color(0xff2e7d32)),
                    const SizedBox(width: 8),
                    const Text(
                      'Disetujui oleh Admin Kecamatan',
                      style: TextStyle(fontSize: 12, color: Color(0xff2e7d32), fontWeight: FontWeight.w700),
                    ),
                  ],
                ),
              ),
            ],
            if (draft.syncState == 'pending' && draft.batchStatus != 'disetujui') ...[
              const Divider(height: 1),
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 10, 16, 12),
                child: Row(
                  children: [
                    const Icon(Icons.hourglass_top_rounded, size: 16, color: Color(0xff1f5f77)),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Sudah dikirim, menunggu verifikasi Admin Kecamatan.',
                        style: TextStyle(fontSize: 12, color: Color(0xff1f5f77), fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  ({IconData icon, Color color, String label}) _stateInfo(DraftRecord draft) {
    final batchStatus = draft.batchStatus;
    if (batchStatus != null) {
      if (batchStatus == 'disetujui') return (icon: Icons.check_circle_rounded, color: const Color(0xff2e7d32), label: 'Disetujui');
      if (batchStatus == 'ditolak') return (icon: Icons.cancel_rounded, color: const Color(0xffa4463b), label: 'Ditolak');
      if (batchStatus == 'sedang_diverifikasi') return (icon: Icons.pending_actions_rounded, color: const Color(0xffd4af37), label: 'Sedang Diverifikasi');
      if (batchStatus == 'perlu_revisi') return (icon: Icons.edit_note_rounded, color: const Color(0xffd4af37), label: 'Perlu Revisi');
      if (batchStatus == 'menunggu_verifikasi') return (icon: Icons.hourglass_top_rounded, color: const Color(0xff1f5f77), label: 'Menunggu Verifikasi');
    }
    switch (draft.syncState) {
      case 'accepted':
        return (icon: Icons.check_circle_rounded, color: const Color(0xff2e7d32), label: 'Disetujui');
      case 'failed':
        return (icon: Icons.error_rounded, color: const Color(0xffa4463b), label: 'Gagal');
      case 'conflict':
        return (icon: Icons.warning_rounded, color: const Color(0xffd4af37), label: 'Konflik');
      case 'pending':
        return (icon: Icons.hourglass_top_rounded, color: const Color(0xff1f5f77), label: 'Menunggu Verifikasi');
      default:
        return (icon: Icons.cloud_upload_rounded, color: const Color(0xff1f5f77), label: 'Menunggu');
    }
  }

  Future<void> _sendAll(BuildContext context) async {
    final pending = _syncService.getPendingDrafts();
    if (pending.isEmpty) return;

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Kirim Semua Draft?'),
        content: Text('${pending.length} draft akan dikirim ke server Kabupaten Sangihe.\nPastikan koneksi internet aktif.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Kirim Sekarang')),
        ],
      ),
    );
    if (ok != true) return;

    if (!context.mounted) return;
    final overlay = _LoadingOverlay.show(context, 'Mengirim ${pending.length} draft...');
    try {
      final result = await _syncService.sync();
      overlay.remove();
      widget.onChanged();
      _loadDrafts();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${result.accepted} berhasil dikirim, ${result.failed} gagal'),
            backgroundColor: result.failed > 0 ? const Color(0xffa4463b) : const Color(0xff2e7d32),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      overlay.remove();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal mengirim: $e'),
            backgroundColor: const Color(0xffa4463b),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _sendOne(BuildContext context, DraftRecord draft) async {
    if (draft.type != DraftType.rumah && draft.type != DraftType.infrastruktur) return;
    final overlay = _LoadingOverlay.show(context, 'Mengirim draft...');
    try {
      final ok = await _syncService.retryDraft(draft.localId);
      overlay.remove();
      widget.onChanged();
      _loadDrafts();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(ok ? '${draft.title} berhasil dikirim.' : 'Gagal mengirim ${draft.title}'),
            backgroundColor: ok ? const Color(0xff2e7d32) : const Color(0xffa4463b),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      overlay.remove();
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal: $e'),
            backgroundColor: const Color(0xffa4463b),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _editDraft(BuildContext context, DraftRecord draft) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => DraftPage(
          store: widget.store,
          api: widget.api,
          userDesaId: widget.userDesaId,
          bootstrap: widget.bootstrap,
          initialDataset: draft.type.key,
          editDraft: draft,
          onSaved: () {
            widget.onChanged();
            _loadDrafts();
          },
        ),
      ),
    );
  }

  Future<void> _deleteDraft(BuildContext context, DraftRecord draft) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Hapus Draft?'),
        content: Text('Apakah Anda yakin ingin menghapus "${draft.title}"? Data lokal ini tidak dapat dikembalikan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(backgroundColor: const Color(0xffa4463b)),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    if (ok != true) return;
    await _syncService.deleteDraft(draft.localId);
    widget.onChanged();
    _loadDrafts();
  }
}

class _LoadingOverlay {
  static _LoadingOverlay show(BuildContext context, String message) {
    final entry = OverlayEntry(
      builder: (_) => Material(
        color: Colors.black.withValues(alpha: 0.5),
        child: Center(
          child: Container(
            padding: const EdgeInsets.all(28),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.2),
                  blurRadius: 20,
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const CircularProgressIndicator(color: Color(0xff0b3c4d)),
                const SizedBox(height: 20),
                Text(
                  message,
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: Color(0xff0b3c4d)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
    Overlay.of(context).insert(entry);
    return _LoadingOverlay._(entry);
  }

  final OverlayEntry _entry;
  _LoadingOverlay._(this._entry);
  void remove() => _entry.remove();
}
