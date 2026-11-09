import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:uuid/uuid.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';

class RumahCapturePage extends StatefulWidget {
  const RumahCapturePage({
    super.key,
    required this.store,
    required this.api,
    this.userDesaId,
    this.bootstrap,
    this.editDraft,
    required this.onSaved,
  });

  final LocalStore store;
  final ApiClient api;
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
  bool _gettingGps = false;
  bool _loadingFamilies = false;
  List<MobileFamilyOption> _families = const [];
  final Set<int> _selectedFamilyIds = {};

  static const _statuses = ['Milik Sendiri', 'Kontrak', 'Sewa', 'Dinas', 'Lainnya'];

  @override
  void initState() {
    super.initState();
    final villages = widget.bootstrap?.desa ?? const <ReferenceOption>[];
    _desaId = widget.userDesaId ?? widget.bootstrap?.userDesaId ?? (villages.length == 1 ? villages.first.id : null);

    final draft = widget.editDraft;
    if (draft != null) {
      final p = draft.payload;
      _desaId = (p['desa_id'] as num?)?.toInt() ?? _desaId;
      _kode.text = (p['kode_rumah'] as String?) ?? '';
      _alamat.text = (p['alamat_lengkap'] as String?) ?? '';
      _lat.text = (p['latitude']?.toString() ?? '');
      _lng.text = (p['longitude']?.toString() ?? '');
      _keterangan.text = (p['keterangan'] as String?) ?? '';
      _dusunId = (p['dusun_id'] as int?) ?? _dusunId;
      final st = p['status_rumah'] as String?;
      if (st != null && _statuses.contains(st)) _status = st;
      _selectedFamilyIds.addAll((p['keluarga_ids'] as List<dynamic>? ?? const []).whereType<num>().map((id) => id.toInt()));
    }
    _loadFamilies();
  }

  Future<void> _loadFamilies() async {
    final desaId = _desaId;
    if (desaId == null) {
      if (mounted) setState(() { _families = const []; _loadingFamilies = false; });
      return;
    }
    setState(() => _loadingFamilies = true);
    try {
      final families = await widget.api.families(desaId: desaId);
      if (mounted) setState(() => _families = families);
    } catch (_) {
      // Family records remain optional if the OPD has not enabled a source yet.
    } finally {
      if (mounted) setState(() => _loadingFamilies = false);
    }
  }

  Future<void> _chooseFamilies() async {
    final selected = Set<int>.from(_selectedFamilyIds);
    final result = await showDialog<Set<int>>(
      context: context,
      builder: (context) => StatefulBuilder(builder: (context, setDialogState) => AlertDialog(
        title: const Text('Pilih Keluarga dari Data OPD'),
        content: SizedBox(width: 420, child: _families.isEmpty
            ? const Text('Belum ada data keluarga terbit untuk desa ini.')
            : ListView(shrinkWrap: true, children: [for (final family in _families)
                CheckboxListTile(
                  value: selected.contains(family.id),
                  title: Text(family.namaKepala.isEmpty ? 'Keluarga ${family.nomorKk}' : family.namaKepala),
                  subtitle: Text('No. KK ${family.nomorKk}'),
                  onChanged: (checked) => setDialogState(() => checked == true ? selected.add(family.id) : selected.remove(family.id)),
                ),
              ])),
        actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(context, selected), child: const Text('Simpan Pilihan'))],
      )),
    );
    if (result != null && mounted) setState(() => _selectedFamilyIds
      ..clear()
      ..addAll(result));
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
    setState(() => _gettingGps = true);
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
      setState(() => _gettingGps = false);
      _message('Izin lokasi belum diberikan.', const Color(0xffd4af37));
      return;
    }
    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 0,
          timeLimit: Duration(seconds: 15),
        ),
      );
      _lat.text = position.latitude.toStringAsFixed(7);
      _lng.text = position.longitude.toStringAsFixed(7);
      _message('Titik GPS presisi berhasil dikunci.', const Color(0xff2e7d32));
    } catch (_) {
      _message('Titik GPS belum dapat diambil. Pastikan di area terbuka.', const Color(0xffa4463b));
    } finally {
      if (mounted) setState(() => _gettingGps = false);
    }
  }

  String? _validateLatLng() {
    final latText = _lat.text.trim();
    final lngText = _lng.text.trim();
    if (latText.isEmpty) return 'Latitude wajib diisi';
    if (lngText.isEmpty) return 'Longitude wajib diisi';
    final lat = double.tryParse(latText);
    final lng = double.tryParse(lngText);
    if (lat == null) return 'Latitude harus angka';
    if (lng == null) return 'Longitude harus angka';
    if (lat < -90 || lat > 90) return 'Latitude harus antara -90 dan 90';
    if (lng < -180 || lng > 180) return 'Longitude harus antara -180 dan 180';
    return null;
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    final coordError = _validateLatLng();
    if (coordError != null) {
      _message(coordError, const Color(0xffa4463b));
      return;
    }
    if (_desaId == null) {
      _message('Pilih desa tugas terlebih dahulu.', const Color(0xffa4463b));
      return;
    }
    setState(() => _saving = true);

    int? finalDesaId = _desaId;
    final draft = widget.editDraft;
    if (finalDesaId == null && draft != null) {
      finalDesaId = draft.payload['desa_id'] as int?;
    }
    finalDesaId ??= widget.userDesaId ?? widget.bootstrap?.userDesaId ?? _desaId;
    if (finalDesaId == null) {
      setState(() => _saving = false);
      _message('Akun belum terhubung ke desa.', const Color(0xffa4463b));
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
        'desa_id': finalDesaId,
        'dusun_id': _dusunId,
        'kode_rumah': _kode.text.trim(),
        'alamat_lengkap': _alamat.text.trim(),
        'latitude': double.parse(_lat.text.trim()),
        'longitude': double.parse(_lng.text.trim()),
        'status_rumah': _status,
        'keluarga_ids': _selectedFamilyIds.toList(),
        'keterangan': _keterangan.text.trim().isEmpty ? null : _keterangan.text.trim(),
      },
    );
    await widget.store.saveDraft(newDraft);
    if (!mounted) return;
    widget.onSaved();
    Navigator.of(context).pop();
  }

  void _message(String message, Color color) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final desa = widget.bootstrap?.desa ?? const <ReferenceOption>[];
    final selectedDesa = desa.where((item) => item.id == _desaId).firstOrNull;
    final dusun = (widget.bootstrap?.dusun ?? const <ReferenceOption>[]).where((item) => item.desaId == _desaId).toList();

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.editDraft != null ? 'Edit Data Rumah' : 'Pendataan Rumah Baru'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
          children: [
            _infoCard('Catat titik Rumah. Keluarga dipilih dari data OPD yang sudah terbit; data KK dan Penduduk tidak diubah oleh aplikasi ini.'),
            const SizedBox(height: 20),
            _sectionTitle('Identitas & Wilayah Rumah'),
            const SizedBox(height: 12),
            _field(_kode, 'Kode Bangunan / Rumah', 'Contoh: R-001 / RMH-01', required: true, icon: Icons.tag_rounded),
            const SizedBox(height: 14),
            if (widget.userDesaId != null || widget.bootstrap?.userDesaId != null)
              _infoField('Wilayah Desa Terkunci', selectedDesa?.name ?? 'Wilayah Desa Petugas', Icons.location_city_rounded)
            else if (desa.length > 1)
              DropdownButtonFormField<int>(
                initialValue: _desaId,
                decoration: const InputDecoration(labelText: 'Pilih Desa Tugas', prefixIcon: Icon(Icons.location_city_rounded)),
                items: desa.map((item) => DropdownMenuItem(value: item.id, child: Text(item.name))).toList(),
                validator: (value) => value == null ? 'Pilih desa tugas' : null,
                onChanged: (value) { setState(() { _desaId = value; _dusunId = null; _selectedFamilyIds.clear(); }); _loadFamilies(); },
              )
            else
              _infoField('Desa Tugas', selectedDesa?.name ?? 'Pilih desa', Icons.location_city_rounded),
            if (dusun.isNotEmpty) ...[
              const SizedBox(height: 14),
              DropdownButtonFormField<int>(
                initialValue: _dusunId,
                decoration: const InputDecoration(
                  labelText: 'Dusun / Lingkungan (Opsional)',
                  prefixIcon: Icon(Icons.holiday_village_rounded, color: Color(0xff0b3c4d)),
                ),
                items: dusun.map((item) => DropdownMenuItem(value: item.id, child: Text(item.name))).toList(),
                onChanged: (value) => setState(() => _dusunId = value),
              ),
            ],
            const SizedBox(height: 14),
            _field(_alamat, 'Alamat Lengkap', 'Jalan, RT/RW, nomor rumah', required: true, maxLines: 2, icon: Icons.map_rounded),
            const SizedBox(height: 14),
            DropdownButtonFormField<String>(
              initialValue: _status,
              decoration: const InputDecoration(
                labelText: 'Status Kepemilikan Rumah',
                prefixIcon: Icon(Icons.home_work_rounded, color: Color(0xff0b3c4d)),
              ),
              items: _statuses.map((item) => DropdownMenuItem(value: item, child: Text(item))).toList(),
              onChanged: (value) => setState(() => _status = value ?? _statuses.first),
            ),
            const SizedBox(height: 14),
            OutlinedButton.icon(
              onPressed: _loadingFamilies || _desaId == null ? null : _chooseFamilies,
              icon: _loadingFamilies ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.family_restroom_rounded),
              label: Text(_loadingFamilies ? 'Memuat keluarga OPD…' : 'Pilih Keluarga (${_selectedFamilyIds.length})'),
            ),
            const SizedBox(height: 28),
            _sectionTitle('Koordinat Lokasi Presisi (GPS)'),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(child: _field(_lat, 'Latitude', '3.5500000', required: true, keyboardType: const TextInputType.numberWithOptions(decimal: true), icon: Icons.my_location_rounded)),
                const SizedBox(width: 12),
                Expanded(child: _field(_lng, 'Longitude', '125.5000000', required: true, keyboardType: const TextInputType.numberWithOptions(decimal: true), icon: Icons.place_rounded)),
              ],
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _gettingGps ? null : _useGps,
              icon: _gettingGps
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.gps_fixed_rounded),
              label: Text(_gettingGps ? 'Mengunci Lokasi GPS...' : 'Ambil Titik Lokasi Presisi GPS'),
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
                side: const BorderSide(color: Color(0xff0b3c4d), width: 1.5),
              ),
            ),
            const SizedBox(height: 24),
            _field(_keterangan, 'Catatan Tambahan', 'Opsional (misal: warna cat rumah, kondisi khusus)', maxLines: 3, icon: Icons.note_alt_rounded),
            const SizedBox(height: 32),
            FilledButton.icon(
              onPressed: _saving ? null : _save,
              icon: _saving
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.save_rounded),
              label: Text(_saving ? 'Menyimpan...' : (widget.editDraft != null ? 'Simpan Perubahan' : 'Simpan Ke Draft Lokal')),
              style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 18)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xff0b3c4d)),
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

  Widget _infoField(String label, String value, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xfff4f7f6),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xff0b3c4d), size: 22),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600, letterSpacing: 0.3)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(fontSize: 15, color: Color(0xff0b3c4d), fontWeight: FontWeight.w800)),
              ],
            ),
          ),
          Icon(Icons.lock_rounded, color: Colors.grey.shade400, size: 18),
        ],
      ),
    );
  }

  Widget _field(
    TextEditingController controller,
    String label,
    String hint, {
    bool required = false,
    int maxLines = 1,
    TextInputType? keyboardType,
    required IconData icon,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: required ? '$label *' : label,
        hintText: hint,
        prefixIcon: Icon(icon, color: const Color(0xff0b3c4d)),
      ),
      validator: required
          ? (value) => value == null || value.trim().isEmpty ? '$label wajib diisi' : null
          : null,
    );
  }
}
