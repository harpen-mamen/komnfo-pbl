import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../core/models.dart';

class MapPage extends StatefulWidget {
  const MapPage({super.key, this.userDesaId, this.bootstrap, required this.onAddHouse});
  final int? userDesaId;
  final MobileBootstrap? bootstrap;
  final VoidCallback onAddHouse;
  @override State<MapPage> createState() => _MapPageState();
}

class _MapPageState extends State<MapPage> {
  final _defaultCenter = const LatLng(3.5500, 125.5000);
  RumahRecord? _selected;
  LatLng get _center {
    if (_selected != null) return LatLng(_selected!.latitude, _selected!.longitude);
    final houses = widget.bootstrap?.rumah ?? [];
    if (houses.isEmpty) return _defaultCenter;
    final valid = houses.where((h) => h.latitude != 0 && h.longitude != 0).toList();
    if (valid.isEmpty) return _defaultCenter;
    return LatLng(valid.first.latitude, valid.first.longitude);
  }
  @override Widget build(BuildContext context) {
    final houses = widget.bootstrap?.rumah ?? const [];
    final validHouses = houses.where((h) => h.latitude != 0 && h.longitude != 0).toList();
    return Scaffold(
      body: Stack(children: [
        FlutterMap(options: MapOptions(initialCenter: _center, initialZoom: _selected != null ? 17 : 13, onTap: (_, __) => setState(() => _selected = null)),
          children: [
            TileLayer(urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', userAgentPackageName: 'com.example.sipdt_mobile'),
            MarkerLayer(markers: validHouses.map((house) {
              final isSelected = _selected?.id == house.id;
              return Marker(point: LatLng(house.latitude, house.longitude), width: isSelected ? 56 : 44, height: isSelected ? 56 : 44,
                child: GestureDetector(onTap: () => setState(() => _selected = house),
                  child: AnimatedContainer(duration: const Duration(milliseconds: 200),
                    decoration: BoxDecoration(color: _houseColor(house).withOpacity(isSelected ? 1 : 0.85), shape: BoxShape.circle,
                      border: Border.all(color: isSelected ? Colors.white : Colors.white70, width: isSelected ? 3 : 2),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(isSelected ? 0.4 : 0.2), blurRadius: isSelected ? 12 : 6, offset: const Offset(0, 2))]),
                    child: Icon(Icons.home, color: Colors.white, size: isSelected ? 30 : 22))));
            }).toList()),
            RichAttributionWidget(attributions: [TextSourceAttribution('OpenStreetMap contributors')]),
          ]),
        if (validHouses.isEmpty) Center(child: Container(margin: const EdgeInsets.all(32), padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.95), borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 16)]),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Icon(Icons.home_work_outlined, size: 48, color: Colors.grey.shade400), const SizedBox(height: 12),
            Text('Belum ada rumah', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600, color: Colors.grey.shade700)),
            const SizedBox(height: 4),
            Text('Tambah rumah pertama menggunakan tombol di bawah.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey.shade500, fontSize: 13)),
          ]))),
        if (_selected != null) _housePanel(context, _selected!),
        Positioned(left: 12, bottom: _selected != null ? 220 : 100, child: Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.95), borderRadius: BorderRadius.circular(10), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 8)]),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            _legendItem(Colors.teal.shade600, 'Rumah'), const SizedBox(height: 4),
            Text(validHouses.length == 1 ? '1 titik rumah' : '${validHouses.length} titik rumah', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
          ]))),
      ]),
      floatingActionButton: Column(mainAxisSize: MainAxisSize.min, children: [
        FloatingActionButton.small(heroTag: 'center', backgroundColor: Colors.white, onPressed: () => setState(() => _selected = null), child: Icon(Icons.center_focus_strong, color: Colors.grey.shade700, size: 20)),
        const SizedBox(height: 8),
        FloatingActionButton.extended(heroTag: 'add', onPressed: widget.onAddHouse, icon: const Icon(Icons.add_location_alt), label: const Text('Tambah Rumah')),
      ]),
    );
  }

  Widget _housePanel(BuildContext context, RumahRecord house) {
    return Positioned(left: 12, right: 12, bottom: 12, child: Container(
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.15), blurRadius: 16, offset: const Offset(0, -2))]),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Padding(padding: const EdgeInsets.fromLTRB(16, 14, 8, 10), child: Row(children: [
          Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: _houseColor(house).withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)), child: Icon(Icons.home, color: _houseColor(house), size: 24)),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(house.kodeRumah, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
            const SizedBox(height: 2),
            Text(house.alamat, style: TextStyle(color: Colors.grey.shade600, fontSize: 13), maxLines: 2, overflow: TextOverflow.ellipsis),
          ])),
          IconButton(onPressed: () => setState(() => _selected = null), icon: const Icon(Icons.close, size: 22), color: Colors.grey.shade500),
        ])),
        const Divider(height: 1),
        Padding(padding: const EdgeInsets.fromLTRB(16, 10, 16, 14), child: Row(children: [
          _infoChip(Icons.family_restroom, house.keluargaCount == 1 ? '1 KK' : '${house.keluargaCount} KK'),
          const SizedBox(width: 8),
          _infoChip(Icons.location_on, '${house.latitude.toStringAsFixed(5)}, ${house.longitude.toStringAsFixed(5)}'),
          const Spacer(),
          Container(padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(color: _houseColor(house).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(20), border: Border.all(color: _houseColor(house).withOpacity(0.3))),
            child: Text(house.status, style: TextStyle(color: _houseColor(house), fontSize: 11, fontWeight: FontWeight.w600))),
        ])),
      ]),
    ));
  }

  Widget _infoChip(IconData icon, String text) {
    return Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(20)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 13, color: Colors.grey.shade600), const SizedBox(width: 4),
        Text(text, style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
      ]));
  }

  Widget _legendItem(Color color, String label) {
    return Row(mainAxisSize: MainAxisSize.min, children: [
      Container(width: 12, height: 12, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
      const SizedBox(width: 6),
      Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
    ]);
  }

  Color _houseColor(RumahRecord house) {
    if (house.keluargaCount == 0) return Colors.red.shade600;
    if (house.keluargaCount <= 2) return Colors.orange.shade600;
    return Colors.teal.shade600;
  }
}