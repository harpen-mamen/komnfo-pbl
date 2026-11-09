<?php

namespace App\Services\DataInput;

use App\Models\Dataset;
use App\Models\DatasetValue;

/**
 * FieldAutoCalculator - Hitung otomatis field-field derived.
 *
 * Beberapa field di Buku Sektoral 2024 adalah HASIL PERHITUNGAN:
 * - persen_pengukuran = entry_pengukuran / sasaran_balita x 100
 * - persen_stunting = jumlah_stunting / entry_pengukuran x 100
 * - nilai_produksi_rp = volume_kg x harga_rata_rp_kg
 * - persen_realisasi = realisasi_anggaran / pagu_anggaran x 100
 * - total_penduduk = laki_laki + perempuan (untuk tabel kependudukan)
 *
 * Calculator ini mengupdate field-field derived SEBELUM disimpan.
 * Field dengan suffix _pct_ atau _total_ biasanya auto-calc.
 */
class FieldAutoCalculator
{
    /**
     * Apply auto-calculation pada data values sebelum disimpan.
     * Mengupdate values[] dengan field-field yang dihitung.
     */
    public static function apply(Dataset $dataset, array $values): array
    {
        $kode = $dataset->kode;

        $requirements = match ($kode) {
            '3.20'=>['sasaran_balita','entry_pengukuran','jumlah_stunting'],
            '4.47'=>['volume_kg','harga_rata_rp_kg'],
            '7.75','7.73'=>['pagu_anggaran','realisasi_anggaran'],
            '3.12'=>['kk_laki','kk_perempuan'], '3.13'=>['penduduk_laki','penduduk_perempuan'],
            '3.15'=>['lk_0_18','pr_0_18'], '3.17'=>['jumlah_guru','jumlah_murid'],
            '4.42'=>['luas_panen_ha','produksi_ton'], '9.92'=>['wisman','wisnu'], default=>[],
        };
        foreach ($requirements as $key) if (!isset($values[$key]) || $values[$key]==='') return $values;
        $calculated = match ($kode) {
            '3.20' => self::calcStunting($values),
            '4.47' => self::calcProduksiIkan($values),
            '7.75' => self::calcRealisasi($values),
            '3.12' => self::calcKK($values),
            '3.13' => self::calcPenduduk($values),
            '3.15' => self::calcAkta($values),
            '3.17' => self::calcGuruMurid($values),
            '4.42' => self::calcProduktivitas($values),
            '7.73' => self::calcRealisasi($values),
            '9.92' => self::calcWisatawan($values),
            default => $values,
        };
        // Reported figures take precedence; calculate only missing outputs.
        return array_replace($calculated,array_filter($values,fn($value)=>$value!==null && $value!==''));
    }

    /**
     * e-PPGBM Stunting - hitung % pengukuran dan % stunting
     */
    protected static function calcStunting(array $v): array
    {
        // Hitung % pengukuran
        if (isset($v['sasaran_balita'], $v['entry_pengukuran']) && $v['sasaran_balita'] > 0) {
            $v['persen_pengukuran'] = round(($v['entry_pengukuran'] / $v['sasaran_balita']) * 100, 2);
        }
        // Hitung % stunting
        if (isset($v['entry_pengukuran'], $v['jumlah_stunting']) && $v['entry_pengukuran'] > 0) {
            $v['persen_stunting'] = round(($v['jumlah_stunting'] / $v['entry_pengukuran']) * 100, 2);
        }
        return $v;
    }

    /**
     * Produksi Ikan - hitung nilai produksi dari volume x harga
     */
    protected static function calcProduksiIkan(array $v): array
    {
        if (isset($v['volume_kg'], $v['harga_rata_rp_kg'])) {
            $v['nilai_produksi_rp'] = round($v['volume_kg'] * $v['harga_rata_rp_kg'], 2);
        }
        return $v;
    }

    /**
     * Realisasi Anggaran - hitung % realisasi
     */
    protected static function calcRealisasi(array $v): array
    {
        if (isset($v['pagu_anggaran'], $v['realisasi_anggaran']) && $v['pagu_anggaran'] > 0) {
            $v['persen_realisasi'] = round(($v['realisasi_anggaran'] / $v['pagu_anggaran']) * 100, 2);
        }
        return $v;
    }

    /**
     * Demografi (KK, Penduduk) - hitung total laki+perempuan
     */
    /**
     * 3.12 KK per Kecamatan - hitung total KK
     */
    protected static function calcKK(array $v): array
    {
        $lk = (int) ($v["kk_laki"] ?? 0);
        $pr = (int) ($v["kk_perempuan"] ?? 0);
        if (isset($v['kk_laki'], $v['kk_perempuan'])) {
            $v["total"] = $lk + $pr;
        }
        return $v;
    }

    /**
     * 3.13 Penduduk per Kecamatan - hitung total
     */
    protected static function calcPenduduk(array $v): array
    {
        $lk = (int) ($v["penduduk_laki"] ?? 0);
        $pr = (int) ($v["penduduk_perempuan"] ?? 0);
        if (isset($v['penduduk_laki'], $v['penduduk_perempuan'])) {
            $v["total"] = $lk + $pr;
        }
        return $v;
    }

    /**
     * 3.15 Kepemilikan Akta - hitung jumlah
     */
    protected static function calcAkta(array $v): array
    {
        $lk = (int) ($v["lk_0_18"] ?? 0);
        $pr = (int) ($v["pr_0_18"] ?? 0);
        if (isset($v['lk_0_18'], $v['pr_0_18'])) {
            $v["jumlah"] = $lk + $pr;
        }
        return $v;
    }

    /**
     * 3.17 Guru dan Murid - hitung rasio
     */
    protected static function calcGuruMurid(array $v): array
    {
        $guru = (int) ($v["jumlah_guru"] ?? 0);
        $murid = (int) ($v["jumlah_murid"] ?? 0);
        if ($guru > 0) {
            $v["rasio"] = (int) round($murid / $guru);
        }
        return $v;
    }

    /**
     * 4.42 Tanaman Pangan - hitung produktivitas
     */
    protected static function calcProduktivitas(array $v): array
    {
        $panen = (float) ($v["luas_panen_ha"] ?? 0);
        $prod = (float) ($v["produksi_ton"] ?? 0);
        if ($panen > 0) {
            $v["produktivitas_ton_ha"] = round($prod / $panen, 2);
        }
        return $v;
    }

    /**
     * 9.92 Kunjungan Wisatawan - hitung total wisman + wisnu
     */
    protected static function calcWisatawan(array $v): array
    {
        $ws = (int) ($v["wisman"] ?? 0);
        $wn = (int) ($v["wisnu"] ?? 0);
        if (isset($v['wisman'], $v['wisnu'])) {
            $v["total"] = $ws + $wn;
        }
        return $v;
    }

    protected static function calcDemografi(array $v): array
    {
        if (isset($v['kk_laki'], $v['kk_perempuan'])) {
            $v['jumlah'] = ($v['kk_laki'] ?? 0) + ($v['kk_perempuan'] ?? 0);
        }
        if (isset($v['penduduk_laki'], $v['penduduk_perempuan'])) {
            $v['jumlah'] = ($v['penduduk_laki'] ?? 0) + ($v['penduduk_perempuan'] ?? 0);
        }
        return $v;
    }

    /**
     * Get list of field yang di auto-calculate
     */
    public static function getAutoCalcFields(string $datasetKode): array
    {
        return match ($datasetKode) {
            '3.20' => ['persen_pengukuran', 'persen_stunting'],
            '4.47' => ['nilai_produksi_rp'],
            '7.75', '7.73' => ['persen_realisasi'],
            '3.12', '3.13', '9.92' => ['total'],
            '3.15'=>['jumlah'], '3.17'=>['rasio'], '4.42'=>['produktivitas_ton_ha'],
            default => [],
        };
    }
}
