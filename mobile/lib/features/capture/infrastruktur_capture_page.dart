import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:uuid/uuid.dart';

import '../../core/local_store.dart';
import '../../core/models.dart';

class InfrastrukturCapturePage extends StatefulWidget {
  const InfrastrukturCapturePage({super.key, required this.store, required this.userDesaId, this.bootstrap, this.editDraft, required this.onSaved});
  final LocalStore store;
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final DraftRecord? editDraft;
  final VoidCallback onSaved;

  @override
  State<InfrastrukturCapturePage> createState() => _InfrastrukturCapturePageState();
}

class _InfrastrukturCapturePageState extends State<InfrastrukturCapturePage> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _code = TextEditingController();
  final _address = TextEditingController();
  final _lat = TextEditingController();
  final _lng = TextEditingController();
  final _notes = TextEditingController();
  String _type = 'jalan';
  int? _desaId;
  bool _saving = false;
  bool _gps = false;

  static const _types = {'jalan':'Jalan', 'jembatan':'Jembatan', 'drainase':'Drainase', 'lampu_jalan':'Lampu Jalan', 'air_bersih':'Sarana Air Bersih', 'lainnya':'Infrastruktur Lain'};

  @override
  void initState() {
    super.initState();
    final villages = widget.bootstrap?.desa ?? const <ReferenceOption>[];
    _desaId = widget.userDesaId ?? widget.bootstrap?.userDesaId ?? (villages.length == 1 ? villages.first.id : null);
    final payload = widget.editDraft?.payload;
    if (payload != null) {
      _desaId = (payload['desa_id'] as num?)?.toInt() ?? _desaId;
      _type = payload['jenis'] as String? ?? _type;
      _name.text = payload['nama'] as String? ?? '';
      _code.text = payload['kode'] as String? ?? '';
      _address.text = payload['alamat'] as String? ?? '';
      _lat.text = payload['latitude']?.toString() ?? '';
      _lng.text = payload['longitude']?.toString() ?? '';
      _notes.text = payload['keterangan'] as String? ?? '';
    }
  }

  @override
  void dispose() {
    for (final controller in [_name, _code, _address, _lat, _lng, _notes]) { controller.dispose(); }
    super.dispose();
  }

  Future<void> _getGps() async {
    setState(() => _gps = true);
    try {
      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) throw Exception('Izin lokasi belum diberikan.');
      final position = await Geolocator.getCurrentPosition(locationSettings: const LocationSettings(accuracy: LocationAccuracy.high, timeLimit: Duration(seconds: 15)));
      _lat.text = position.latitude.toStringAsFixed(7);
      _lng.text = position.longitude.toStringAsFixed(7);
    } catch (error) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$error')));
    } finally {
      if (mounted) setState(() => _gps = false);
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    if (_desaId == null) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih desa tugas terlebih dahulu.'))); return; }
    final latitude = double.tryParse(_lat.text.trim());
    final longitude = double.tryParse(_lng.text.trim());
    if (latitude == null || longitude == null || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Koordinat GPS tidak valid.')));
      return;
    }
    setState(() => _saving = true);
    final old = widget.editDraft;
    await widget.store.saveDraft(DraftRecord(
      localId: old?.localId ?? const Uuid().v4(),
      operationId: old?.operationId ?? const Uuid().v4(),
      type: DraftType.infrastruktur,
      title: '${_types[_type]}: ${_name.text.trim()}',
      payload: {'desa_id': _desaId, 'jenis':_type, 'nama':_name.text.trim(), 'kode':_code.text.trim(), 'alamat':_address.text.trim(), 'latitude':latitude, 'longitude':longitude, 'keterangan':_notes.text.trim()},
      syncState: 'queued',
      createdAt: old?.createdAt ?? DateTime.now(),
    ));
    if (!mounted) return;
    widget.onSaved();
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final villages = widget.bootstrap?.desa ?? const <ReferenceOption>[];
    final assignedVillage = widget.userDesaId ?? widget.bootstrap?.userDesaId;
    return Scaffold(
    appBar: AppBar(title: Text(widget.editDraft == null ? 'Catat Infrastruktur' : 'Edit Infrastruktur')),
    body: Form(key: _formKey, child: ListView(padding: const EdgeInsets.all(20), children: [
      Text('Titik disimpan untuk desa tugas Anda dan diajukan ke alur verifikasi lapangan.', style: Theme.of(context).textTheme.bodyMedium),
      const SizedBox(height: 18),
      if (assignedVillage != null)
        Text('Desa tugas: ${villages.where((item) => item.id == assignedVillage).firstOrNull?.name ?? assignedVillage}')
      else
        DropdownButtonFormField<int>(initialValue: _desaId, decoration: const InputDecoration(labelText: 'Pilih Desa Tugas'), items: villages.map((item) => DropdownMenuItem(value: item.id, child: Text(item.name))).toList(), validator: (value) => value == null ? 'Pilih desa tugas' : null, onChanged: (value) => setState(() => _desaId = value)),
      const SizedBox(height: 12),
      DropdownButtonFormField<String>(initialValue: _type, decoration: const InputDecoration(labelText: 'Jenis Infrastruktur'), items: _types.entries.map((entry) => DropdownMenuItem(value: entry.key, child: Text(entry.value))).toList(), onChanged: (value) => setState(() => _type = value ?? 'lainnya')),
      const SizedBox(height: 12),
      TextFormField(controller: _name, decoration: const InputDecoration(labelText: 'Nama / Keterangan objek *'), validator: (value) => (value ?? '').trim().isEmpty ? 'Nama wajib diisi' : null),
      const SizedBox(height: 12),
      TextFormField(controller: _code, decoration: const InputDecoration(labelText: 'Kode aset (opsional)')),
      const SizedBox(height: 12),
      TextFormField(controller: _address, decoration: const InputDecoration(labelText: 'Lokasi / alamat')),
      const SizedBox(height: 12),
      Row(children: [Expanded(child: TextFormField(controller: _lat, keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true), decoration: const InputDecoration(labelText: 'Latitude *'))), const SizedBox(width: 10), Expanded(child: TextFormField(controller: _lng, keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true), decoration: const InputDecoration(labelText: 'Longitude *')))]),
      const SizedBox(height: 12),
      OutlinedButton.icon(onPressed: _gps ? null : _getGps, icon: const Icon(Icons.gps_fixed), label: Text(_gps ? 'Mengambil lokasi…' : 'Ambil koordinat GPS')),
      const SizedBox(height: 12),
      TextFormField(controller: _notes, maxLines: 3, decoration: const InputDecoration(labelText: 'Catatan lapangan')),
      const SizedBox(height: 24),
      FilledButton.icon(onPressed: _saving ? null : _save, icon: const Icon(Icons.save), label: Text(_saving ? 'Menyimpan…' : 'Simpan Draft')),
    ])),
  );
  }
}
