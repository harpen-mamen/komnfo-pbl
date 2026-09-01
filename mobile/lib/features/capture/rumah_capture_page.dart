import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class RumahCapturePage extends StatefulWidget {
  const RumahCapturePage({super.key, required this.store, this.userDesaId, this.bootstrap, this.editDraft, required this.onSaved});

  final LocalStore store;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final DraftRecord? editDraft;
  final VoidCallback onSaved;

  @override
  State<RumahCapturePage> createState() => _RumahCapturePageState();
}

class _RumahCapturePageState extends State<RumahCapturePage> {
  final _formKey = GlobalKey<FormState>();
  final _kode = TextEditingController();
  final _alamat = TextEditingController();
  final _lat = TextEditingController();
  final _lng = TextEditingController();
  final _keterangan = TextEditingController();
  int? _desaId;
  int? _dusunId;
  String _status = 'Milik Sendiri';
  bool _saving = false;

  static const _statuses = ['Milik Sendiri', 'Kontrak', 'Sewa', 'Dinas', 'Lainnya'];

  @override
  void initState() {
    super.initState();
    // Primary source: userDesaId from session (always available after login)
    // Secondary: from bootstrap (may not be loaded yet)
    _desaId = widget.userDesaId ?? widget.bootstrap?.userDesaId;

    // Pre-fill from edit draft
    final draft = widget.editDraft;
    if (draft != null) {
      final p = draft.payload;
      _desaId = (p['desa_id'] as int?) ?? _desaId;
      _kode.text = (p['kode_rumah'] as String?) ?? '';
      _alamat.text = (p['alamat_lengkap'] as String?) ?? '';
      _lat.text = (p['latitude']?.toString() ?? '');
      _lng.text = (p['longitude']?.toString() ?? '');
      _keterangan.text = (p['keterangan'] as String?) ?? '';
      _dusunId = (p['dusun_id'] as int?) ?? _dusunId;
      final st = p['status_rumah'] as String?;
      if (st != null && _statuses.contains(st)) _status = st;
    }
  }

  @override
  void dispose() {
    _kode.dispose();
    _alamat.dispose();
    _lat.dispose();
    _lng.dispose();
    _keterangan.dispose();
    super.dispose();
  }

  Future<void> _useGps() async {
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      _message('Izin lokasi belum diberikan.', Colors.orange);
      return;
    }
    try {
      final position = await Geolocator.getCurrentPosition();
      _lat.text = position.latitude.toStringAsFixed(7);
      _lng.text = position.longitude.toStringAsFixed(7);
      if (mounted) setState(() {});
    } catch (_) {
      _message('Titik GPS belum dapat diambil.', Colors.red);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    // Resolve finalDesaId: edit draft > session user > bootstrap
    int? finalDesaId = _desaId;
    final draft = widget.editDraft;
    if (finalDesaId == null && draft != null) {
      finalDesaId = draft.payload['desa_id'] as int?;
    }
    if (finalDesaId == null) {
      finalDesaId = widget.userDesaId ?? widget.bootstrap?.userDesaId;
    }
    if (finalDesaId == null) {
      setState(() => _saving = false);
      _message('Akun belum terhubung ke desa. Hubungi Admin Kecamatan.', Colors.red);
      return;
    }

    final localId = draft?.localId ?? const Uuid().v4();
    final opId = draft?.operationId ?? const Uuid().v4();
    final newDraft = DraftRecord(
      localId: localId,
      operationId: opId,
      type: DraftType.rumah,
      title: 'Rumah ${_kode.text.trim()}',
      syncState: 'queued',
      createdAt: draft?.createdAt ?? DateTime.now(),
      payload: {
        'desa_id': finalDesaId, 'dusun_id': _dusunId, 'kode_rumah': _kode.text.trim(),
        'alamat_lengkap': _alamat.text.trim(), 'latitude': double.parse(_lat.text.trim()),
        'longitude': double.parse(_lng.text.trim()), 'status_rumah': _status,
        'keterangan': _keterangan.text.trim().isEmpty ? null : _keterangan.text.trim(),
      },
    );
    await widget.store.saveDraft(newDraft);
    if (!mounted) return;
    widget.onSaved();
    Navigator.of(context).pop();
  }

  void _message(String message, Color color) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), backgroundColor: color));

  @override
  Widget build(BuildContext context) {
    final desa = widget.bootstrap?.desa ?? const <ReferenceOption>[];
    final userDesa = desa.isNotEmpty ? desa.first : null;
    final dusun = (widget.bootstrap?.dusun ?? const <ReferenceOption>[]).where((item) => item.desaId == _desaId).toList();
    return Scaffold(
      appBar: AppBar(title: Text(widget.editDraft != null ? 'Edit Rumah' : 'Tambah Rumah')),
      body: Form(
        key: _formKey,
        child: ListView(padding: const EdgeInsets.all(16), children: [
          _info('Rumah menjadi titik awal relasi KK dan Penduduk. Ambil koordinat langsung dari lokasi rumah.'),
          const SizedBox(height: 16),
          _field(_kode, 'Kode Rumah', 'Contoh: R-001', required: true),
          const SizedBox(height: 12),
          _infoField('Desa', userDesa?.name ?? 'Tidak diketahui', Icons.location_city),
          if (dusun.isNotEmpty) ...[
            const SizedBox(height: 12),
            DropdownButtonFormField<int>(initialValue: _dusunId, decoration: const InputDecoration(labelText: 'Dusun (opsional)'), items: dusun.map((item) => DropdownMenuItem(value: item.id, child: Text(item.name))).toList(), onChanged: (value) => setState(() => _dusunId = value)),
          ],
          const SizedBox(height: 12),
          _field(_alamat, 'Alamat lengkap', 'Jalan, RT/RW, nomor rumah', required: true, maxLines: 2),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(initialValue: _status, decoration: const InputDecoration(labelText: 'Status rumah'), items: _statuses.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(), onChanged: (value) => setState(() => _status = value ?? _statuses.first)),
          const SizedBox(height: 20),
          Text('Titik GPS', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          Row(children: [Expanded(child: _field(_lat, 'Latitude', '3.5500000', required: true, keyboardType: const TextInputType.numberWithOptions(decimal: true))), const SizedBox(width: 8), Expanded(child: _field(_lng, 'Longitude', '125.5000000', required: true, keyboardType: const TextInputType.numberWithOptions(decimal: true)))]),
          const SizedBox(height: 10),
          OutlinedButton.icon(onPressed: _useGps, icon: const Icon(Icons.my_location), label: const Text('Ambil titik dari GPS perangkat')),
          const SizedBox(height: 12),
          _field(_keterangan, 'Keterangan', 'Opsional', maxLines: 3),
          const SizedBox(height: 24),
          FilledButton.icon(onPressed: _saving ? null : _save, icon: _saving ? const SizedBox.square(dimension: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.save), label: Text(_saving ? 'Menyimpan...' : (widget.editDraft != null ? 'Simpan Perubahan' : 'Simpan ke Draft'))),
        ]),
      ),
    );
  }

  Widget _info(String text) => Card(color: Colors.blue.shade50, child: Padding(padding: const EdgeInsets.all(14), child: Row(children: [const Icon(Icons.info_outline, color: Colors.blue), const SizedBox(width: 10), Expanded(child: Text(text))])));

  Widget _infoField(String label, String value, IconData icon) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.teal.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.teal.shade200),
        ),
        child: Row(children: [
          Icon(icon, color: Colors.teal.shade700, size: 22),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600, letterSpacing: 0.5)),
            const SizedBox(height: 2),
            Text(value, style: TextStyle(fontSize: 15, color: Colors.teal.shade800, fontWeight: FontWeight.w700)),
          ])),
          Icon(Icons.lock, color: Colors.grey.shade400, size: 18),
        ]),
      );

  Widget _field(TextEditingController controller, String label, String hint, {bool required = false, int maxLines = 1, TextInputType? keyboardType}) => TextFormField(controller: controller, maxLines: maxLines, keyboardType: keyboardType, decoration: InputDecoration(labelText: required ? '$label *' : label, hintText: hint), validator: required ? (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null : null);
}
