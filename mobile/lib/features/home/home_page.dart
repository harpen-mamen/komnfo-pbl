import 'package:flutter/material.dart';

import '../../core/api_client.dart';
import '../../core/local_store.dart';
import '../../core/models.dart';
import '../../core/session_controller.dart';
import '../capture/draft_page.dart';
import '../map/map_page.dart';
import '../sync/sync_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key, required this.session, required this.api, required this.store});

  final SessionController session;
  final ApiClient api;
  final LocalStore store;

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _index = 0;
  List<DraftRecord> _drafts = const [];
  MobileBootstrap? _bootstrap;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _reload();
    _loadBootstrap();
  }

  Future<void> _reload() async {
    if (mounted) setState(() { _drafts = widget.store.drafts(); _bootstrap = widget.store.bootstrap(); _loading = false; });
  }

  Future<void> _loadBootstrap() async {
    final cached = widget.store.bootstrap();
    final hasVillageScope = cached?.datasets.map((item) => item.key).toSet().containsAll({'rumah', 'keluarga', 'penduduk'}) ?? false;
    if (cached != null && hasVillageScope) {
      if (mounted) setState(() => _bootstrap = cached);
      return;
    }
    try {
      final bootstrap = await widget.api.bootstrap();
      await widget.store.saveBootstrap(bootstrap);
      if (mounted) setState(() => _bootstrap = bootstrap);
    } catch (_) {
      // Capture remains available when cached references already exist.
    }
  }

  Future<void> _refreshBootstrap() async {
    try {
      final bootstrap = await widget.api.bootstrap();
      await widget.store.saveBootstrap(bootstrap);
      if (!mounted) return;
      setState(() => _bootstrap = bootstrap);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Data desa dan referensi diperbarui.')));
    } catch (error) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal memperbarui data: $error')));
    }
  }

  Future<void> _newDraft(String type) async {
    final userDesaId = widget.session.user?.desaId;
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => DraftPage(store: widget.store, userDesaId: userDesaId, bootstrap: _bootstrap, initialDataset: type, onSaved: _reload)));
    await _reload();
  }

  @override
  Widget build(BuildContext context) {
    final pages = <Widget>[
      _dashboard(context),
      MapPage(userDesaId: widget.session.user?.desaId, bootstrap: _bootstrap, onAddHouse: () => _newDraft('rumah')),
      SyncPage(store: widget.store, api: widget.api, userDesaId: widget.session.user?.desaId, bootstrap: _bootstrap, onChanged: _reload),
      _profile(context),
    ];
    return Scaffold(
      appBar: AppBar(title: Text(['Dashboard', 'Peta Rumah', 'Pengiriman Draft', 'Profil'][_index]), actions: [PopupMenuButton<String>(onSelected: (value) async { if (value == 'refresh') await _refreshBootstrap(); if (value == 'logout') await widget.session.logout(); }, itemBuilder: (_) => const [PopupMenuItem(value: 'refresh', child: Text('Perbarui data desa')), PopupMenuItem(value: 'logout', child: Text('Keluar'))])]),
      body: _loading ? const Center(child: CircularProgressIndicator()) : pages[_index],
      floatingActionButton: _index == 0 ? FloatingActionButton.extended(onPressed: () => _newDraft('rumah'), icon: const Icon(Icons.add_home), label: const Text('Tambah Rumah')) : null,
      bottomNavigationBar: NavigationBar(selectedIndex: _index, onDestinationSelected: (value) => setState(() => _index = value), destinations: const [NavigationDestination(icon: Icon(Icons.dashboard_outlined), selectedIcon: Icon(Icons.dashboard), label: 'Beranda'), NavigationDestination(icon: Icon(Icons.map_outlined), selectedIcon: Icon(Icons.map), label: 'Peta'), NavigationDestination(icon: Icon(Icons.cloud_upload_outlined), selectedIcon: Icon(Icons.cloud_upload), label: 'Kirim'), NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil')]),
    );
  }

  Widget _dashboard(BuildContext context) {
    final user = widget.session.user!;
    final pending = _drafts.where((item) => item.syncState != 'accepted').length;
    return RefreshIndicator(onRefresh: _refreshBootstrap, child: ListView(padding: const EdgeInsets.all(16), children: [
      Card(child: ListTile(leading: CircleAvatar(child: Text(user.name.isEmpty ? 'A' : user.name[0].toUpperCase())), title: Text(user.name), subtitle: Text('${user.roleLabel ?? 'Admin Desa'}\n${user.desa ?? 'Desa belum ditentukan'}'))),
      const SizedBox(height: 12),
      Row(children: [Expanded(child: _stat('Rumah', '${_bootstrap?.rumah.length ?? 0}', Icons.home)), const SizedBox(width: 8), Expanded(child: _stat('KK', '${_bootstrap?.keluarga.length ?? 0}', Icons.family_restroom)), const SizedBox(width: 8), Expanded(child: _stat('Penduduk', '${_bootstrap?.penduduk.length ?? 0}', Icons.people))]),
      const SizedBox(height: 12),
      Card(child: ListTile(leading: const Icon(Icons.cloud_upload, color: Colors.orange), title: Text('$pending draft belum dikirim'), subtitle: const Text('Data tetap aman di perangkat sampai koneksi tersedia.'), trailing: TextButton(onPressed: () => setState(() => _index = 2), child: const Text('Lihat')))),
      const SizedBox(height: 20),
      Text('Input data desa', style: Theme.of(context).textTheme.titleLarge),
      const SizedBox(height: 8),
      _action('Rumah', 'Buat rumah dan titik GPS', Icons.add_home, Colors.teal, 'rumah'),
      _action('Kartu Keluarga', 'Hubungkan KK ke rumah', Icons.family_restroom, Colors.orange, 'keluarga'),
      _action('Penduduk', 'Hubungkan penduduk ke KK', Icons.person_add, Colors.blue, 'penduduk'),
      const SizedBox(height: 20),
      Card(color: Colors.blue.shade50, child: const ListTile(leading: Icon(Icons.info_outline), title: Text('Scope Admin Desa'), subtitle: Text('Mobile ini hanya untuk Rumah, KK, Penduduk, dan koordinat GPS. Kesehatan serta Pendidikan dikelola melalui dashboard web OPD.'))),
    ]));
  }

  Widget _stat(String label, String value, IconData icon) => Card(child: Padding(padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8), child: Column(children: [Icon(icon, color: const Color(0xff0f766e)), const SizedBox(height: 4), Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)), Text(label, style: const TextStyle(fontSize: 11))])));

  Widget _action(String title, String subtitle, IconData icon, Color color, String type) => Card(child: ListTile(leading: CircleAvatar(backgroundColor: color.withValues(alpha: 0.12), child: Icon(icon, color: color)), title: Text(title), subtitle: Text(subtitle), trailing: const Icon(Icons.chevron_right), onTap: () => _newDraft(type)));

  Widget _profile(BuildContext context) {
    final user = widget.session.user!;
    return ListView(padding: const EdgeInsets.all(16), children: [Card(child: ListTile(leading: const CircleAvatar(child: Icon(Icons.person)), title: Text(user.name), subtitle: Text(user.email))), const SizedBox(height: 12), Card(child: Column(children: [ListTile(leading: const Icon(Icons.badge_outlined), title: const Text('Wewenang'), subtitle: Text(user.roleLabel ?? 'Admin Desa')), ListTile(leading: const Icon(Icons.location_city), title: const Text('Wilayah kerja'), subtitle: Text(user.desa ?? '-')), ListTile(leading: const Icon(Icons.info_outline), title: const Text('Versi bootstrap'), subtitle: Text(_bootstrap?.version ?? 'Belum diambil'))]))]);
  }
}
