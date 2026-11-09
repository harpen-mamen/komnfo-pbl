import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class PendudukCapturePage extends StatefulWidget {
  const PendudukCapturePage({
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
      final g = p['jenis_kelamin'] as String?;
      if (g != null && _genders.contains(g)) _gender = g;
      final a = p['agama'] as String?;
      if (a != null && _religions.contains(a)) _agama = a;
      final k = p['status_perkawinan'] as String?;
      if (k != null && _marriages.contains(k)) _perkawinan = k;
      final ed = p['pendidikan'] as String?;
      if (ed != null && _education.contains(ed)) _pendidikan = ed;
      final sk = p['status_dalam_keluarga'] as String?;
      if (sk != null && _familyStatuses.contains(sk)) _statusKeluarga = sk;
      final sp = p['status_penduduk'] as String?;
      if (sp != null) _statusPenduduk = sp;
      final keluargaId = p['keluarga_id'] as int?;
      final keluargaLocalId = p['keluarga_local_id'] as String?;
      if (keluargaId != null) {
        _familyKey = 'server:$keluargaId';
      } else if (keluargaLocalId != null) {
        _familyKey = 'local:$keluargaLocalId';
      }
    }
  }

  @override
  void dispose() {
    for (final controller in [_nik, _nama, _tempatLahir, _tanggalLahir, _pekerjaan, _penghasilan, _hp]) {
      controller.dispose();
    }
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
      'nik': _nik.text.trim(),
      'nama_lengkap': _nama.text.trim(),
      'jenis_kelamin': _gender,
      'tempat_lahir': _tempatLahir.text.trim(),
      'tanggal_lahir': _tanggalLahir.text.trim(),
      'agama': _agama,
      'status_perkawinan': _perkawinan,
      'pendidikan': _pendidikan,
      'pekerjaan': _pekerjaan.text.trim().isEmpty ? null : _pekerjaan.text.trim(),
      'penghasilan': _penghasilan.text.trim().isEmpty ? null : double.tryParse(_penghasilan.text.trim()),
      'status_dalam_keluarga': _statusKeluarga,
      'nomor_hp': _hp.text.trim().isEmpty ? null : _hp.text.trim(),
      'status_penduduk': _statusPenduduk,
    };
    if (_familyKey!.startsWith('server:')) {
      payload['keluarga_id'] = int.parse(_familyKey!.substring(7));
    } else {
      payload['keluarga_local_id'] = _familyKey!.substring(6);
    }
    final draft = widget.editDraft;
    final localId = draft?.localId ?? const Uuid().v4();
    final opId = draft?.operationId ?? const Uuid().v4();
    await widget.store.saveDraft(
      DraftRecord(
        localId: localId,
        operationId: opId,
        type: DraftType.penduduk,
        title: _nama.text.trim(),
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
    final families = _families();
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.editDraft != null ? 'Edit Data Penduduk' : 'Pendataan Penduduk Baru'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          children: [
            _infoCard('Setiap data penduduk harus dihubungkan ke Kartu Keluarga (KK) yang sudah terhubung ke data rumah.'),
            const SizedBox(height: 20),
            DropdownButtonFormField<String>(
              initialValue: _familyKey,
              decoration: const InputDecoration(
                labelText: 'Kartu Keluarga Acuan (Wajib) *',
                prefixIcon: Icon(Icons.family_restroom_rounded, color: Color(0xff0b3c4d)),
              ),
              items: families
                  .map((family) => DropdownMenuItem(
                        value: family.key,
                        child: Text(family.label, overflow: TextOverflow.ellipsis),
                      ))
                  .toList(),
              onChanged: (value) => setState(() => _familyKey = value),
              validator: (value) => value == null ? 'KK acuan wajib dipilih' : null,
            ),
            const SizedBox(height: 14),
            _field(_nik, 'NIK Dukcapil', '16 digit NIK', required: true, keyboardType: TextInputType.number, nikValidator: true, icon: Icons.badge_rounded),
            const SizedBox(height: 14),
            _field(_nama, 'Nama Lengkap', 'Sesuai KTP / KK', required: true, icon: Icons.person_rounded),
            const SizedBox(height: 14),
            _select('Jenis Kelamin', _gender, _genders, Icons.wc_rounded, (value) => setState(() => _gender = value)),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(child: _field(_tempatLahir, 'Tempat Lahir', 'Misal: Tahuna', required: true, icon: Icons.location_city_rounded)),
                const SizedBox(width: 12),
                Expanded(child: _dateField('Tanggal Lahir *', _tanggalLahir)),
              ],
            ),
            const SizedBox(height: 14),
            _select('Agama', _agama, _religions, Icons.church_rounded, (value) => setState(() => _agama = value)),
            const SizedBox(height: 14),
            _select('Status Perkawinan', _perkawinan, _marriages, Icons.favorite_rounded, (value) => setState(() => _perkawinan = value)),
            const SizedBox(height: 14),
            _select('Pendidikan Terakhir', _pendidikan, _education, Icons.school_rounded, (value) => setState(() => _pendidikan = value)),
            const SizedBox(height: 14),
            _field(_pekerjaan, 'Pekerjaan Utama', 'Misal: Nelayan, Petani, PNS', icon: Icons.work_rounded),
            const SizedBox(height: 14),
            _field(_penghasilan, 'Estimasi Penghasilan Bulan (Rp)', 'Opsional', keyboardType: const TextInputType.numberWithOptions(decimal: true), icon: Icons.attach_money_rounded),
            const SizedBox(height: 14),
            _select('Status Dalam Keluarga', _statusKeluarga, _familyStatuses, Icons.groups_rounded, (value) => setState(() => _statusKeluarga = value)),
            const SizedBox(height: 14),
            _field(_hp, 'Nomor Handphone / WhatsApp', 'Opsional', keyboardType: TextInputType.phone, icon: Icons.phone_android_rounded),
            const SizedBox(height: 14),
            _select('Status Penduduk', _statusPenduduk, const ['Tetap', 'Domisili'], Icons.house_siding_rounded, (value) => setState(() => _statusPenduduk = value)),
            const SizedBox(height: 32),
            FilledButton.icon(
              onPressed: _saving || families.isEmpty ? null : _save,
              icon: _saving
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.save_rounded),
              label: Text(families.isEmpty
                  ? 'Catat KK Terlebih Dahulu'
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
        color: const Color(0xffebf1f0),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xffd5e0df)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xff0b3c4d).withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.info_outline_rounded, color: Color(0xff0b3c4d), size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 12, color: Color(0xff0b3c4d), fontWeight: FontWeight.w600, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }

  Widget _select(String label, String value, List<String> options, IconData icon, ValueChanged<String> onChanged) {
    return DropdownButtonFormField<String>(
      initialValue: value,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: const Color(0xff0b3c4d)),
      ),
      items: options.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(),
      onChanged: (item) => onChanged(item ?? value),
    );
  }

  Widget _dateField(String label, TextEditingController controller) {
    return TextFormField(
      controller: controller,
      readOnly: true,
      decoration: InputDecoration(
        labelText: label,
        hintText: 'YYYY-MM-DD',
        prefixIcon: const Icon(Icons.calendar_today_rounded, color: Color(0xff0b3c4d)),
      ),
      onTap: () async {
        final date = await showDatePicker(
          context: context,
          initialDate: DateTime.tryParse(controller.text)?.isAfter(DateTime(1900)) == true
              ? DateTime.parse(controller.text)
              : DateTime.now(),
          firstDate: DateTime(1920),
          lastDate: DateTime.now(),
        );
        if (date != null) {
          controller.text = '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
        }
      },
      validator: (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null,
    );
  }

  Widget _field(
    TextEditingController controller,
    String label,
    String hint, {
    bool required = false,
    TextInputType? keyboardType,
    bool nikValidator = false,
    required IconData icon,
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
        if (v.isNotEmpty && nikValidator) {
          if (!RegExp(r'^\d{16}$').hasMatch(v)) {
            return 'NIK harus 16 digit angka';
          }
        }
        return null;
      },
    );
  }
}
