import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class KeluargaCapturePage extends StatefulWidget {
  const KeluargaCapturePage({
    super.key,
    required this.store,
    this.userDesaId,
    this.bootstrap,
    this.editDraft,
    required this.onSaved,
  });

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
      'jumlah_anggota': int.parse(_jumlah.text.trim()),
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
    await widget.store.saveDraft(
      DraftRecord(
        localId: localId,
        operationId: opId,
        type: DraftType.keluarga,
        title: 'KK ${_kk.text.trim()}',
        payload: payload,
        syncState: 'queued',
        createdAt: draft?.createdAt ?? DateTime.now(),
      ),
    );
    if (!mounted) return;
    widget.onSaved();
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final houses = _houses();
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.editDraft != null ? 'Edit Kartu Keluarga' : 'Pendataan KK Baru'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          children: [
            _infoCard('Pilih bangunan rumah acuan terlebih dahulu. Kartu Keluarga akan dikaitkan langsung ke rumah tersebut.'),
            const SizedBox(height: 20),
            DropdownButtonFormField<String>(
              initialValue: _houseKey,
              decoration: const InputDecoration(
                labelText: 'Rumah Acuan (Wajib) *',
                prefixIcon: Icon(Icons.home_rounded, color: Color(0xff0b3c4d)),
              ),
              items: houses
                  .map((house) => DropdownMenuItem(
                        value: house.key,
                        child: Text(house.label, overflow: TextOverflow.ellipsis),
                      ))
                  .toList(),
              onChanged: (value) => setState(() => _houseKey = value),
              validator: (value) => value == null ? 'Rumah acuan wajib dipilih' : null,
            ),
            const SizedBox(height: 14),
            _field(_kk, 'Nomor Kartu Keluarga (KK)', '16 digit nomor KK Dukcapil', required: true, keyboardType: TextInputType.number, icon: Icons.badge_rounded, kkValidator: true),
            const SizedBox(height: 14),
            _field(_kepala, 'Nama Kepala Keluarga', 'Sesuai dokumen resmi KK', required: true, icon: Icons.person_rounded),
            const SizedBox(height: 14),
            _field(_jumlah, 'Jumlah Anggota Keluarga', 'Minimal 1 orang', required: true, keyboardType: TextInputType.number, numericMin: 1, icon: Icons.groups_rounded),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _status,
              decoration: const InputDecoration(
                labelText: 'Kategori Status Ekonomi',
                prefixIcon: Icon(Icons.payments_rounded, color: Color(0xff0b3c4d)),
              ),
              items: _statuses.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(),
              onChanged: (value) => setState(() => _status = value ?? _statuses.first),
            ),
            const SizedBox(height: 32),
            FilledButton.icon(
              onPressed: _saving || houses.isEmpty ? null : _save,
              icon: _saving
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.save_rounded),
              label: Text(houses.isEmpty
                  ? 'Catat Rumah Terlebih Dahulu'
                  : (widget.editDraft != null ? 'Simpan Perubahan' : 'Simpan Ke Draft Lokal')),
              style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 18)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoCard(String text) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xfffff8e7),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xffe2c46c)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xffd4af37).withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.lightbulb_outline_rounded, color: Color(0xff3b2e04), size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 12, color: Color(0xff3b2e04), fontWeight: FontWeight.w600, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }

  Widget _field(
    TextEditingController controller,
    String label,
    String hint, {
    bool required = false,
    TextInputType? keyboardType,
    int? numericMin,
    required IconData icon,
    bool kkValidator = false,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: required ? '$label *' : label,
        hintText: hint,
        prefixIcon: Icon(icon, color: const Color(0xff0b3c4d)),
      ),
      validator: (value) {
        final v = (value ?? '').trim();
        if (required && v.isEmpty) return '$label wajib diisi';
        if (v.isNotEmpty && kkValidator) {
          if (!RegExp(r'^\d{16}$').hasMatch(v)) {
            return 'Nomor KK harus 16 digit angka';
          }
        }
        if (v.isNotEmpty && numericMin != null) {
          final n = int.tryParse(v);
          if (n == null) return '$label harus berupa angka';
          if (n < numericMin) return '$label minimal $numericMin';
        }
        return null;
      },
    );
  }
}
