<?php

namespace App\Enums;

enum AppRole: string
{
    // ========================
    // CORE ADMIN ROLES
    // ========================
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';

    // ========================
    // KABUPATEN LEVEL
    // ========================
    case ADMIN_KOMINFO = 'admin_kominfo';
    case WALIDATA = 'walidata';
    case ADMIN_BUPATI = 'admin_bupati';
    case ADMIN_WAKIL_BUPATI = 'admin_wakil_bupati';

    // ========================
    // OPD LEVEL (DINAS/LEMBAGA)
    // ========================
    case ADMIN_OPD = 'admin_opd';
    case KEPALA_DINAS = 'kepala_dinas';
    case OPERATOR_OPD = 'operator_opd';

    // Admin Berita & Halaman Publik
    case ADMIN_BERITA = 'admin_berita';
    case ADMIN_PORTAL = 'admin_portal';

    // ========================
    // KECAMATAN LEVEL
    // ========================
    case ADMIN_KECAMATAN = 'admin_kecamatan';
    case ADMIN_LAPANGAN = 'admin_lapangan';
    case ADMIN_DESA = 'admin_desa';

    // ========================
    // OPD-SPECIFIC ROLES
    // ========================

    // Kesehatan (Dinkes)
    case ADMIN_DINKES = 'admin_dinkes';
    case OPERATOR_DINKES = 'operator_dinkes';

    // Pendidikan (Dispendik)
    case ADMIN_DISPEND = 'admin_dispend';
    case OPERATOR_DISPEND = 'operator_dispend';

    // PU-PR (DPUPR)
    case ADMIN_DPU = 'admin_dpu';
    case OPERATOR_DPU = 'operator_dpu';

    // Sosial (Dinsos)
    case ADMIN_DINSOS = 'admin_dinsos';
    case OPERATOR_DINSOS = 'operator_dinsos';

    // Perdagangan & Perindustrian (Disperindag)
    case ADMIN_DISPERINDAG = 'admin_disperindag';
    case OPERATOR_DISPERINDAG = 'operator_disperindag';

    // Kependudukan & Pencatatan Sipil (Dukcapil)
    case ADMIN_DUKCAPIL = 'admin_dukcapil';
    case OPERATOR_DUKCAPIL = 'operator_dukcapil';

    // Lingkungan Hidup (DLH)
    case ADMIN_DLH = 'admin_dlh';
    case OPERATOR_DLH = 'operator_dlh';

    // Pertanian (Distan)
    case ADMIN_DISTAN = 'admin_distan';
    case OPERATOR_DISTAN = 'operator_distan';

    //Hubungi (Dishub)
    case ADMIN_DISHUB = 'admin_dishub';
    case OPERATOR_DISHUB = 'operator_dishub';

    // Pariwisata & Budaya (Distparbud)
    case ADMIN_DISTPAR = 'admin_distpar';
    case OPERATOR_DISTPAR = 'operator_distpar';

    // Pemuda & Olahraga (Dispora)
    case ADMIN_DISPORA = 'admin_dispora';
    case OPERATOR_DISPORA = 'operator_dispora';

    // BKPSDMD - Kepegawaian
    case ADMIN_BKPSDMD = 'admin_bkpsdmd';
    case OPERATOR_BKPSDMD = 'operator_bkpsdmd';

    // Bappeda - Perencanaan Pembangunan
    case ADMIN_BAPPEDA = 'admin_bappeda';
    case OPERATOR_BAPPEDA = 'operator_bappeda';

    // Bapenda - Keuangan & Pendapatan Daerah
    case ADMIN_BAPENDA = 'admin_bapenda';
    case OPERATOR_BAPENDA = 'operator_bapenda';

    // BPBD - Bencana
    case ADMIN_BPBD = 'admin_bpbd';
    case OPERATOR_BPBD = 'operator_bpbd';

    // Diskominfo - Komunikasi & Informatika
    case ADMIN_DISKOMINFO = 'admin_diskominfo';
    case OPERATOR_DISKOMINFO = 'operator_diskominfo';

    // DPMTSP - Penanaman Modal & PTSP
    case ADMIN_DPMTSP = 'admin_dpmptsp';
    case OPERATOR_DPMTSP = 'operator_dpmptsp';

    // Distanak - Perkebunan & Hortikultura
    case ADMIN_DISTAMAN = 'admin_distaman';
    case OPERATOR_DISTAMAN = 'operator_distaman';

    // ========================
    // FASE 1: OPD BARU
    // 14 OPD tambahan sesuai 32 OPD Kabupaten Kepulauan Sangihe
    // ========================

    // Setda - Sekretariat Daerah
    case ADMIN_SETDA = 'admin_setda';
    case OPERATOR_SETDA = 'operator_setda';

    // Setwan - Sekretariat Dewan
    case ADMIN_SETWAN = 'admin_setwan';
    case OPERATOR_SETWAN = 'operator_setwan';

    // DPPKB - Pengendalian Penduduk & KB
    case ADMIN_DPPKB = 'admin_dppkb';
    case OPERATOR_DPPKB = 'operator_dppkb';

    // DP3A - Pemberdayaan Perempuan & Perlindungan Anak
    case ADMIN_DP3A = 'admin_dp3a';
    case OPERATOR_DP3A = 'operator_dp3a';

    // DPMD - Pemberdayaan Masyarakat & Desa
    case ADMIN_DPMD = 'admin_dpmd';
    case OPERATOR_DPMD = 'operator_dpmd';

    // Disnaker - Ketenagakerjaan
    case ADMIN_DISNAKER = 'admin_disnaker';
    case OPERATOR_DISNAKER = 'operator_disnaker';

    // Ketapang - Ketahanan Pangan
    case ADMIN_KETAPANG = 'admin_ketapang';
    case OPERATOR_KETAPANG = 'operator_ketapang';

    // Dinas Kelautan &Perikanan
    case ADMIN_PERIKANAN = 'admin_perikanan';
    case OPERATOR_PERIKANAN = 'operator_perikanan';

    // Perpus - Perpustakaan
    case ADMIN_PERPUS = 'admin_perpus';
    case OPERATOR_PERPUS = 'operator_perpus';

    // Perkim - Perumahan & Permukiman
    case ADMIN_PERKIM = 'admin_perkim';
    case OPERATOR_PERKIM = 'operator_perkim';

    // Dinas Perdagangan & Koprasi
    case ADMIN_KOPERASI = 'admin_koperasi';
    case OPERATOR_KOPERASI = 'operator_koperasi';

    // Inspektorat
    case ADMIN_INSPEKTORAT = 'admin_inspektorat';
    case OPERATOR_INSPEKTORAT = 'operator_inspektorat';

    // Kesbangpol - Kesatuan Bangsa & Politik
    case ADMIN_KESBANGPOL = 'admin_kesbangpol';
    case OPERATOR_KESBANGPOL = 'operator_kesbangpol';

    // Batas - Perbatasan
    case ADMIN_BATAS = 'admin_batas';
    case OPERATOR_BATAS = 'operator_batas';

    // Satpol PP - Satpol Pamong Praja
    case ADMIN_SATPOLPP = 'admin_satpolpp';
    case OPERATOR_SATPOLPP = 'operator_satpolpp';

    // Public / Guest
    case PUBLIC = 'public';

    // ========================
    // LABEL METHODS
    // ========================

    public function label(): string
    {
        return match ($this) {
            // Core Admin
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Administrator',

            // Kabupaten Level
            self::ADMIN_KOMINFO => 'Admin Kominfo',
            self::WALIDATA => 'Walidata',
            self::ADMIN_BUPATI => 'Admin Bupati',
            self::ADMIN_WAKIL_BUPATI => 'Admin Wakil Bupati',
            self::ADMIN_PORTAL => 'Admin Portal Publik',

            // OPD Level
            self::ADMIN_OPD => 'Admin OPD',
            self::KEPALA_DINAS => 'Kepala Dinas',
            self::OPERATOR_OPD => 'Operator OPD',
            self::ADMIN_BERITA => 'Admin Berita & Halaman',

            // Kecamatan Level
            self::ADMIN_KECAMATAN => 'Admin Kecamatan',
            self::ADMIN_LAPANGAN => 'Admin Lapangan',
            self::ADMIN_DESA => 'Admin Desa',

            // OPD Spesific - Kesehatan
            self::ADMIN_DINKES => 'Admin Dinkes',
            self::OPERATOR_DINKES => 'Operator Dinkes',

            // OPD Spesific - Pendidikan
            self::ADMIN_DISPEND => 'Admin Dispendik',
            self::OPERATOR_DISPEND => 'Operator Dispendik',

            // OPD Spesific - PU
            self::ADMIN_DPU => 'Admin DPUPR',
            self::OPERATOR_DPU => 'Operator DPUPR',

            // OPD Spesific - Sosial
            self::ADMIN_DINSOS => 'Admin Dinsos',
            self::OPERATOR_DINSOS => 'Operator Dinsos',

            // OPD Spesific - Perdagangan
            self::ADMIN_DISPERINDAG => 'Admin Disperindag',
            self::OPERATOR_DISPERINDAG => 'Operator Disperindag',

            // OPD Spesific - Kependudukan
            self::ADMIN_DUKCAPIL => 'Admin Dukcapil',
            self::OPERATOR_DUKCAPIL => 'Operator Dukcapil',

            // OPD Spesific - Lingkungan
            self::ADMIN_DLH => 'Admin DLH',
            self::OPERATOR_DLH => 'Operator DLH',

            // OPD Spesific - Pertanian
            self::ADMIN_DISTAN => 'Admin Distan',
            self::OPERATOR_DISTAN => 'Operator Distan',

            // OPD Spesific -Hubungi
            self::ADMIN_DISHUB => 'Admin Dishub',
            self::OPERATOR_DISHUB => 'Operator Dishub',

            // OPD Spesific - Pariwisata
            self::ADMIN_DISTPAR => 'Admin Distparbud',
            self::OPERATOR_DISTPAR => 'Operator Distparbud',

            // OPD Spesific - Pemuda
            self::ADMIN_DISPORA => 'Admin Dispora',
            self::OPERATOR_DISPORA => 'Operator Dispora',

            // OPD Spesific - Kepegawaian
            self::ADMIN_BKPSDMD => 'Admin BKPSDMD',
            self::OPERATOR_BKPSDMD => 'Operator BKPSDMD',

            // OPD Spesific - Perencanaan
            self::ADMIN_BAPPEDA => 'Admin Bappeda',
            self::OPERATOR_BAPPEDA => 'Operator Bappeda',

            // OPD Spesific - Keuangan
            self::ADMIN_BAPENDA => 'Admin Bapenda',
            self::OPERATOR_BAPENDA => 'Operator Bapenda',

            // OPD Spesific - Bencana
            self::ADMIN_BPBD => 'Admin BPBD',
            self::OPERATOR_BPBD => 'Operator BPBD',

            // OPD Spesific - Kominfo
            self::ADMIN_DISKOMINFO => 'Admin Diskominfo',
            self::OPERATOR_DISKOMINFO => 'Operator Diskominfo',

            // OPD Spesific - Investasi
            self::ADMIN_DPMTSP => 'Admin DPMTSP',
            self::OPERATOR_DPMTSP => 'Operator DPMTSP',

            // OPD Spesific - Perkebunan
            self::ADMIN_DISTAMAN => 'Admin Distanak',
            self::OPERATOR_DISTAMAN => 'Operator Distanak',

            // OPD Baru - Fase 1
            self::ADMIN_SETDA => 'Admin Setda',
            self::OPERATOR_SETDA => 'Operator Setda',
            self::ADMIN_SETWAN => 'Admin Setwan',
            self::OPERATOR_SETWAN => 'Operator Setwan',
            self::ADMIN_DPPKB => 'Admin DPPKB',
            self::OPERATOR_DPPKB => 'Operator DPPKB',
            self::ADMIN_DP3A => 'Admin DP3A',
            self::OPERATOR_DP3A => 'Operator DP3A',
            self::ADMIN_DPMD => 'Admin DPMD',
            self::OPERATOR_DPMD => 'Operator DPMD',
            self::ADMIN_DISNAKER => 'Admin Disnaker',
            self::OPERATOR_DISNAKER => 'Operator Disnaker',
            self::ADMIN_KETAPANG => 'Admin Ketapang',
            self::OPERATOR_KETAPANG => 'Operator Ketapang',
            self::ADMIN_PERIKANAN => 'AdminPerikanan',
            self::OPERATOR_PERIKANAN => 'OperatorPerikanan',
            self::ADMIN_PERPUS => 'Admin Perpus',
            self::OPERATOR_PERPUS => 'Operator Perpus',
            self::ADMIN_PERKIM => 'Admin Perkim',
            self::OPERATOR_PERKIM => 'Operator Perkim',
            self::ADMIN_KOPERASI => 'Admin Koprasi',
            self::OPERATOR_KOPERASI => 'Operator Koprasi',
            self::ADMIN_INSPEKTORAT => 'Admin Inspektorat',
            self::OPERATOR_INSPEKTORAT => 'Operator Inspektorat',
            self::ADMIN_KESBANGPOL => 'Admin Kesbangpol',
            self::OPERATOR_KESBANGPOL => 'Operator Kesbangpol',
            self::ADMIN_BATAS => 'Admin Batas',
            self::OPERATOR_BATAS => 'Operator Batas',
            self::ADMIN_SATPOLPP => 'Admin Satpol PP',
            self::OPERATOR_SATPOLPP => 'Operator Satpol PP',

            // Public
            self::PUBLIC => 'Publik',

            default => ucwords(str_replace('_', ' ', $this->value)),
        };
    }

    // ========================
    // ACCESS CONTROL METHODS
    // ========================

    public function canAccessFilament(): bool
    {
        return $this !== self::PUBLIC;
    }

    /**
     * Role yang tidak bisa diregistrasi oleh user sendiri
     */
    public function isAvailableForRegistration(): bool
    {
        return ! in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ADMIN_KOMINFO,
            self::WALIDATA,
            self::ADMIN_BUPATI,
            self::ADMIN_WAKIL_BUPATI,
            self::KEPALA_DINAS,
            self::ADMIN_OPD,
            self::ADMIN_LAPANGAN,
            self::ADMIN_PORTAL,
            self::PUBLIC,
        ], true);
    }

    /**
     * Get OPD module identifier for this role
     */
    public function opdModule(): ?string
    {
        return match ($this) {
            // Kesehatan
            self::ADMIN_DINKES,
            self::OPERATOR_DINKES => 'kesehatan',

            // Pendidikan
            self::ADMIN_DISPEND,
            self::OPERATOR_DISPEND => 'pendidikan',

            // PU
            self::ADMIN_DPU,
            self::OPERATOR_DPU => 'pu',

            // Sosial
            self::ADMIN_DINSOS,
            self::OPERATOR_DINSOS => 'sosial',

            // Perdagangan
            self::ADMIN_DISPERINDAG,
            self::OPERATOR_DISPERINDAG => 'ekonomi',

            // Kependudukan
            self::ADMIN_DUKCAPIL,
            self::OPERATOR_DUKCAPIL => 'kependudukan',

            // Lingkungan
            self::ADMIN_DLH,
            self::OPERATOR_DLH => 'lingkungan',

            // Pertanian
            self::ADMIN_DISTAN,
            self::OPERATOR_DISTAN => 'pertanian',

            //Hubungi
            self::ADMIN_DISHUB,
            self::OPERATOR_DISHUB => 'hubungan',

            // Pariwisata
            self::ADMIN_DISTPAR,
            self::OPERATOR_DISTPAR => 'pariwisata',

            // Pemuda
            self::ADMIN_DISPORA,
            self::OPERATOR_DISPORA => 'pemuda',

            // Kepegawaian
            self::ADMIN_BKPSDMD,
            self::OPERATOR_BKPSDMD => 'kepegawaian',

            // Perencanaan
            self::ADMIN_BAPPEDA,
            self::OPERATOR_BAPPEDA => 'perencanaan',

            // Keuangan
            self::ADMIN_BAPENDA,
            self::OPERATOR_BAPENDA => 'keuangan',

            // Bencana
            self::ADMIN_BPBD,
            self::OPERATOR_BPBD => 'bencana',

            // Kominfo
            self::ADMIN_DISKOMINFO,
            self::OPERATOR_DISKOMINFO => 'kominfo',

            // Investasi
            self::ADMIN_DPMTSP,
            self::OPERATOR_DPMTSP => 'investasi',

            // Perkebunan
            self::ADMIN_DISTAMAN,
            self::OPERATOR_DISTAMAN => 'perkebunan',

            // OPD Baru - Fase 1
            self::ADMIN_DPPKB,
            self::OPERATOR_DPPKB => 'pengendalian_penduduk',
            self::ADMIN_DP3A,
            self::OPERATOR_DP3A => 'pemberdayaan_perempuan',
            self::ADMIN_DPMD,
            self::OPERATOR_DPMD => 'pemberdayaan_masyarakat',
            self::ADMIN_DISNAKER,
            self::OPERATOR_DISNAKER => 'ketenagakerjaan',
            self::ADMIN_KETAPANG,
            self::OPERATOR_KETAPANG => 'ketahanan_pangan',
            self::ADMIN_PERIKANAN,
            self::OPERATOR_PERIKANAN => 'perikanan',
            self::ADMIN_PERPUS,
            self::OPERATOR_PERPUS => 'perpustakaan',
            self::ADMIN_PERKIM,
            self::OPERATOR_PERKIM => 'perkim',
            self::ADMIN_KOPERASI,
            self::OPERATOR_KOPERASI => 'koperasi',
            self::ADMIN_INSPEKTORAT,
            self::OPERATOR_INSPEKTORAT => 'pengawasan',
            self::ADMIN_KESBANGPOL,
            self::OPERATOR_KESBANGPOL => 'kesbangpol',
            self::ADMIN_BATAS,
            self::OPERATOR_BATAS => 'perbatasan',
            self::ADMIN_SATPOLPP,
            self::OPERATOR_SATPOLPP => 'satpolpp',
            self::ADMIN_SETDA,
            self::OPERATOR_SETDA => 'setda',
            self::ADMIN_SETWAN,
            self::OPERATOR_SETWAN => 'setwan',

            // Generic OPD
            self::ADMIN_OPD,
            self::OPERATOR_OPD => 'umum',

            default => null,
        };
    }

    /**
     * Check if this role is kabupaten level
     */
    public function isKabupatenLevel(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ADMIN_KOMINFO,
            self::WALIDATA,
            self::ADMIN_BUPATI,
            self::ADMIN_WAKIL_BUPATI,
        ], true);
    }

    /**
     * Check if this role is kecamatan level
     */
    public function isKecamatanLevel(): bool
    {
        return $this === self::ADMIN_KECAMATAN;
    }

    /**
     * Check if this role is desa level
     */
    public function isDesaLevel(): bool
    {
        return $this === self::ADMIN_DESA;
    }

    /**
     * Check if this role is lapangan level
     */
    public function isLapanganLevel(): bool
    {
        return $this === self::ADMIN_LAPANGAN;
    }

    /**
     * Check if this role is OPD level
     */
    public function isOpdLevel(): bool
    {
        return $this->opdModule() !== null || in_array($this, [
            self::ADMIN_OPD,
            self::OPERATOR_OPD,
            self::KEPALA_DINAS,
        ]);
    }

    /**
     * Check if this role is berita/content admin
     */
    public function isBeritaAdmin(): bool
    {
        return $this === self::ADMIN_BERITA;
    }

    public function isPortalAdmin(): bool
    {
        return $this === self::ADMIN_PORTAL;
    }

    /**
     * Check if this role is kepala daerah (bupati/wakil)
     */
    public function isKepalaDaerah(): bool
    {
        return in_array($this, [
            self::ADMIN_BUPATI,
            self::ADMIN_WAKIL_BUPATI,
        ]);
    }

    /**
     * Check if this role can approve data
     */
    public function canApprove(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ADMIN_KOMINFO,
            self::WALIDATA,
            self::KEPALA_DINAS,
        ]) || $this->isKabupatenLevel();
    }

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get roles available for user registration
     */
    public static function availableForRegistration(): array
    {
        return collect(self::cases())
            ->filter(fn (self $r) => $r->isAvailableForRegistration())
            ->mapWithKeys(fn (self $r) => [$r->value => $r->label()])
            ->all();
    }

    /**
     * Get roles that can access Filament
     */
    public static function filamentAccessValues(): array
    {
        return collect(self::cases())
            ->filter(fn (self $r) => $r->canAccessFilament())
            ->map(fn (self $r) => $r->value)
            ->all();
    }

    /**
     * Get all OPD-specific admin roles
     */
    public static function opdAdminRoles(): array
    {
        return array_filter(self::cases(), fn (self $r) => str_starts_with($r->value, 'admin_') && $r->opdModule() !== null);
    }

    /**
     * Get all OPD-specific operator roles
     */
    public static function opdOperatorRoles(): array
    {
        return array_filter(self::cases(), fn (self $r) => str_starts_with($r->value, 'operator_') && $r->opdModule() !== null);
    }
}
