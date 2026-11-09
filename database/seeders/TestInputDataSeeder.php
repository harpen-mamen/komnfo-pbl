<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class TestInputDataSeeder extends Seeder
{
    public function run(): void
    {
        $walidata = User::whereHas("roles", function($q) { $q->where("name", "admin_kominfo"); })->first();
        $admin = User::whereHas("roles", function($q) { $q->where("name", "super_admin"); })->first();
        if (!$walidata || !$admin) { $this->command->warn("User not found"); return; }
        $kecs = Wilayah::where("tipe", "kecamatan")->orderBy("id")->get();
        $tv = 0; $td = 0; $ts = 0; $tvf = 0; $tp = 0;

        $tests = [
            // [dataset_kode, rows_data, status]
            ["3.12", [["Tabukan Utara", 6358, 1984, 8342], ["Tamako", 4143, 1308, 5451]], "published"],
            ["3.20", [["Tabukan Utara", "Baik", 250], ["Tamako", "Buruk", 12]], "verified"],
            ["3.17", [["SD", "Tabukan Utara", 65, 920], ["SMP", "Tamako", 28, 410]], "submitted"],
            ["6.64", [["Mikro", 374, 32014240000], ["Kecil", 286, 2815211800875], ["Besar", 47, 140272855689]], "published"],
            ["7.76", [["Pajak Hotel", 150000000, 95000000], ["Pajak Restoran", 75000000, 50000000]], "draft"],
            ["5.59", [["Ruas Bitung-Tahuna", 45.5, "Baik"], ["Ruas Tahuna-Tamako", 22.7, "Sedang"]], "draft"],
            ["5.50", [["R2", 8542], ["R4", 1287], ["R6", 156]], "verified"],
            ["9.88", [["Nasional", 3.12, "Baik"], ["Provinsi Sulawesi Utara", 3.01, "Baik"], ["Kab. Kep. Sangihe", 2.46, "Cukup"]], "published"],
        ];

        foreach ($tests as $t) {
            list($kode, $rows, $st) = $t;
            $ds = Dataset::where("kode", $kode)->first();
            if (!$ds) continue;
            foreach ($rows as $r) {
                $data = ["dataset_id" => $ds->id, "tahun" => 2024, "periode_label" => "2024", "values" => []];
                $values = [];
                if ($kode == "3.12") {
                    [$kc, $lk, $pr, $tot] = $r;
                    $w = $kecs->firstWhere("nama", $kc);
                    if (!$w) continue;
                    $data["wilayah_type"] = "kecamatan"; $data["wilayah_id"] = $w->id;
                    $values = ["kecamatan" => $kc, "lk" => $lk, "pr" => $pr, "total" => $tot];
                } elseif ($kode == "3.20") {
                    [$kc, $s, $j] = $r;
                    $w = $kecs->firstWhere("nama", $kc);
                    if (!$w) continue;
                    $data["wilayah_type"] = "kecamatan"; $data["wilayah_id"] = $w->id;
                    $values = ["kecamatan" => $kc, "status" => $s, "jumlah" => $j];
                } elseif ($kode == "3.17") {
                    [$jj, $kc, $g, $m] = $r;
                    $w = $kecs->firstWhere("nama", $kc);
                    if (!$w) continue;
                    $data["wilayah_type"] = "kecamatan"; $data["wilayah_id"] = $w->id;
                    $values = ["jenjang" => $jj, "kecamatan" => $kc, "guru" => $g, "murid" => $m];
                } elseif ($kode == "6.64") {
                    [$ju, $j, $n] = $r;
                    $values = ["jenis_usaha" => $ju, "jumlah" => $j, "nilai" => $n];
                } elseif ($kode == "7.76") {
                    [$j, $t, $rl] = $r;
                    $values = ["jenis" => $j, "target" => $t, "realisasi" => $rl];
                } elseif ($kode == "5.59") {
                    [$ru, $p, $k] = $r;
                    $values = ["ruas" => $ru, "panjang" => $p, "kondisi" => $k];
                } elseif ($kode == "5.50") {
                    [$j, $jm] = $r;
                    $values = ["jenis" => $j, "jumlah" => $jm];
                } elseif ($kode == "9.88") {
                    [$in, $idx, $pr] = $r;
                    $values = ["instansi" => $in, "indeks" => $idx, "predikat" => $pr];
                }
                $data["values"] = $values;
                $data["status"] = $st;
                $data["status_verifikasi"] = $st;
                $data["created_by"] = $admin->id;
                if ($st == "verified" || $st == "published") {
                    $data["verified_at"] = now();
                    $data["verified_by"] = $walidata->id;
                }
                if ($st == "published") {
                    $data["published_at"] = now();
                }
                DatasetValue::create($data);
                $tv++;
                if ($st == "draft") $td++;
                elseif ($st == "submitted") $ts++;
                elseif ($st == "verified") $tvf++;
                elseif ($st == "published") $tp++;
            }
            $this->command->info("OK $kode $st");
        }
        $this->command->info("Total: $tv | D:$td S:$ts V:$tvf P:$tp");
    }
}
