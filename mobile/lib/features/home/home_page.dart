import 'package:flutter/material.dart';

import '../../core/api_client.dart';
import '../../core/config.dart';
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

class _HomePageState extends State<HomePage> with SingleTickerProviderStateMixin {
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
    if (mounted) {
      setState(() {
        _drafts = widget.store.drafts();
        _bootstrap = widget.store.bootstrap();
        _loading = false;
      });
      if (_index == 1) {
        // Rebuild the map so a newly saved draft appears immediately.
        setState(() => _index = 0);
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (mounted) setState(() => _index = 1);
        });
      }
    }
  }

  Future<void> _loadBootstrap() async {
    final cached = widget.store.bootstrap();
    final hasVillageScope = cached?.datasets.map((item) => item.key).toSet().containsAll({'rumah', 'infrastruktur'}) ?? false;
    if (cached != null && hasVillageScope) {
      if (mounted) setState(() => _bootstrap = cached);
      return;
    }
    try {
      final bootstrap = await widget.api.bootstrap();
      await widget.store.saveBootstrap(bootstrap);
      if (mounted) setState(() => _bootstrap = bootstrap);
    } catch (_) {
      // Capture remains available when cached references exist.
    }
  }

  Future<void> _refreshBootstrap() async {
    try {
      final bootstrap = await widget.api.bootstrap();
      await widget.store.saveBootstrap(bootstrap);
      if (!mounted) return;
      setState(() => _bootstrap = bootstrap);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Data desa dan referensi berhasil diperbarui.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal memperbarui data: $error'),
            backgroundColor: const Color(0xffa4463b),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _newDraft(String type) async {
    final userDesaId = widget.session.user?.desaId;
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => DraftPage(
          store: widget.store,
          api: widget.api,
          userDesaId: userDesaId,
          bootstrap: _bootstrap,
          initialDataset: type,
          onSaved: _reload,
        ),
      ),
    );
    await _reload();
  }

  @override
  Widget build(BuildContext context) {
    final pages = <Widget>[
      _dashboard(context),
      MapPage(bootstrap: _bootstrap, store: widget.store, api: widget.api, onRefresh: _refreshBootstrap, onAddHouse: () => _newDraft('rumah'), onAddInfrastructure: () => _newDraft('infrastruktur')),
      SyncPage(
        store: widget.store,
        api: widget.api,
        userDesaId: widget.session.user?.desaId,
        bootstrap: _bootstrap,
        onChanged: _reload,
      ),
      _profile(context),
    ];

    final colors = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: colors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(
                _index == 0
                    ? Icons.grid_view_rounded
                    : _index == 1
                        ? Icons.map_rounded
                        : _index == 2
                            ? Icons.cloud_upload_rounded
                            : Icons.person_rounded,
                size: 20,
                color: colors.primary,
              ),
            ),
            const SizedBox(width: 12),
            Text(
              ['Beranda Desa', 'Peta Spatial Rumah', 'Pengiriman Draft', 'Profil Petugas'][_index],
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Perbarui Data Desa',
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _refreshBootstrap,
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : AnimatedSwitcher(
              duration: const Duration(milliseconds: 300),
              switchInCurve: Curves.easeOut,
              switchOutCurve: Curves.easeIn,
              child: KeyedSubtree(
                key: ValueKey<int>(_index),
                child: pages[_index],
              ),
            ),
      floatingActionButton: _index == 0
          ? FloatingActionButton.extended(
              onPressed: () => _newDraft('rumah'),
              icon: const Icon(Icons.add_location_alt_rounded),
              label: const Text('Tambah Rumah'),
            )
          : null,
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: NavigationBar(
          selectedIndex: _index,
          onDestinationSelected: (value) => setState(() => _index = value),
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.grid_view_outlined),
              selectedIcon: Icon(Icons.grid_view_rounded),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.map_outlined),
              selectedIcon: Icon(Icons.map_rounded),
              label: 'Peta',
            ),
            NavigationDestination(
              icon: Icon(Icons.cloud_upload_outlined),
              selectedIcon: Icon(Icons.cloud_upload_rounded),
              label: 'Kirim Draft',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline_rounded),
              selectedIcon: Icon(Icons.person_rounded),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }

  Widget _dashboard(BuildContext context) {
    final user = widget.session.user!;
    final greeting = _getGreeting();
    final firstName = user.name.split(' ').first;
    final pending = _drafts.where((item) => (item.type == DraftType.rumah || item.type == DraftType.infrastruktur) && item.syncState != 'accepted').length;

    return RefreshIndicator(
      onRefresh: _refreshBootstrap,
      color: Theme.of(context).colorScheme.primary,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
        children: [
          if (widget.session.offlineMode) _offlineBanner(context),
          if (widget.session.offlineMode) const SizedBox(height: 16),
          _greetingHero(context, greeting, firstName, user),
          const SizedBox(height: 24),
          _statsGrid(context),
          if (pending > 0) ...[
            const SizedBox(height: 20),
            _pendingCard(context, pending),
          ],
          const SizedBox(height: 28),
          _sectionHeader(context, 'Input Pendataan Desa', 'Pilih kategori data untuk mulai mencatat'),
          const SizedBox(height: 14),
          _quickActions(context),
          const SizedBox(height: 28),
          _scopeCard(context),
        ],
      ),
    );
  }

  String _getGreeting() {
    final hour = DateTime.now().hour;
    if (hour < 11) return 'Selamat pagi';
    if (hour < 15) return 'Selamat siang';
    if (hour < 18) return 'Selamat sore';
    return 'Selamat malam';
  }

  Widget _greetingHero(BuildContext context, String greeting, String firstName, MobileUser user) {
    final isOffline = widget.session.offlineMode;

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isOffline
              ? [const Color(0xff8a672c), const Color(0xff573e12)]
              : [const Color(0xff0b3c4d), const Color(0xff05212b)],
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: (isOffline ? const Color(0xff8a672c) : const Color(0xff0b3c4d)).withValues(alpha: 0.35),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Background decorative crest watermark
          const Positioned(
            right: -20,
            bottom: -20,
            child: Icon(
              Icons.landscape_rounded,
              size: 140,
              color: Colors.white12,
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Container(
                    width: 58,
                    height: 58,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xffd4af37), Color(0xffe2c46c)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(18),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xffd4af37).withValues(alpha: 0.4),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      firstName.isEmpty ? 'A' : firstName[0].toUpperCase(),
                      style: const TextStyle(color: Color(0xff0b3c4d), fontSize: 26, fontWeight: FontWeight.w800),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              '$greeting,',
                              style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 13, fontWeight: FontWeight.w500),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xffd4af37).withValues(alpha: 0.25),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xffd4af37).withValues(alpha: 0.5)),
                              ),
                              child: const Text(
                                'Admin Desa',
                                style: TextStyle(color: Color(0xffe2c46c), fontSize: 10, fontWeight: FontWeight.w700),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          firstName,
                          style: const TextStyle(color: Colors.white, fontSize: 21, fontWeight: FontWeight.w800, letterSpacing: -0.3),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.location_on_rounded, size: 16, color: Color(0xffd4af37)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                          user.desa ?? user.kecamatan ?? 'Wilayah Tugas Belum Ditentukan',
                        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const Icon(Icons.verified_rounded, size: 16, color: Color(0xffe2c46c)),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statsGrid(BuildContext context) {
    final stats = [
      _Stat('Rumah', _bootstrap?.rumah.length ?? 0, Icons.home_rounded, const Color(0xff0b3c4d)),
      _Stat('Keluarga Terhubung', _bootstrap?.rumah.fold<int>(0, (sum, rumah) => sum + rumah.keluargaCount) ?? 0, Icons.family_restroom_rounded, const Color(0xffd4af37)),
      _Stat('Draft Infrastruktur', widget.store.drafts().where((draft) => draft.type == DraftType.infrastruktur && draft.syncState != 'accepted').length, Icons.account_balance_rounded, const Color(0xff1f5f77)),
    ];
    return Row(
      children: [
        for (var i = 0; i < stats.length; i++) ...[
          Expanded(child: _statCard(context, stats[i])),
          if (i < stats.length - 1) const SizedBox(width: 12),
        ],
      ],
    );
  }

  Widget _statCard(BuildContext context, _Stat s) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
        border: Border.all(color: s.color.withValues(alpha: 0.2), width: 1.2),
      ),
      child: Column(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: s.color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            alignment: Alignment.center,
            child: Icon(s.icon, color: s.color, size: 24),
          ),
          const SizedBox(height: 12),
          Text(
            '${s.value}',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: s.color, letterSpacing: -0.5),
          ),
          const SizedBox(height: 2),
          Text(
            s.label,
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.grey.shade600, letterSpacing: 0.2),
          ),
        ],
      ),
    );
  }

  Widget _pendingCard(BuildContext context, int pending) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => setState(() => _index = 2),
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xfffff8e7), Color(0xfffef3d6)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xffe2c46c)),
            boxShadow: [
              BoxShadow(
                color: const Color(0xffd4af37).withValues(alpha: 0.15),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xff0b3c4d),
                  borderRadius: BorderRadius.circular(16),
                ),
                alignment: Alignment.center,
                child: const Icon(Icons.cloud_upload_rounded, color: Color(0xffd4af37), size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '$pending draft tersimpan lokal',
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xff3b2e04)),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'Ketuk untuk mengirim ke server saat online',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: Color(0xff614c07)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
                child: const Icon(Icons.arrow_forward_rounded, color: Color(0xff0b3c4d), size: 18),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _sectionHeader(BuildContext context, String title, String subtitle) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w800)),
        const SizedBox(height: 2),
        Text(subtitle, style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
      ],
    );
  }

  Widget _quickActions(BuildContext context) {
    final actions = [
      _ActionItem('Rumah Baru', 'Catat rumah & titik koordinat GPS', Icons.add_location_alt_rounded, const Color(0xff0b3c4d), 'rumah'),
      _ActionItem('Infrastruktur', 'Catat fasilitas dan koordinat', Icons.account_balance_rounded, const Color(0xff1f5f77), 'infrastruktur'),
    ];
    return Column(
      children: [
        for (var i = 0; i < actions.length; i++) ...[
          _actionCard(context, actions[i]),
          if (i < actions.length - 1) const SizedBox(height: 12),
        ],
      ],
    );
  }

  Widget _actionCard(BuildContext context, _ActionItem a) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => _newDraft(a.type),
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: a.color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(16),
                ),
                alignment: Alignment.center,
                child: Icon(a.icon, color: a.color, size: 26),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(a.title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 3),
                    Text(a.subtitle, style: TextStyle(fontSize: 12, color: Colors.grey.shade600, height: 1.3)),
                  ],
                ),
              ),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xfff4f7f6),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.add_rounded, color: Color(0xff0b3c4d), size: 20),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _scopeCard(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xffebf1f0),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xffd5e0df)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: const Color(0xff0b3c4d).withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(12),
            ),
            alignment: Alignment.center,
            child: const Icon(Icons.account_tree_rounded, color: Color(0xff0b3c4d), size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Alur Data Desa SIPDT', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xff0b3c4d))),
                const SizedBox(height: 4),
                Text(
                  'Aplikasi ini mencatat titik Rumah dan Infrastruktur. Data keluarga dipilih dari sumber OPD; data KK dan Penduduk dikelola OPD melalui Dashboard Web.',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade800, height: 1.5),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _offlineBanner(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          widget.session.offlineMode = false;
          widget.session.retryAutoLogin();
        },
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xfffff4e5),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xffffe0b2)),
          ),
          child: Row(
            children: [
              const Icon(Icons.wifi_off_rounded, color: Color(0xffe65100), size: 22),
              const SizedBox(width: 12),
              const Expanded(
                child: Text(
                  'Mode Offline / Demo Aktif. Pembacaan data lokal saja.',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xffbf360c)),
                ),
              ),
              const Text('Coba Online', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xff0b3c4d))),
              const SizedBox(width: 2),
              const Icon(Icons.arrow_forward_rounded, size: 14, color: Color(0xff0b3c4d)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _profile(BuildContext context) {
    final user = widget.session.user!;
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
      children: [
        Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xff0b3c4d), Color(0xff05212b)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: const Color(0xff0b3c4d).withValues(alpha: 0.3),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xffd4af37), Color(0xffe2c46c)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xffd4af37).withValues(alpha: 0.4),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    )
                  ],
                ),
                alignment: Alignment.center,
                child: Text(
                  user.name.isEmpty ? 'A' : user.name[0].toUpperCase(),
                  style: const TextStyle(color: Color(0xff0b3c4d), fontSize: 36, fontWeight: FontWeight.w800),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                user.name,
                style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              Text(
                user.email,
                style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xffd4af37).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xffd4af37).withValues(alpha: 0.5)),
                ),
                child: Text(
                  user.roleLabel ?? 'Admin Desa Official',
                  style: const TextStyle(color: Color(0xffe2c46c), fontSize: 12, fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Column(
            children: [
              _profileRow(Icons.badge_outlined, 'Wewenang Petugas', user.roleLabel ?? 'Admin Desa'),
              Divider(height: 1, color: Colors.grey.shade200, indent: 56),
              _profileRow(Icons.location_city_rounded, 'Wilayah Desa', user.desa ?? '-'),
              Divider(height: 1, color: Colors.grey.shade200, indent: 56),
              _profileRow(Icons.cloud_sync_rounded, 'Versi Referensi Bootstrap', _bootstrap?.version ?? 'Belum Diambil'),
            ],
          ),
        ),
        const SizedBox(height: 20),
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xffebf1f0),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xffd5e0df)),
          ),
          child: Row(
            children: [
              const Icon(Icons.shield_outlined, size: 20, color: Color(0xff0b3c4d)),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'SIPDT Mobile Sangihe v${AppConfig.appVersion}\nKabupaten Kepulauan Sangihe',
                  style: TextStyle(fontSize: 12, color: Colors.grey.shade800, fontWeight: FontWeight.w600, height: 1.4),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _profileRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      child: Row(
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: const Color(0xff0b3c4d).withValues(alpha: 0.10),
              borderRadius: BorderRadius.circular(12),
            ),
            alignment: Alignment.center,
            child: Icon(icon, size: 20, color: const Color(0xff0b3c4d)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600, fontWeight: FontWeight.w600, letterSpacing: 0.3)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Stat {
  const _Stat(this.label, this.value, this.icon, this.color);
  final String label;
  final int value;
  final IconData icon;
  final Color color;
}

class _ActionItem {
  const _ActionItem(this.title, this.subtitle, this.icon, this.color, this.type);
  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final String type;
}
