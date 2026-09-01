import 'api_client.dart';
import 'local_store.dart';
import 'models.dart';

class SyncResult {
  const SyncResult({
    required this.accepted,
    required this.failed,
    this.conflicts = 0,
  });

  final int accepted;
  final int failed;
  final int conflicts;
}

/// Maps backend batch statuses to mobile sync states.
/// The flow is:
/// queued → pending (batch created, awaiting AdminKecamatan review)
///          → accepted (AdminKecamatan approved the batch)
///          → failed (validation error on server)
class SyncService {
  SyncService({required this.api, required this.store});

  final ApiClient api;
  final LocalStore store;

  /// Get all pending drafts (not yet accepted)
  List<DraftRecord> getPendingDrafts() {
    return store.drafts()
        .where((d) => d.syncState != 'accepted' && d.syncState != 'conflict')
        .toList()
      ..sort((a, b) => a.type.index.compareTo(b.type.index));
  }

  /// Get all drafts by type
  List<DraftRecord> getDraftsByType(DraftType type) {
    return store.drafts().where((d) => d.type == type).toList();
  }

  /// Get draft statistics
  Map<String, int> getStats() {
    final drafts = store.drafts();
    return {
      'total': drafts.length,
      'queued': drafts.where((d) => d.syncState == 'queued').length,
      'pending': drafts.where((d) => d.syncState == 'pending').length,
      'accepted': drafts.where((d) => d.syncState == 'accepted').length,
      'failed': drafts.where((d) => d.syncState == 'failed').length,
      'rumah': drafts.where((d) => d.type == DraftType.rumah).length,
      'keluarga': drafts.where((d) => d.type == DraftType.keluarga).length,
      'penduduk': drafts.where((d) => d.type == DraftType.penduduk).length,
    };
  }

  /// Sync all pending drafts to server.
  /// Server creates SubmissionBatch (status: menunggu_verifikasi).
  /// Draft stays 'pending' until AdminKecamatan approves.
  Future<SyncResult> syncAll() async {
    final pending = getPendingDrafts();
    if (pending.isEmpty) {
      return const SyncResult(accepted: 0, failed: 0);
    }

    try {
      final results = await api.pushDrafts(pending);
      final resultByLocalId = {for (final r in results) r['local_id'] as String: r};

      var accepted = 0;
      var failed = 0;
      var conflicts = 0;

      for (final draft in pending) {
        final result = resultByLocalId[draft.localId];
        final status = result?['status'] as String?;
        final batchId = (result?['batch_id'] as num?)?.toInt();
        final batchStatus = result?['batch_status'] as String?;
        final batchStatusLabel = result?['batch_status_label'] as String?;

        if (status == 'failed') {
          failed++;
          await store.saveDraft(draft.copyWith(
            syncState: 'failed',
            batchId: batchId,
            batchStatus: batchStatus,
            batchStatusLabel: batchStatusLabel,
            errorMessage: result?['message'] as String? ?? 'Gagal mengirim',
          ));
        } else {
          // 'pending' or 'accepted' — server created a batch for this
          final newState = status ?? 'pending';
          if (newState == 'accepted') accepted++;
          await store.saveDraft(draft.copyWith(
            syncState: newState,
            serverId: (result?['server_id'] as num?)?.toInt(),
            batchId: batchId,
            batchStatus: batchStatus,
            batchStatusLabel: batchStatusLabel,
            clearErrorMessage: true,
          ));
        }
      }

      return SyncResult(accepted: accepted, failed: failed, conflicts: conflicts);
    } catch (e) {
      // Mark all as failed
      for (final draft in pending) {
        await store.saveDraft(draft.copyWith(
          syncState: 'failed',
          errorMessage: e.toString(),
        ));
      }
      return SyncResult(accepted: 0, failed: pending.length);
    }
  }

  /// Retry a specific draft.
  Future<bool> retryDraft(String localId) async {
    final matches = store.drafts().where((d) => d.localId == localId).toList();
    if (matches.isEmpty) return false;

    final draft = matches.first;
    await store.saveDraft(draft.copyWith(syncState: 'queued', clearErrorMessage: true));

    try {
      final results = await api.pushDrafts([draft]);
      final result = results.isEmpty ? null : results.first;
      final status = result?['status'] as String?;

      if (status == 'failed') {
        await store.saveDraft(draft.copyWith(
          syncState: 'failed',
          errorMessage: result?['message'] as String? ?? 'Gagal mengirim',
        ));
        return false;
      }

      // 'pending' or 'accepted'
      await store.saveDraft(draft.copyWith(
        syncState: status ?? 'pending',
        serverId: (result?['server_id'] as num?)?.toInt(),
        batchId: (result?['batch_id'] as num?)?.toInt(),
        batchStatus: result?['batch_status'] as String?,
        batchStatusLabel: result?['batch_status_label'] as String?,
        clearErrorMessage: true,
      ));
      return true;
    } catch (e) {
      await store.saveDraft(draft.copyWith(
        syncState: 'failed',
        errorMessage: e.toString(),
      ));
      return false;
    }
  }

  /// Pull batch status updates from server.
  /// This updates drafts when AdminKecamatan approves/rejects batches.
  Future<void> refreshBatchStatuses() async {
    final drafts = store.drafts()
        .where((d) => d.batchId != null && d.syncState == 'pending')
        .toList();
    if (drafts.isEmpty) return;

    try {
      final batchStatuses = await api.fetchBatchStatuses(drafts.map((d) => d.batchId!).toSet());
      for (final draft in drafts) {
        final serverBatch = batchStatuses[draft.batchId];
        if (serverBatch != null) {
          final batchStatus = serverBatch['status'] as String?;
          final batchStatusLabel = serverBatch['status_label'] as String?;

          // Batch approved by AdminKecamatan
          if (batchStatus == 'disetujui') {
            await store.saveDraft(draft.copyWith(
              syncState: 'accepted',
              batchStatus: batchStatus,
              batchStatusLabel: batchStatusLabel,
            ));
          // Batch rejected by AdminKecamatan
          } else if (batchStatus == 'ditolak') {
            await store.saveDraft(draft.copyWith(
              syncState: 'failed',
              batchStatus: batchStatus,
              batchStatusLabel: batchStatusLabel,
              errorMessage: 'Batch ditolak Admin Kecamatan. Mohon perbaiki dan kirim ulang.',
            ));
          } else if (batchStatus != draft.batchStatus) {
            // Update status if changed
            await store.saveDraft(draft.copyWith(
              batchStatus: batchStatus,
              batchStatusLabel: batchStatusLabel,
            ));
          }
        }
      }
    } catch (_) {
      // Silently ignore pull failures — data will sync on next push
    }
  }

  Future<SyncResult> sync() => syncAll();

  Future<void> retry(String localId) async {
    await retryDraft(localId);
  }

  /// Delete a draft
  Future<void> deleteDraft(String localId) async {
    await store.removeDraft(localId);
  }
}
