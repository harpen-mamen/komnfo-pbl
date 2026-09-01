<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $table = 'opds';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'aktif',
        'kategori',
        'parent_id',
        'alamat',
        'telepon',
        'email',
        'kepala_dinas',
        'nip_kepala_dinas',
        'singkatan',
        'is_verifikator',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'is_verifikator' => 'boolean',
    ];

    // Kategori OPD untuk Kabupaten Kepulauan Sangihe
    public const KATEGORI_KESEHATAN = 'kesehatan';
    public const KATEGORI_PENDIDIKAN = 'pendidikan';
    public const KATEGORI_PU = 'pu';
    public const KATEGORI_SOSIAL = 'sosial';
    public const KATEGORI_PERDAGANGAN = 'perdagangan';
    public const KATEGORI_KEPENDUDUKAN = 'kependudukan';
    public const KATEGORI_LINGKUNGAN = 'lingkungan';
    public const KATEGORI_PERTANIAN = 'pertanian';
    public const KATEGORI_HUBUNGAN = 'hubungan';
    public const KATEGORI_PARIWISATA = 'pariwisata';
    public const KATEGORI_PEMUDA = 'pemuda';
    public const KATEGORI_KEPEGAWAIAN = 'kepegawaian';
    public const KATEGORI_PERENCANAAN = 'perencanaan';
    public const KATEGORI_KEUANGAN = 'keuangan';
    public const KATEGORI_BENCANA = 'bencana';
    public const KATEGORI_KOMINFO = 'kominfo';
    public const KATEGORI_INVESTASI = 'investasi';
    public const KATEGORI_PERKEBUNAN = 'perkebunan';
    public const KATEGORI_LAINNYA = 'lainnya';

    public static function kategoriOptions(): array
    {
        return [
            self::KATEGORI_KESEHATAN => 'Dinas Kesehatan',
            self::KATEGORI_PENDIDIKAN => 'Dinas Pendidikan',
            self::KATEGORI_PU => 'Dinas PU-PR',
            self::KATEGORI_SOSIAL => 'Dinas Sosial',
            self::KATEGORI_PERDAGANGAN => 'Dinas Perindustrian & Perdagangan',
            self::KATEGORI_KEPENDUDUKAN => 'Dinas Kependudukan & Pencatatan Sipil',
            self::KATEGORI_LINGKUNGAN => 'Dinas Lingkungan Hidup',
            self::KATEGORI_PERTANIAN => 'Dinas Pertanian & Peternakan',
            self::KATEGORI_HUBUNGAN => 'Dinas Perhubungan',
            self::KATEGORI_PARIWISATA => 'Dinas Pariwisata',
            self::KATEGORI_PEMUDA => 'Dinas Pemuda & Olahraga',
            self::KATEGORI_KEPEGAWAIAN => 'Badan Kepegawaian & Pengembangan SDM',
            self::KATEGORI_PERENCANAAN => 'Badan Perencanaan Pembangunan',
            self::KATEGORI_KEUANGAN => 'Badan Pendapatan Daerah',
            self::KATEGORI_BENCANA => 'Badan Penanggulangan Bencana Daerah',
            self::KATEGORI_KOMINFO => 'Dinas Komunikasi & Informatika',
            self::KATEGORI_INVESTASI => 'Dinas Penanaman Modal & PTSP',
            self::KATEGORI_PERKEBUNAN => 'Dinas Perkebunan & Pertambangan',
            self::KATEGORI_LAINNYA => 'Lainnya',
        ];
    }

    public static function kategoriLabels(string $kategori): string
    {
        return self::kategoriOptions()[$kategori] ?? $kategori;
    }

    // Mapping kategori ke role admin
    public static function getAdminRoleForKategori(string $kategori): string
    {
        return match ($kategori) {
            self::KATEGORI_KESEHATAN => 'admin_dinkes',
            self::KATEGORI_PENDIDIKAN => 'admin_dispend',
            self::KATEGORI_PU => 'admin_dpu',
            self::KATEGORI_SOSIAL => 'admin_dinsos',
            self::KATEGORI_PERDAGANGAN => 'admin_disperindag',
            self::KATEGORI_KEPENDUDUKAN => 'admin_dukcapil',
            self::KATEGORI_LINGKUNGAN => 'admin_dlh',
            self::KATEGORI_PERTANIAN => 'admin_distan',
            self::KATEGORI_HUBUNGAN => 'admin_dishub',
            self::KATEGORI_PARIWISATA => 'admin_distpar',
            self::KATEGORI_PEMUDA => 'admin_dispora',
            self::KATEGORI_KEPEGAWAIAN => 'admin_bkpsdmd',
            self::KATEGORI_PERENCANAAN => 'admin_bappeda',
            self::KATEGORI_KEUANGAN => 'admin_bapenda',
            self::KATEGORI_BENCANA => 'admin_bpbd',
            self::KATEGORI_KOMINFO => 'admin_diskominfo',
            self::KATEGORI_INVESTASI => 'admin_dpmptsp',
            self::KATEGORI_PERKEBUNAN => 'admin_distaman',
            default => 'admin_opd',
        };
    }

    public static function getOperatorRoleForKategori(string $kategori): string
    {
        $adminRole = self::getAdminRoleForKategori($kategori);
        return str_replace('admin_', 'operator_', $adminRole);
    }

    // Relasi
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Opd::class, 'parent_id');
    }

    // Scope untuk OPD aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // Scope berdasarkan kategori
    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    // Check apakah OPD ini adalah verifikator untuk data tertentu
    public function isVerifikator(): bool
    {
        return $this->is_verifikator ?? false;
    }

    // Check apakah OPD ini bisa memverifikasi data dari kategori tertentu
    public function canVerifyData(string $dataCategory): bool
    {
        // Mapping kategori data dengan OPD verifikator
        $verificationMap = [
            'kesehatan' => [self::KATEGORI_KESEHATAN],
            'pendidikan' => [self::KATEGORI_PENDIDIKAN],
            'pu' => [self::KATEGORI_PU],
            'sosial' => [self::KATEGORI_SOSIAL],
            'perdagangan' => [self::KATEGORI_PERDAGANGAN],
            'kependudukan' => [self::KATEGORI_KEPENDUDUKAN],
            'lingkungan' => [self::KATEGORI_LINGKUNGAN],
            'pertanian' => [self::KATEGORI_PERTANIAN],
            'hubungan' => [self::KATEGORI_HUBUNGAN],
            'bencana' => [self::KATEGORI_BENCANA],
        ];

        return in_array($this->kategori, $verificationMap[$dataCategory] ?? []);
    }

    // Get module name untuk batch submission
    public function getModuleName(): string
    {
        return $this->kategori ?? self::KATEGORI_LAINNYA;
    }

    // Static untuk mendapatkan OPD berdasarkan kategori
    public static function findByKategori(string $kategori): ?Opd
    {
        return self::where('kategori', $kategori)->aktif()->first();
    }

    // Get all verifikator OPDs
    public static function getVerifikators(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_verifikator', true)->aktif()->get();
    }

    // Get OPD verifikator untuk kategori tertentu
    public static function getVerifikatorForCategory(string $kategori): ?Opd
    {
        return self::where('kategori', $kategori)
            ->where('is_verifikator', true)
            ->aktif()
            ->first();
    }
}
