<?php

namespace App\Services\DataInput;

class RoadInputDefinition
{
    public static function indicators(): array
    {
        return [
            'jalan_kabupaten'=>['Jalan Kabupaten','Panjang jalan kabupaten (km)','km'],
            'jalan_provinsi'=>['Jalan Provinsi','Panjang jalan provinsi (km)','km'],
            'jalan_nasional'=>['Jalan Nasional','Panjang jalan nasional (km)','km'],
            'permukaan_aspal'=>['Konstruksi Permukaan','Aspal/pavement (km)','km'],
            'permukaan_kerikil'=>['Konstruksi Permukaan','Penetrasi/kerikil (km)','km'],
            'permukaan_beton'=>['Konstruksi Permukaan','Beton (km)','km'],
            'permukaan_tanah'=>['Konstruksi Permukaan','Tanah (km)','km'],
            'kondisi_baik'=>['Kualifikasi Kondisi','Jalan kondisi baik (km)','km'],
            'kondisi_sedang'=>['Kualifikasi Kondisi','Jalan rusak sedang (km)','km'],
            'kondisi_ringan'=>['Kualifikasi Kondisi','Jalan rusak ringan (km)','km'],
            'kondisi_berat'=>['Kualifikasi Kondisi','Jalan rusak berat (km)','km'],
            'jumlah_jembatan'=>['Jembatan','Jumlah jembatan (unit)','unit'],
            'panjang_jembatan'=>['Jembatan','Panjang jembatan (m)','m'],
        ];
    }

    public static function normalize(array $values): array
    {
        $definitions=self::indicators();
        $key=$values['indikator_jalan']??null;
        if (!$key) foreach ($definitions as $candidate=>$definition) {
            if (($values['kategori']??null)===$definition[0] && ($values['uraian']??null)===$definition[1]) { $key=$candidate; break; }
        }
        if (is_string($key) && isset($definitions[$key])) {
            [$category,$description,$unit]=$definitions[$key];
            $values=array_merge($values,['indikator_jalan'=>$key,'kategori'=>$category,'uraian'=>$description,'satuan'=>$unit]);
        }
        return $values;
    }
}
