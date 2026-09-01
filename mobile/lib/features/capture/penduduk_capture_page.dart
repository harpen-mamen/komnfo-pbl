import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class PendudukCapturePage extends StatefulWidget {
  const PendudukCapturePage({super.key, required this.store, this.userDesaId, this.bootstrap, this.editDraft, required this.onSaved});

  final LocalStore store;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final DraftRecord? editDraft;
  final VoidCallback onSaved;

  @override
  State<PendudukCapturePage> createState() => _PendudukCapturePageState();
}

class _FamilyChoice {
  const _FamilyChoice({required this.key, required this.label});
  final String key;
  final String label;
}

class _PendudukCapturePageState extends State<PendudukCapturePage> {
  final _formKey = GlobalKey<FormState>();
  final _nik = TextEditingController();
  final _nama = TextEditingController();
  final _tempatLahir = TextEditingController();
  final _tanggalLahir = TextEditingController();
  final _pekerjaan = TextEditingController();
  final _penghasilan = TextEditingController();
  final _hp = TextEditingController();
  String? _familyKey;
  String _gender = 'Laki-laki';
  String _agama = 'Kristen';
  String _perkawinan = 'Belum Kawin';
  String _pendidikan = 'SMA';
  String _statusKeluarga = 'Kepala Keluarga';
  String _statusPenduduk = 'Tetap';
  bool _saving = false;

  static const _genders = ['Laki-laki', 'Perempuan'];
  static const _religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'];
  static const _marriages = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
  static const _education = ['Tidak Sekolah', 'SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'];
  static const _familyStatuses = ['Kepala Keluarga', 'Istri', 'Anak', 'Menantu', 'Cucu', 'Orang Tua', 'Lainnya'];

  @override
  void initState() {
    super.initState();
    final draft = widget.editDraft;
    if (draft != null) {
      final p = draft.payload;
      _nik.text = (p['nik'] as String?) ?? '';
      _nama.text = (p['nama_lengkap'] as String?) ?? '';
      _tempatLahir.text = (p['tempat_lahir'] as String?) ?? '';
      _tanggalLahir.text = (p['tanggal_lahir']?.toString() ?? '');
      _pekerjaan.text = (p['pekerjaan'] as String?) ?? '';
      _penghasilan.text = (p['penghasilan']?.toString() ?? '');
      _hp.text = (p['nomor_hp'] as String?) ?? '';
      final g = p['jenis_kelamin'] as String?; if (g != null && _genders.contains(g)) _gender = g;
      final a = p['agama'] as String?; if (a != null && _religions.contains(a)) _agama = a;
      final k = p['status_perkawinan'] as String?; if (k != null && _marriages.contains(k)) _perkawinan = k;
      final ed = p['pendidikan'] as String?; if (ed != null && _education.contains(ed)) _pendidikan = ed;
      final sk = p['status_dalam_keluarga'] as String?; if (sk != null && _familyStatuses.contains(sk)) _statusKeluarga = sk;
      final sp = p['status_penduduk'] as String?; if (sp != null) _statusPenduduk = sp;
      final keluargaId = p['keluarga_id'] as int?;
      final keluargaLocalId = p['keluarga_local_id'] as String?;
      if (keluargaId != null) _familyKey = 'server:$keluargaId';
      else if (keluargaLocalId != null) _familyKey = 'local:$keluargaLocalId';
    }
  }

  @override
  void dispose() {
    for (final controller in [_nik, _nama, _tempatLahir, _tanggalLahir, _pekerjaan, _penghasilan, _hp]) { controller.dispose(); }
    super.dispose();
  }

  List<_FamilyChoice> _families() {
    final choices = <_FamilyChoice>[];
    for (final family in widget.bootstrap?.keluarga ?? const <KeluargaRecord>[]) {
      choices.add(_FamilyChoice(key: 'server:${family.id}', label: '${family.nomorKk} — ${family.kepala}'));
    }
    for (final draft in widget.store.drafts().where((item) => item.type == DraftType.keluarga)) {
      if (draft.serverId != null) {
        choices.add(_FamilyChoice(key: 'server:${draft.serverId}', label: '${draft.title} — Terkirim'));
      } else if (draft.syncState != 'accepted') {
        choices.add(_FamilyChoice(key: 'local:${draft.localId}', label: '${draft.title} — Draft lokal'));
      }
    }
    return choices;
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);
    final payload = <String, dynamic>{
      'nik': _nik.text.trim(), 'nama_lengkap': _nama.text.trim(), 'jenis_kelamin': _gender,
      'tempat_lahir': _tempatLahir.text.trim(), 'tanggal_lahir': _tanggalLahir.text.trim(),
      'agama': _agama, 'status_perkawinan': _perkawinan, 'pendidikan': _pendidikan,
      'pekerjaan': _pekerjaan.text.trim().isEmpty ? null : _pekerjaan.text.trim(),
      'penghasilan': _penghasilan.text.trim().isEmpty ? null : double.tryParse(_penghasilan.text.trim()),
      'status_dalam_keluarga': _statusKeluarga,
      'nomor_hp': _hp.text.trim().isEmpty ? null : _hp.text.trim(), 'status_penduduk': _statusPenduduk,
    };
    if (_familyKey!.startsWith('server:')) {
      payload['keluarga_id'] = int.parse(_familyKey!.substring(7));
    } else {
      payload['keluarga_local_id'] = _familyKey!.substring(6);
    }
    final draft = widget.editDraft;
    final localId = draft?.localId ?? const Uuid().v4();
    final opId = draft?.operationId ?? const Uuid().v4();
    await widget.store.saveDraft(DraftRecord(localId: localId, operationId: opId, type: DraftType.penduduk, title: _nama.text.trim(), payload: payload, syncState: 'queued', createdAt: draft?.createdAt ?? DateTime.now()));
    if (!mounted) return;
    widget.onSaved();
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final families = _families();
    return Scaffold(
      appBar: AppBar(title: Text(widget.editDraft != null ? 'Edit Penduduk' : 'Tambah Penduduk')),
      body: Form(
        key: _formKey,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          Card(color: Colors.teal.shade50, child: const Padding(padding: EdgeInsets.all(14), child: Text('Penduduk harus ditempatkan pada KK yang sudah terhubung ke rumah.'))),
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(initialValue: _familyKey, decoration: const InputDecoration(labelText: 'Kartu Keluarga *'), items: families.map((family) => DropdownMenuItem(value: family.key, child: Text(family.label, overflow: TextOverflow.ellipsis))).toList(), onChanged: (value) => setState(() => _familyKey = value), validator: (value) => value == null ? 'KK wajib dipilih' : null),
          const SizedBox(height: 12),
          _field(_nik, 'NIK', 'Nomor induk kependudukan', required: true, keyboardType: TextInputType.number),
          const SizedBox(height: 12),
          _field(_nama, 'Nama lengkap', 'Sesuai KTP/KK', required: true),
          const SizedBox(height: 12),
          _select('Jenis kelamin', _gender, _genders, (value) => setState(() => _gender = value)),
          const SizedBox(height: 12),
          Row(children: [Expanded(child: _field(_tempatLahir, 'Tempat lahir', '', required: true)), const SizedBox(width: 8), Expanded(child: _dateField('Tanggal lahir *', _tanggalLahir))]),
          const SizedBox(height: 12),
          _select('Agama', _agama, _religions, (value) => setState(() => _agama = value)),
          const SizedBox(height: 12),
          _select('Status perkawinan', _perkawinan, _marriages, (value) => setState(() => _perkawinan = value)),
          const SizedBox(height: 12),
          _select('Pendidikan terakhir', _pendidikan, _education, (value) => setState(() => _pendidikan = value)),
          const SizedBox(height: 12),
          _field(_pekerjaan, 'Pekerjaan', 'Opsional'),
          const SizedBox(height: 12),
          _field(_penghasilan, 'Penghasilan', 'Opsional', keyboardType: const TextInputType.numberWithOptions(decimal: true)),
          const SizedBox(height: 12),
          _select('Status dalam keluarga', _statusKeluarga, _familyStatuses, (value) => setState(() => _statusKeluarga = value)),
          const SizedBox(height: 12),
          _field(_hp, 'Nomor HP', 'Opsional', keyboardType: TextInputType.phone),
          const SizedBox(height: 12),
          _select('Status penduduk', _statusPenduduk, const ['Tetap', 'Domisili'], (value) => setState(() => _statusPenduduk = value)),
          const SizedBox(height: 24),
          FilledButton.icon(onPressed: _saving || families.isEmpty ? null : _save, icon: _saving ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.save), label: Text(families.isEmpty ? 'Buat KK Terlebih Dahulu' : (widget.editDraft != null ? 'Simpan Perubahan' : 'Simpan ke Draft'))),
        ]),
      ),
    );
  }

  Widget _select(String label, String value, List<String> options, ValueChanged<String> onChanged) => DropdownButtonFormField<String>(initialValue: value, decoration: InputDecoration(labelText: label), items: options.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(), onChanged: (item) => onChanged(item ?? value));

  Widget _dateField(String label, TextEditingController controller) => TextFormField(
        controller: controller,
        readOnly: true,
        decoration: InputDecoration(
          labelText: label,
          hintText: 'YYYY-MM-DD',
          suffixIcon: const Icon(Icons.calendar_today, size: 20),
        ),
        onTap: () async {
          final date = await showDatePicker(
            context: context,
            initialDate: DateTime.tryParse(controller.text)?.isAfter(DateTime(1900)) == true
                ? DateTime.parse(controller.text)
                : DateTime.now(),
            firstDate: DateTime(1940),
            lastDate: DateTime.now(),
          );
          if (date != null) {
            controller.text = '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
          }
        },
        validator: (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null,
      );

  Widget _field(TextEditingController controller, String label, String hint, {bool required = false, TextInputType? keyboardType}) => TextFormField(controller: controller, keyboardType: keyboardType, decoration: InputDecoration(labelText: required ? '$label *' : label, hintText: hint), validator: required ? (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null : null);
}
