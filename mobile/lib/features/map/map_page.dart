import 'dart:async';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';

class MapPage extends StatefulWidget {
  const MapPage({super.key, this.bootstrap, required this.store, required this.api, required this.onRefresh, required this.onAddHouse, required this.onAddInfrastructure});

  final MobileBootstrap? bootstrap;
  final LocalStore store;
  final ApiClient api;
  final Future<void> Function() onRefresh;
  final VoidCallback onAddHouse;
  final VoidCallback onAddInfrastructure;

  @override
  State<MapPage> createState() => _MapPageState();
}

class _MapPageState extends State<MapPage> {
  /// Pusat fallback jika tidak ada rumah dan tidak ada GPS.
  /// Koordinat pusat Kabupaten Kepulauan Sangihe.
  static const _fallbackCenter = LatLng(3.5500, 125.5000);

  /// Batas marker yang di-render agar tidak menyebabkan lag.
  static const _maxRenderedMarkers = 300;

  final MapController _mapController = MapController();
  int? _selectedHouseId;
  int? _selectedInfrastructureId;
  LatLng? _myLocation;
  bool _locating = false;
  bool _satellite = false;
  bool _loadingPoints = false;
  int _refreshGeneration = 0;
  List<RumahRecord> _houses = const [];
  List<InfrastructureRecord> _infrastructure = const [];

  LatLng get _center {
    final selectedHouse = _houses.where((point) => point.id == _selectedHouseId).firstOrNull;
    if (selectedHouse != null) return LatLng(selectedHouse.latitude, selectedHouse.longitude);
    final selectedInfrastructure = _infrastructure.where((point) => point.id == _selectedInfrastructureId).firstOrNull;
    if (selectedInfrastructure != null) return LatLng(selectedInfrastructure.latitude, selectedInfrastructure.longitude);
    if (_myLocation != null) return _myLocation!;
    final points = <LatLng>[
      ..._houses.where((h) => h.latitude != 0 && h.longitude != 0).map((h) => LatLng(h.latitude, h.longitude)),
      ..._infrastructure.where((p) => p.latitude != 0 && p.longitude != 0).map((p) => LatLng(p.latitude, p.longitude)),
    ];
    if (points.isEmpty) return _fallbackCenter;
    final avgLat = points.map((point) => point.latitude).reduce((a, b) => a + b) / points.length;
    final avgLng = points.map((point) => point.longitude).reduce((a, b) => a + b) / points.length;
    return LatLng(avgLat, avgLng);
  }

  @override
  void initState() {
    super.initState();
    _refreshPoints();
  }

  @override
  void didUpdateWidget(covariant MapPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.bootstrap != widget.bootstrap) _refreshPoints();
  }

  Future<void> _refreshPoints() async {
    final generation = ++_refreshGeneration;
    _loadingPoints = true;
    final bootstrap = widget.bootstrap;
    final housesById = <String, RumahRecord>{};
    final infrastructureById = <String, InfrastructureRecord>{};
    for (final house in bootstrap?.rumah ?? const <RumahRecord>[]) {
      housesById['server-${house.id}'] = house;
    }
    for (final item in bootstrap?.infrastruktur ?? const <InfrastructureRecord>[]) {
      infrastructureById['server-${item.id}'] = item;
    }
    for (final draft in widget.store.drafts()) {
      final lat = (draft.payload['latitude'] as num?)?.toDouble() ?? 0;
      final lng = (draft.payload['longitude'] as num?)?.toDouble() ?? 0;
      final desaId = (draft.payload['desa_id'] as num?)?.toInt() ?? widget.bootstrap?.userDesaId ?? 0;
      if (lat == 0 && lng == 0) continue;
      if (draft.type == DraftType.rumah) {
        housesById['local-${draft.operationId ?? draft.localId}'] = RumahRecord(
          id: draft.serverId ?? draft.localId.hashCode,
          operationId: draft.operationId,
          desaId: desaId,
          dusunId: (draft.payload['dusun_id'] as num?)?.toInt(),
          kodeRumah: draft.payload['kode_rumah'] as String? ?? draft.title,
          alamat: draft.payload['alamat_lengkap'] as String? ?? '',
          latitude: lat,
          longitude: lng,
          status: draft.payload['status_rumah'] as String? ?? 'Lainnya',
          keluargaCount: (draft.payload['keluarga_ids'] as List<dynamic>? ?? const []).length,
          isLocal: draft.syncState != 'accepted',
          isVerified: draft.syncState == 'accepted' || draft.batchStatus == 'disetujui',
        );
      } else if (draft.type == DraftType.infrastruktur) {
        infrastructureById['local-${draft.operationId ?? draft.localId}'] = InfrastructureRecord(
          id: draft.serverId ?? draft.localId.hashCode,
          operationId: draft.operationId,
          desaId: desaId,
          jenis: draft.payload['jenis'] as String? ?? 'lainnya',
          nama: draft.payload['nama'] as String? ?? draft.title,
          kode: draft.payload['kode'] as String? ?? '',
          alamat: draft.payload['alamat'] as String? ?? '',
          latitude: lat,
          longitude: lng,
          isLocal: draft.syncState != 'accepted',
          isVerified: draft.syncState == 'accepted' || draft.batchStatus == 'disetujui',
        );
      }
    }
    void mergeServerPoint(Map<String, dynamic> point) {
      final type = point['jenis'] as String? ?? 'rumah';
      final statusApproved = point['is_verified'] as bool? ?? false;
      final batchStatus = point['batch_status'] as String?;
      final opId = point['operation_id'] as String?;
      if (type == 'rumah') {
        final house = RumahRecord(
          id: (point['id'] as num?)?.toInt() ?? 0,
          operationId: opId,
          desaId: (point['desa_id'] as num?)?.toInt() ?? 0,
          kodeRumah: point['kode'] as String? ?? '',
          alamat: point['alamat'] as String? ?? point['nama'] as String? ?? '',
          latitude: (point['latitude'] as num?)?.toDouble() ?? 0,
          longitude: (point['longitude'] as num?)?.toDouble() ?? 0,
          status: (point['attributes'] as Map?)?['status_rumah'] as String? ?? 'Lainnya',
          keluargaCount: (point['keluarga_dataset_value_ids'] as List<dynamic>? ?? const []).length,
          isLocal: false,
          isVerified: statusApproved,
        );
        housesById['server-${house.id}'] = house;
        if (opId != null) housesById.remove('local-$opId');
      } else {
        final item = InfrastructureRecord(
          id: (point['id'] as num?)?.toInt() ?? 0,
          operationId: opId,
          desaId: (point['desa_id'] as num?)?.toInt() ?? 0,
          jenis: type,
          nama: point['nama'] as String? ?? '-',
          kode: point['kode'] as String? ?? '',
          alamat: point['alamat'] as String? ?? '',
          latitude: (point['latitude'] as num?)?.toDouble() ?? 0,
          longitude: (point['longitude'] as num?)?.toDouble() ?? 0,
          isLocal: false,
          isVerified: statusApproved,
        );
        infrastructureById['server-${item.id}'] = item;
        if (opId != null) infrastructureById.remove('local-$opId');
      }
    }
    for (final point in bootstrap?.infrastruktur.map((item) => <String, dynamic>{
          'id': item.id, 'operation_id': item.operationId, 'desa_id': item.desaId, 'jenis': item.jenis,
          'nama': item.nama, 'kode': item.kode, 'alamat': item.alamat, 'latitude': item.latitude,
          'longitude': item.longitude, 'is_verified': item.isVerified,
        }) ?? const <Map<String, dynamic>>[]) {
      mergeServerPoint(point);
    }
    if (mounted) {
      setState(() {
        _houses = housesById.values.toList(growable: false);
        _infrastructure = infrastructureById.values.toList(growable: false);
      });
    }
    try {
      final points = await widget.api.fieldPoints();
      if (!mounted || generation != _refreshGeneration) return;
      for (final point in points) { mergeServerPoint(point); }
    } catch (_) {
      // Local and cached points remain visible while offline.
    } finally {
      if (generation == _refreshGeneration) {
        _loadingPoints = false;
        if (mounted) setState(() {
          _houses = housesById.values.toList(growable: false);
          _infrastructure = infrastructureById.values.toList(growable: false);
        });
      }
    }
  }

  Future<void> _goToMyLocation() async {
    if (_locating) return;
    setState(() => _locating = true);
    try {
      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied || permission == LocationPermission.deniedForever) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Izin lokasi belum diberikan.')),
          );
        }
        return;
      }
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 15),
        ),
      );
      final here = LatLng(position.latitude, position.longitude);
      _mapController.move(here, 16);
      if (mounted) {
        setState(() {
          _myLocation = here;
          _selectedHouseId = null;
          _selectedInfrastructureId = null;
        });
      }
    } on TimeoutException {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('GPS membutuhkan waktu terlalu lama. Coba di area terbuka.')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Lokasi GPS tidak dapat diambil.')),
        );
      }
    } finally {
      if (mounted) setState(() => _locating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final validHouses = _houses.where((h) => h.latitude != 0 && h.longitude != 0).toList();
    final validInfrastructure = _infrastructure.where((p) => p.latitude != 0 && p.longitude != 0).toList();
          if (_selectedHouseId != null && !validHouses.any((house) => house.id == _selectedHouseId)) _selectedHouseId = null;
          if (_selectedInfrastructureId != null && !validInfrastructure.any((point) => point.id == _selectedInfrastructureId)) _selectedInfrastructureId = null;
    final allMarkerCount = validHouses.length + validInfrastructure.length;
    final truncated = allMarkerCount > _maxRenderedMarkers;
    final renderedHouses = validHouses.take(_maxRenderedMarkers).toList();
    final renderedInfrastructure = validInfrastructure.take((_maxRenderedMarkers - renderedHouses.length).clamp(0, _maxRenderedMarkers)).toList();
    final colors = Theme.of(context).colorScheme;

    return Scaffold(
      extendBodyBehindAppBar: true,
      body: Stack(
        children: [
          FlutterMap(
            mapController: _mapController,
            options: MapOptions(
              initialCenter: _center,
              initialZoom: _selectedHouseId != null || _selectedInfrastructureId != null ? 17 : (_myLocation != null ? 15 : 14),
              onTap: (_, __) => setState(() { _selectedHouseId = null; _selectedInfrastructureId = null; }),
              interactionOptions: const InteractionOptions(flags: InteractiveFlag.all & ~InteractiveFlag.rotate),
            ),
            children: [
              TileLayer(
                urlTemplate: _satellite
                    ? 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
                    : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.sangihekab.sipdt_mobile',
              ),
              if (_myLocation != null)
                MarkerLayer(markers: [
                  Marker(
                    point: _myLocation!,
                    width: 60,
                    height: 60,
                    alignment: Alignment.center,
                    child: _MyLocationMarker(),
                  ),
                ]),
              MarkerLayer(
                markers: renderedHouses.map((house) {
                  final isSelected = _selectedHouseId == house.id;
                  return Marker(
                    point: LatLng(house.latitude, house.longitude),
                    width: isSelected ? 60 : 44,
                    height: isSelected ? 64 : 50,
                    alignment: Alignment.bottomCenter,
                    child: GestureDetector(
                      onTap: () {
                        setState(() { _selectedHouseId = house.id; _selectedInfrastructureId = null; });
                        _mapController.move(LatLng(house.latitude, house.longitude), 17);
                      },
                      child: _HousePin(house: house, isSelected: isSelected),
                    ),
                  );
                }).toList(),
              ),
              MarkerLayer(
                markers: renderedInfrastructure.map((point) {
                  final isSelected = _selectedInfrastructureId == point.id;
                  return Marker(
                    point: LatLng(point.latitude, point.longitude),
                    width: isSelected ? 54 : 42,
                    height: isSelected ? 54 : 42,
                    alignment: Alignment.bottomCenter,
                    child: GestureDetector(
                      onTap: () {
                        setState(() { _selectedInfrastructureId = point.id; _selectedHouseId = null; });
                        _mapController.move(LatLng(point.latitude, point.longitude), 17);
                      },
                      child: _InfrastructurePin(point: point, isSelected: isSelected),
                    ),
                  );
                }).toList(),
              ),
        const RichAttributionWidget(attributions: [
          TextSourceAttribution('OpenStreetMap contributors'),
          TextSourceAttribution('Tiles © Esri'),
        ]),
            ],
          ),

          // Top Search Bar
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: _SearchBar(
                  houses: validHouses,
                  onSelect: (h) {
                    setState(() { _selectedHouseId = h.id; _selectedInfrastructureId = null; });
                    _mapController.move(LatLng(h.latitude, h.longitude), 17);
                  },
                  accent: colors.primary,
                ),
              ),
            ),
          ),

          Positioned(
            right: 12,
            top: 144,
            child: SafeArea(
              child: Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                elevation: 3,
                child: IconButton(
                  tooltip: _satellite ? 'Peta jalan' : 'Citra satelit',
                  onPressed: () => setState(() => _satellite = !_satellite),
                  icon: Icon(_satellite ? Icons.map_rounded : Icons.satellite_alt_rounded, color: colors.primary),
                ),
              ),
            ),
          ),

          // Empty state
          if (validHouses.isEmpty && validInfrastructure.isEmpty && _myLocation == null)
            Center(
              child: Container(
                margin: const EdgeInsets.all(32),
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.08), blurRadius: 24, offset: const Offset(0, 8)),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: colors.primary.withValues(alpha: 0.10),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      alignment: Alignment.center,
                      child: Icon(Icons.home_work_rounded, size: 32, color: colors.primary),
                    ),
                    const SizedBox(height: 16),
                    Text('Belum Ada Titik Lapangan', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 6),
                    Text(
                      'Tambahkan rumah atau infrastruktur untuk memunculkan pin lokasi pada peta.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),

          // Selected house card bottom sheet
          if (_selectedHouseId != null)
            Positioned(
              left: 12,
              right: 12,
              bottom: 12,
              child: _HouseBottomSheet(
                house: _houses.firstWhere((point) => point.id == _selectedHouseId),
                onClose: () => setState(() => _selectedHouseId = null),
              ),
            ),
          if (_selectedInfrastructureId != null)
            Positioned(
              left: 12,
              right: 12,
              bottom: 12,
              child: _InfrastructureBottomSheet(
                point: _infrastructure.firstWhere((point) => point.id == _selectedInfrastructureId),
                onClose: () => setState(() => _selectedInfrastructureId = null),
              ),
            ),

          // Legend top right
          if (allMarkerCount > 0)
            Positioned(
              right: 12,
              top: 80,
              child: SafeArea(
                child: _Legend(
                  truncated: truncated,
                  rendered: renderedHouses.length + renderedInfrastructure.length,
                  total: allMarkerCount,
                  hasMyLocation: _myLocation != null,
                ),
              ),
            ),
        ],
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _CircleFab(
              heroTag: 'my-location',
              icon: _locating ? null : Icons.my_location_rounded,
              loading: _locating,
              accent: const Color(0xff0b3c4d),
              tooltip: 'Lokasi Saya',
              onPressed: _locating ? null : _goToMyLocation,
            ),
            const SizedBox(height: 8),
            FloatingActionButton.small(
              heroTag: 'refresh-map-points',
              tooltip: 'Perbarui titik dari server',
              onPressed: _loadingPoints ? null : () async {
                await widget.onRefresh();
                await _refreshPoints();
              },
              child: const Icon(Icons.refresh_rounded),
            ),
            const SizedBox(height: 8),
            _CircleFab(
              heroTag: 'center',
              icon: Icons.center_focus_strong_rounded,
              accent: Colors.grey.shade700,
              tooltip: 'Reset Pilihan',
              onPressed: () => setState(() { _selectedHouseId = null; _selectedInfrastructureId = null; }),
            ),
            const SizedBox(height: 12),
            FloatingActionButton.extended(
              heroTag: 'add-infrastructure',
              onPressed: widget.onAddInfrastructure,
              icon: const Icon(Icons.construction_rounded),
              label: const Text('Infrastruktur'),
            ),
            const SizedBox(height: 8),
            FloatingActionButton.extended(
              heroTag: 'add',
              onPressed: widget.onAddHouse,
              icon: const Icon(Icons.add_location_alt_rounded),
              label: const Text('Tambah Rumah'),
            ),
          ],
        ),
      ),
    );
  }
}

Color _houseColor(RumahRecord house) {
  if (house.keluargaCount == 0) return const Color(0xffe11d48);
  if (house.keluargaCount <= 2) return const Color(0xffd4af37);
  return const Color(0xff0b3c4d);
}

Color _infrastructureColor(InfrastructureRecord point) => switch (point.jenis) {
      'jembatan' => const Color(0xff7c3aed),
      'drainase' => const Color(0xff0284c7),
      'lampu_jalan' => const Color(0xffd97706),
      'air_bersih' => const Color(0xff0891b2),
      _ => const Color(0xff15803d),
    };

String _infrastructureLabel(String type) => switch (type) {
      'jalan' => 'Jalan',
      'jembatan' => 'Jembatan',
      'drainase' => 'Drainase',
      'lampu_jalan' => 'Lampu Jalan',
      'air_bersih' => 'Sarana Air Bersih',
      _ => 'Infrastruktur Lain',
    };

class _InfrastructurePin extends StatelessWidget {
  const _InfrastructurePin({required this.point, required this.isSelected});
  final InfrastructureRecord point;
  final bool isSelected;

  @override
  Widget build(BuildContext context) {
    final color = _infrastructureColor(point);
    return Center(
      child: Container(
          width: isSelected ? 42 : 34,
          height: isSelected ? 42 : 34,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 3),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.25), blurRadius: 6, offset: const Offset(0, 2))],
          ),
          child: Icon(Icons.construction_rounded, size: isSelected ? 22 : 18, color: Colors.white),
      ),
    );
  }
}

class _InfrastructureBottomSheet extends StatelessWidget {
  const _InfrastructureBottomSheet({required this.point, required this.onClose});
  final InfrastructureRecord point;
  final VoidCallback onClose;

  @override
  Widget build(BuildContext context) {
    final color = _infrastructureColor(point);
    final status = point.isLocal ? 'Draft lokal' : (point.isVerified ? 'Terverifikasi' : 'Terkirim · menunggu verifikasi');
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      elevation: 8,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(backgroundColor: color.withValues(alpha: 0.12), child: Icon(Icons.construction_rounded, color: color)),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(point.nama, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                  Text('${_infrastructureLabel(point.jenis)} · $status', style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w600)),
                  if (point.alamat.isNotEmpty) Text(point.alamat, maxLines: 1, overflow: TextOverflow.ellipsis),
                  Text('${point.latitude.toStringAsFixed(5)}, ${point.longitude.toStringAsFixed(5)}', style: const TextStyle(fontSize: 11)),
                ],
              ),
            ),
            IconButton(onPressed: onClose, icon: const Icon(Icons.close_rounded)),
          ],
        ),
      ),
    );
  }
}

class _PinPainter extends CustomPainter {
  _PinPainter({required this.color, required this.selected});
  final Color color;
  final bool selected;

  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final cx = w / 2;
    final radius = w * 0.42;

    final path = ui.Path()
      ..moveTo(cx, h * 0.92)
      ..quadraticBezierTo(cx - radius, h * 0.55, cx - radius, h * 0.38)
      ..arcToPoint(Offset(cx + radius, h * 0.38), radius: Radius.circular(radius), clockwise: true)
      ..quadraticBezierTo(cx + radius, h * 0.55, cx, h * 0.92)
      ..close();

    canvas.drawShadow(path, Colors.black.withValues(alpha: selected ? 0.5 : 0.25), selected ? 8 : 4, true);
    canvas.drawPath(path, Paint()..color = color);

    final innerRadius = radius * 0.62;
    canvas.drawCircle(
      Offset(cx, h * 0.34),
      innerRadius,
      Paint()..color = Colors.white.withValues(alpha: 0.2),
    );
  }

  @override
  bool shouldRepaint(_PinPainter old) => old.color != color || old.selected != selected;
}

class _HousePin extends StatefulWidget {
  const _HousePin({required this.house, required this.isSelected});
  final RumahRecord house;
  final bool isSelected;

  @override
  State<_HousePin> createState() => _HousePinState();
}

class _HousePinState extends State<_HousePin> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _pulse;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1200))..repeat();
    _pulse = Tween<double>(begin: 1.0, end: 1.5).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final color = _houseColor(widget.house);
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (context, _) {
        return Stack(
          alignment: Alignment.bottomCenter,
          clipBehavior: Clip.none,
          children: [
            if (widget.isSelected)
              Positioned(
                bottom: 6,
                child: Container(
                  width: 22 * _pulse.value,
                  height: 22 * _pulse.value,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.4 * (2 - _pulse.value)),
                    shape: BoxShape.circle,
                  ),
                ),
              ),
            CustomPaint(
              size: Size(widget.isSelected ? 60 : 44, widget.isSelected ? 64 : 50),
              painter: _PinPainter(color: color, selected: widget.isSelected),
            ),
            Positioned(
              top: widget.isSelected ? 14 : 9,
              child: Icon(Icons.home_rounded, color: Colors.white, size: widget.isSelected ? 22 : 18),
            ),
          ],
        );
      },
    );
  }
}

class _MyLocationMarker extends StatefulWidget {
  @override
  State<_MyLocationMarker> createState() => _MyLocationMarkerState();
}

class _MyLocationMarkerState extends State<_MyLocationMarker> with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _pulse;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 1500))..repeat();
    _pulse = Tween<double>(begin: 1.0, end: 2.2).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (context, _) {
        return Stack(
          alignment: Alignment.center,
          children: [
            Container(
              width: 18 * _pulse.value,
              height: 18 * _pulse.value,
              decoration: BoxDecoration(
                color: const Color(0xff0b3c4d).withValues(alpha: 0.3 * (2 - _pulse.value)),
                shape: BoxShape.circle,
              ),
            ),
            Container(
              width: 14,
              height: 14,
              decoration: BoxDecoration(
                color: const Color(0xff0b3c4d),
                shape: BoxShape.circle,
                border: Border.all(color: const Color(0xffd4af37), width: 3),
                boxShadow: [BoxShadow(color: const Color(0xff0b3c4d).withValues(alpha: 0.5), blurRadius: 6)],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _SearchBar extends StatefulWidget {
  const _SearchBar({required this.houses, required this.onSelect, required this.accent});
  final List<RumahRecord> houses;
  final void Function(RumahRecord) onSelect;
  final Color accent;

  @override
  State<_SearchBar> createState() => _SearchBarState();
}

class _SearchBarState extends State<_SearchBar> {
  final _controller = TextEditingController();
  final _focus = FocusNode();
  List<RumahRecord> _results = const [];
  bool _expanded = false;

  @override
  void dispose() {
    _controller.dispose();
    _focus.dispose();
    super.dispose();
  }

  void _onChanged(String q) {
    if (q.trim().isEmpty) {
      setState(() => _results = const []);
      return;
    }
    final lower = q.toLowerCase();
    final matched = widget.houses
        .where((h) => h.kodeRumah.toLowerCase().contains(lower) || h.alamat.toLowerCase().contains(lower))
        .take(5)
        .toList();
    setState(() {
      _results = matched;
      _expanded = true;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 16, offset: const Offset(0, 4))],
            border: Border.all(color: widget.accent.withValues(alpha: 0.2)),
          ),
          child: TextField(
            controller: _controller,
            focusNode: _focus,
            onChanged: _onChanged,
            onTap: () => setState(() => _expanded = true),
            textInputAction: TextInputAction.search,
            decoration: InputDecoration(
              hintText: 'Cari rumah (kode atau alamat)',
              prefixIcon: Icon(Icons.search_rounded, size: 22, color: widget.accent),
              suffixIcon: _controller.text.isEmpty
                  ? null
                  : IconButton(
                      icon: const Icon(Icons.close_rounded, size: 18),
                      onPressed: () {
                        _controller.clear();
                        setState(() {
                          _results = const [];
                          _expanded = false;
                        });
                      },
                    ),
              border: InputBorder.none,
              enabledBorder: InputBorder.none,
              focusedBorder: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            ),
          ),
        ),
        if (_expanded && _results.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(top: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.10), blurRadius: 16)],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: _results.map((h) {
                return ListTile(
                  dense: true,
                  leading: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: widget.accent.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(Icons.home_rounded, color: widget.accent, size: 18),
                  ),
                  title: Text(h.kodeRumah, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                  subtitle: Text(h.alamat, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12)),
                  onTap: () {
                    widget.onSelect(h);
                    _controller.clear();
                    _focus.unfocus();
                    setState(() {
                      _results = const [];
                      _expanded = false;
                    });
                  },
                );
              }).toList(),
            ),
          ),
      ],
    );
  }
}

class _HouseBottomSheet extends StatelessWidget {
  const _HouseBottomSheet({required this.house, required this.onClose});
  final RumahRecord house;
  final VoidCallback onClose;

  @override
  Widget build(BuildContext context) {
    final color = _houseColor(house);
    final status = house.isLocal ? 'Draft lokal' : (house.isVerified ? 'Terverifikasi' : 'Terkirim · menunggu verifikasi');
    return Material(
      color: Colors.transparent,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24), bottom: Radius.circular(24)),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.20), blurRadius: 24, offset: const Offset(0, -4))],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              margin: const EdgeInsets.only(top: 10),
              width: 44,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 14, 16),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    alignment: Alignment.center,
                    child: Icon(Icons.home_rounded, color: color, size: 26),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(house.kodeRumah, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, letterSpacing: -0.3)),
                        const SizedBox(height: 2),
                        Text(
                          house.alamat,
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: onClose,
                    icon: const Icon(Icons.close_rounded, size: 22),
                    color: Colors.grey.shade500,
                  ),
                ],
              ),
            ),
            Divider(height: 1, color: Colors.grey.shade200),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
              child: Column(
                children: [
                  Row(
                    children: [
                      Expanded(child: _InfoTile(icon: Icons.family_restroom_rounded, label: 'Keluarga', value: house.keluargaCount == 1 ? '1 Kartu Keluarga' : '${house.keluargaCount} Kartu Keluarga')),
                      const SizedBox(width: 12),
                      Expanded(child: _InfoTile(icon: Icons.location_on_rounded, label: 'Koordinat GPS', value: '${house.latitude.toStringAsFixed(4)}, ${house.longitude.toStringAsFixed(4)}')),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.10),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: color.withValues(alpha: 0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.home_work_rounded, size: 16, color: color),
                        const SizedBox(width: 8),
                        Text(
                          'Status: $status · Kepemilikan: ${house.status}',
                          style: TextStyle(color: color, fontSize: 13, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  const _InfoTile({required this.icon, required this.label, required this.value});
  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xfff4f7f6),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 14, color: const Color(0xff0b3c4d)),
              const SizedBox(width: 4),
              Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600, letterSpacing: 0.3)),
            ],
          ),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

class _Legend extends StatelessWidget {
  const _Legend({required this.truncated, required this.rendered, required this.total, required this.hasMyLocation});
  final bool truncated;
  final int rendered;
  final int total;
  final bool hasMyLocation;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.95),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          _legendRow(const Color(0xff0b3c4d), 'Rumah', isLast: false),
          _legendRow(const Color(0xff15803d), 'Infrastruktur', isLast: !hasMyLocation),
          if (hasMyLocation) _legendRow(const Color(0xffd4af37), 'Posisi Saya', isLast: true),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: truncated ? const Color(0xfffff8e7) : const Color(0xffebf1f0),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              truncated ? '$rendered/$total titik' : '$total titik',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: truncated ? const Color(0xff514322) : const Color(0xff0b3c4d),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _legendRow(Color color, String label, {required bool isLast}) {
    return Padding(
      padding: EdgeInsets.only(bottom: isLast ? 0 : 6),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
          const SizedBox(width: 8),
          Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _CircleFab extends StatelessWidget {
  const _CircleFab({
    required this.heroTag,
    required this.icon,
    required this.accent,
    required this.tooltip,
    required this.onPressed,
    this.loading = false,
  });
  final String heroTag;
  final IconData? icon;
  final Color accent;
  final String tooltip;
  final VoidCallback? onPressed;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: FloatingActionButton.small(
        heroTag: heroTag,
        backgroundColor: Colors.white,
        elevation: 4,
        onPressed: onPressed,
        child: loading
            ? SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: accent))
            : Icon(icon, color: accent, size: 20),
      ),
    );
  }
}
