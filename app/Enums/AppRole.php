<?php

namespace App\Enums;

enum AppRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case ADMIN_KOMINFO = 'admin_kominfo';
    case ADMIN_OPD = 'admin_opd';
    case ADMIN_KECAMATAN = 'admin_kecamatan';
    case ADMIN_DESA = 'admin_desa';
    case PUBLIC = 'public';

    case ADMIN_KESEHATAN = 'admin_kesehatan';
    case OPERATOR_KESEHATAN = 'operator_kesehatan';
    case ADMIN_PENDIDIKAN = 'admin_pendidikan';
    case OPERATOR_PENDIDIKAN = 'operator_pendidikan';
    case ADMIN_PU = 'admin_pu';
    case OPERATOR_PU = 'operator_pu';
    case ADMIN_SOSIAL = 'admin_sosial';
    case OPERATOR_SOSIAL = 'operator_sosial';
    case ADMIN_EKONOMI = 'admin_ekonomi';
    case OPERATOR_EKONOMI = 'operator_ekonomi';
    case ADMIN_KEPENDUDUKAN = 'admin_kependudukan';
    case OPERATOR_KEPENDUDUKAN = 'operator_kependudukan';
    case ADMIN_LINGKUNGAN = 'admin_lingkungan';
    case OPERATOR_LINGKUNGAN = 'operator_lingkungan';

    case OPERATOR_PUSKESMAS = 'operator_puskesmas';
    case ADMIN_PUSKESMAS = 'admin_puskesmas';
    case OPERATOR_SEKOLAH = 'operator_sekolah';
    case ADMIN_SEKOLAH = 'admin_sekolah';

    case ADMIN_DINKES = 'admin_dinkes';
    case OPERATOR_DINKES = 'operator_dinkes';
    case ADMIN_DISPEND = 'admin_dispend';
    case OPERATOR_DISPEND = 'operator_dispend';
    case ADMIN_DPU = 'admin_dpu';
    case OPERATOR_DPU = 'operator_dpu';
    case ADMIN_DINSOS = 'admin_dinsos';
    case OPERATOR_DINSOS = 'operator_dinsos';
    case ADMIN_DISPERINDAG = 'admin_disperindag';
    case OPERATOR_DISPERINDAG = 'operator_disperindag';
    case ADMIN_DUKCAPIL = 'admin_dukcapil';
    case OPERATOR_DUKCAPIL = 'operator_dukcapil';
    case ADMIN_DLH = 'admin_dlh';
    case OPERATOR_DLH = 'operator_dlh';
    case ADMIN_DISTAN = 'admin_distan';
    case OPERATOR_DISTAN = 'operator_distan';
    case ADMIN_DISHUB = 'admin_dishub';
    case OPERATOR_DISHUB = 'operator_dishub';
    case ADMIN_DISTPAR = 'admin_distpar';
    case OPERATOR_DISTPAR = 'operator_distpar';
    case ADMIN_DISPORA = 'admin_dispora';
    case OPERATOR_DISPORA = 'operator_dispora';
    case ADMIN_BKPSDMD = 'admin_bkpsdmd';
    case OPERATOR_BKPSDMD = 'operator_bkpsdmd';
    case ADMIN_BAPPEDA = 'admin_bappeda';
    case OPERATOR_BAPPEDA = 'operator_bappeda';
    case ADMIN_BAPENDA = 'admin_bapenda';
    case OPERATOR_BAPENDA = 'operator_bapenda';
    case ADMIN_BPBD = 'admin_bpbd';
    case OPERATOR_BPBD = 'operator_bpbd';
    case ADMIN_DISKOMINFO = 'admin_diskominfo';
    case OPERATOR_DISKOMINFO = 'operator_diskominfo';
    case ADMIN_DPMTSP = 'admin_dpmptsp';
    case OPERATOR_DPMTSP = 'operator_dpmptsp';
    case ADMIN_DISTAMAN = 'admin_distaman';
    case OPERATOR_DISTAMAN = 'operator_distaman';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Administrator',
            self::ADMIN_KOMINFO => 'Admin Kominfo',
            self::ADMIN_OPD => 'Admin OPD',
            self::ADMIN_KECAMATAN => 'Admin Kecamatan',
            self::ADMIN_DESA => 'Admin Desa',
            self::PUBLIC => 'Publik',
            self::ADMIN_KESEHATAN => 'Admin Kesehatan',
            self::OPERATOR_KESEHATAN => 'Operator Kesehatan',
            self::ADMIN_PENDIDIKAN => 'Admin Pendidikan',
            self::OPERATOR_PENDIDIKAN => 'Operator Pendidikan',
            self::ADMIN_PU => 'Admin PU-PR',
            self::OPERATOR_PU => 'Operator PU-PR',
            self::ADMIN_SOSIAL => 'Admin Sosial',
            self::OPERATOR_SOSIAL => 'Operator Sosial',
            self::ADMIN_EKONOMI => 'Admin Ekonomi',
            self::OPERATOR_EKONOMI => 'Operator Ekonomi',
            self::ADMIN_KEPENDUDUKAN => 'Admin Kependudukan',
            self::OPERATOR_KEPENDUDUKAN => 'Operator Kependudukan',
            self::ADMIN_LINGKUNGAN => 'Admin Lingkungan',
            self::OPERATOR_LINGKUNGAN => 'Operator Lingkungan',
            self::ADMIN_PUSKESMAS => 'Admin Puskesmas',
            self::OPERATOR_PUSKESMAS => 'Operator Puskesmas',
            self::ADMIN_SEKOLAH => 'Admin Sekolah',
            self::OPERATOR_SEKOLAH => 'Operator Sekolah',
            self::ADMIN_DINKES => 'Admin Dinkes',
            self::OPERATOR_DINKES => 'Operator Dinkes',
            self::ADMIN_DISPEND => 'Admin Dispend',
            self::OPERATOR_DISPEND => 'Operator Dispend',
            self::ADMIN_DPU => 'Admin DPU',
            self::OPERATOR_DPU => 'Operator DPU',
            self::ADMIN_DINSOS => 'Admin Dinsos',
            self::OPERATOR_DINSOS => 'Operator Dinsos',
            self::ADMIN_DISPERINDAG => 'Admin Disperindag',
            self::OPERATOR_DISPERINDAG => 'Operator Disperindag',
            self::ADMIN_DUKCAPIL => 'Admin Dukcapil',
            self::OPERATOR_DUKCAPIL => 'Operator Dukcapil',
            self::ADMIN_DLH => 'Admin DLH',
            self::OPERATOR_DLH => 'Operator DLH',
            self::ADMIN_DISTAN => 'Admin Distan',
            self::OPERATOR_DISTAN => 'Operator Distan',
            self::ADMIN_DISHUB => 'Admin Dishub',
            self::OPERATOR_DISHUB => 'Operator Dishub',
            self::ADMIN_DISTPAR => 'Admin Distpar',
            self::OPERATOR_DISTPAR => 'Operator Distpar',
            self::ADMIN_DISPORA => 'Admin Dispora',
            self::OPERATOR_DISPORA => 'Operator Dispora',
            self::ADMIN_BKPSDMD => 'Admin BKPSDMD',
            self::OPERATOR_BKPSDMD => 'Operator BKPSDMD',
            self::ADMIN_BAPPEDA => 'Admin Bappeda',
            self::OPERATOR_BAPPEDA => 'Operator Bappeda',
            self::ADMIN_BAPENDA => 'Admin Bapenda',
            self::OPERATOR_BAPENDA => 'Operator Bapenda',
            self::ADMIN_BPBD => 'Admin BPBD',
            self::OPERATOR_BPBD => 'Operator BPBD',
            self::ADMIN_DISKOMINFO => 'Admin Diskominfo',
            self::OPERATOR_DISKOMINFO => 'Operator Diskominfo',
            self::ADMIN_DPMTSP => 'Admin DPMTSP',
            self::OPERATOR_DPMTSP => 'Operator DPMTSP',
            self::ADMIN_DISTAMAN => 'Admin Distaman',
            self::OPERATOR_DISTAMAN => 'Operator Distaman',
            default => ucwords(str_replace('_', ' ', $this->value)),
        };
    }

    public function canAccessFilament(): bool
    {
        return $this !== self::PUBLIC;
    }

    public function isAvailableForRegistration(): bool
    {
        return ! in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ADMIN_KOMINFO,
            self::ADMIN_OPD,
            self::PUBLIC,
        ], true);
    }

    public function opdModule(): ?string
    {
        return match ($this) {
            self::ADMIN_KESEHATAN,
            self::OPERATOR_KESEHATAN,
            self::ADMIN_DINKES,
            self::OPERATOR_DINKES,
            self::ADMIN_PUSKESMAS,
            self::OPERATOR_PUSKESMAS => 'kesehatan',
            self::ADMIN_PENDIDIKAN,
            self::OPERATOR_PENDIDIKAN,
            self::ADMIN_SEKOLAH,
            self::OPERATOR_SEKOLAH => 'pendidikan',
            self::ADMIN_PU,
            self::OPERATOR_PU,
            self::ADMIN_DPU,
            self::OPERATOR_DPU => 'pu',
            self::ADMIN_SOSIAL,
            self::OPERATOR_SOSIAL,
            self::ADMIN_DINSOS,
            self::OPERATOR_DINSOS => 'sosial',
            self::ADMIN_EKONOMI,
            self::OPERATOR_EKONOMI,
            self::ADMIN_DISPEND,
            self::OPERATOR_DISPEND,
            self::ADMIN_DISPERINDAG,
            self::OPERATOR_DISPERINDAG => 'ekonomi',
            self::ADMIN_KEPENDUDUKAN,
            self::OPERATOR_KEPENDUDUKAN,
            self::ADMIN_DUKCAPIL,
            self::OPERATOR_DUKCAPIL => 'kependudukan',
            self::ADMIN_LINGKUNGAN,
            self::OPERATOR_LINGKUNGAN,
            self::ADMIN_DLH,
            self::OPERATOR_DLH => 'lingkungan',
            default => null,
        };
    }

    public function isKabupatenLevel(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::ADMIN_KOMINFO,
        ], true);
    }

    public function isKecamatanLevel(): bool
    {
        return $this === self::ADMIN_KECAMATAN;
    }

    public function isDesaLevel(): bool
    {
        return $this === self::ADMIN_DESA;
    }

    public function isOpdLevel(): bool
    {
        return $this->opdModule() !== null || $this === self::ADMIN_OPD;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function availableForRegistration(): array
    {
        return collect(self::cases())
            ->filter(fn (self $r) => $r->isAvailableForRegistration())
            ->mapWithKeys(fn (self $r) => [$r->value => $r->label()])
            ->all();
    }

    public static function filamentAccessValues(): array
    {
        return collect(self::cases())
            ->filter(fn (self $r) => $r->canAccessFilament())
            ->map(fn (self $r) => $r->value)
            ->all();
    }
}
