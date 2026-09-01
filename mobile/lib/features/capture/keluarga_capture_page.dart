import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class KeluargaCapturePage extends StatefulWidget {
  const KeluargaCapturePage({super.key, required this.store, this.userDesaId, this.bootstrap, this.editDraft, required this.onSaved});

  final LocalStore store;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final DraftRecord? editDraft;
  final VoidCallback onSaved;

  @override
  State<KeluargaCapturePage> createState() => _KeluargaCapturePageState();
}

class _HouseChoice {
  const _HouseChoice({required this.key, required this.label});
  final String key;
  final String label;
}

class _KeluargaCapturePageState extends State<KeluargaCapturePage> {
  final _formKey = GlobalKey<FormState>();
  final _kk = TextEditingController();
  final _kepala = TextEditingController();
  final _jumlah = TextEditingController(text: '1');
  String? _houseKey;
  String _status = 'Menengah';
  bool _saving = false;

  static const _statuses = ['Miskin', 'Menengah', 'Mampu'];

  @override
  void initState() {
    super.initState();
    final draft = widget.editDraft;
    if (draft != null) {
      final p = draft.payload;
      _kk.text = (p['nomor_kk'] as String?) ?? '';
      _kepala.text = (p['nama_kepala_keluarga'] as String?) ?? '';
      _jumlah.text = '${p['jumlah_anggota'] ?? 1}';
      final st = p['status_ekonomi'] as String?;
      if (st != null && _statuses.contains(st)) _status = st;
      // Resolve rumah key
      final rumahId = p['rumah_id'] as int?;
      final rumahLocalId = p['rumah_local_id'] as String?;
      if (rumahId != null) {
        _houseKey = 'server:$rumahId';
      } else if (rumahLocalId != null) {
        _houseKey = 'local:$rumahLocalId';
      }
    }
  }

  @override
  void dispose() {
    _kk.dispose();
    _kepala.dispose();
    _jumlah.dispose();
    super.dispose();
  }

  List<_HouseChoice> _houses() {
    final choices = <_HouseChoice>[];
    for (final house in widget.bootstrap?.rumah ?? const <RumahRecord>[]) {
      choices.add(_HouseChoice(key: 'server:${house.id}', label: '${house.kodeRumah} — ${house.alamat}'));
    }
    for (final draft in widget.store.drafts().where((item) => item.type == DraftType.rumah)) {
      if (draft.serverId != null) {
        choices.add(_HouseChoice(key: 'server:${draft.serverId}', label: '${draft.title} — Terkirim'));
      } else if (draft.syncState != 'accepted') {
        choices.add(_HouseChoice(key: 'local:${draft.localId}', label: '${draft.title} — Draft lokal'));
      }
    }
    return choices;
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    final payload = <String, dynamic>{
      'nomor_kk': _kk.text.trim(),
      'nama_kepala_keluarga': _kepala.text.trim(),
      'jumlah_anggota': int.tryParse(_jumlah.text.trim()) ?? 0,
      'status_ekonomi': _status,
    };
    if (_houseKey!.startsWith('server:')) {
      payload['rumah_id'] = int.parse(_houseKey!.substring(7));
    } else {
      payload['rumah_local_id'] = _houseKey!.substring(6);
    }
    final draft = widget.editDraft;
    final localId = draft?.localId ?? const Uuid().v4();
    final opId = draft?.operationId ?? const Uuid().v4();
    await widget.store.saveDraft(DraftRecord(localId: localId, operationId: opId, type: DraftType.keluarga, title: 'KK ${_kk.text.trim()}', payload: payload, syncState: 'queued', createdAt: draft?.createdAt ?? DateTime.now()));
    if (!mounted) return;
    widget.onSaved();
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final houses = _houses();
    return Scaffold(
      appBar: AppBar(title: Text(widget.editDraft != null ? 'Edit Kartu Keluarga' : 'Tambah Kartu Keluarga')),
      body: Form(
        key: _formKey,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          Card(color: Colors.orange.shade50, child: const Padding(padding: EdgeInsets.all(14), child: Text('Pilih rumah terlebih dahulu. KK baru akan dikaitkan ke rumah tersebut.'))),
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(initialValue: _houseKey, decoration: const InputDecoration(labelText: 'Rumah *'), items: houses.map((house) => DropdownMenuItem(value: house.key, child: Text(house.label, overflow: TextOverflow.ellipsis))).toList(), onChanged: (value) => setState(() => _houseKey = value), validator: (value) => value == null ? 'Rumah wajib dipilih' : null),
          const SizedBox(height: 12),
          _field(_kk, 'Nomor KK', '16 digit nomor KK', required: true, keyboardType: TextInputType.number),
          const SizedBox(height: 12),
          _field(_kepala, 'Nama kepala keluarga', 'Sesuai dokumen KK', required: true),
          const SizedBox(height: 12),
          _field(_jumlah, 'Jumlah anggota', '0', required: true, keyboardType: TextInputType.number),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(initialValue: _status, decoration: const InputDecoration(labelText: 'Status ekonomi'), items: _statuses.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(), onChanged: (value) => setState(() => _status = value ?? _statuses.first)),
          const SizedBox(height: 24),
          FilledButton.icon(onPressed: _saving || houses.isEmpty ? null : _save, icon: _saving ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.save), label: Text(houses.isEmpty ? 'Buat Rumah Terlebih Dahulu' : (widget.editDraft != null ? 'Simpan Perubahan' : 'Simpan ke Draft'))),
        ]),
      ),
    );
  }

  Widget _field(TextEditingController controller, String label, String hint, {bool required = false, TextInputType? keyboardType}) => TextFormField(controller: controller, keyboardType: keyboardType, decoration: InputDecoration(labelText: required ? '$label *' : label, hintText: hint), validator: required ? (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null : null);
}
