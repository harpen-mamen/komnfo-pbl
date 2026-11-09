<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    // FASE 1: 14 kategori tambahan (Prinsip 14)
    public const KATEGORI_PEMERINTAHAN = 'pemerintahan';
    public const KATEGORI_KETENAGAKERJAAN = 'ketenagakerjaan';
    public const KATEGORI_KETAHANAN_PANGAN = 'ketahanan_pangan';
    public const KATEGORI_PERIKANAN = 'perikanan';
    public const KATEGORI_PERPUSTAKAAN = 'perpustakaan';
    public const KATEGORI_PERUMAHAN = 'perumahan';
    public const KATEGORI_KOPERASI = 'koperasi';
    public const KATEGORI_PENGAWASAN = 'pengawasan';
    public const KATEGORI_KESBANGPOL = 'kesbangpol';
    public const KATEGORI_PERBATASAN = 'perbatasan';
    public const KATEGORI_SATPOLPP = 'satpolpp';
    public const KATEGORI_SETDA = 'setda';
    public const KATEGORI_SETWAN = 'setwan';

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
            // FASE 1: 14 kategori tambahan (Prinsip 14)
            self::KATEGORI_PEMERINTAHAN => 'Bidang Pemerintahan',
            self::KATEGORI_KETENAGAKERJAAN => 'Bidang Ketenagakerjaan',
            self::KATEGORI_KETAHANAN_PANGAN => 'Bidang Ketahanan Pangan',
            self::KATEGORI_PERIKANAN => 'Bidang Perikanan',
            self::KATEGORI_PERPUSTAKAAN => 'Bidang Perpustakaan',
            self::KATEGORI_PERUMAHAN => 'Bidang Perumahan & Permukiman',
            self::KATEGORI_KOPERASI => 'Bidang Koperasi & UKM',
            self::KATEGORI_PENGAWASAN => 'Bidang Pengawasan',
            self::KATEGORI_KESBANGPOL => 'Bidang Kesbangpol',
            self::KATEGORI_PERBATASAN => 'Bidang Pengelolaan Perbatasan',
            self::KATEGORI_SATPOLPP => 'Bidang Satpol PP & Damkar',
            self::KATEGORI_SETDA => 'Sekretariat Daerah',
            self::KATEGORI_SETWAN => 'Sekretariat DPRD',
            self::KATEGORI_LAINNYA => 'Lainnya',
        ];
    }

    public static function kategoriLabels(string $kategori): string
    {
        return self::kategoriOptions()[$kategori] ?? $kategori;
    }

    // Relasi
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class);
    }

    public function programBantuans(): HasMany
    {
        return $this->hasMany(ProgramBantuan::class);
    }

    /**
     * FASE 1: Source attribution polymorphic
     * (untuk data yang OPD ini menjadi source-nya)
     */
    public function sourceAttributions(): MorphMany
    {
        return $this->morphMany(SourceAttribution::class, 'sourceable');
    }

    // Scope untuk OPD aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
