<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SangiheDesaSeeder extends Seeder
{
    /** Source: Kepmendagri 300.2.2-2430/2025, wilayah.sql, Sangihe section. */
    private const DISTRICTS = [
        'Tabukan Utara' => '710308', 'Nusa Tabukan' => '710309',
        'Manganitu Selatan' => '710310', 'Tatoareng' => '710311',
        'Tamako' => '710312', 'Manganitu' => '710313',
        'Tabukan Tengah' => '710314', 'Tabukan Selatan' => '710315',
        'Kendahe' => '710316', 'Tahuna' => '710317',
        'Tabukan Selatan Tengah' => '710319', 'Tabukan Selatan Tenggara' => '710320',
        'Tahuna Barat' => '710323', 'Tahuna Timur' => '710324',
        'Kepulauan Marore' => '710325',
    ];

    /** [kode wilayah Kemendagri tanpa titik, kecamatan, nama desa/kelurahan]. */
    private const VILLAGES = [
        ['7103082001','Tabukan Utara','Kalasuge'], ['7103082002','Tabukan Utara','Bahu'], ['7103082003','Tabukan Utara','Mala'], ['7103082004','Tabukan Utara','Kalekube'],
        ['7103082005','Tabukan Utara','Naha'], ['7103082006','Tabukan Utara','Beha'], ['7103082007','Tabukan Utara','Utaurano'], ['7103082008','Tabukan Utara','Lenganeng'],
        ['7103082009','Tabukan Utara','Tarolang'], ['7103082010','Tabukan Utara','Tola'], ['7103082011','Tabukan Utara','Kalurae'], ['7103082012','Tabukan Utara','Bengketang'],
        ['7103082013','Tabukan Utara','Petta'], ['7103082014','Tabukan Utara','Bowongkulu'], ['7103082016','Tabukan Utara','Pusunge'], ['7103082017','Tabukan Utara','Moade'],
        ['7103082019','Tabukan Utara','Raku'], ['7103082020','Tabukan Utara','Petta Timur'], ['7103082021','Tabukan Utara','Petta Barat'], ['7103082022','Tabukan Utara','Petta Selatan'],
        ['7103082023','Tabukan Utara','Likuang'], ['7103082024','Tabukan Utara','Kalekube I'], ['7103082025','Tabukan Utara','Bowongkulu I'], ['7103082026','Tabukan Utara','Naha I'],
        ['7103092001','Nusa Tabukan','Nanedakele'], ['7103092002','Nusa Tabukan','Nusa'], ['7103092003','Nusa Tabukan','Bukide'], ['7103092004','Nusa Tabukan','Bukide Timur'], ['7103092005','Nusa Tabukan','Nanusa'],
        ['7103102001','Manganitu Selatan','Kaluwatu'], ['7103102002','Manganitu Selatan','Laine'], ['7103102003','Manganitu Selatan','Lapango'], ['7103102004','Manganitu Selatan','Sowaeng'],
        ['7103102005','Manganitu Selatan','Ngalipaeng I'], ['7103102006','Manganitu Selatan','Ngalipaeng II'], ['7103102007','Manganitu Selatan','Batunderang'], ['7103102008','Manganitu Selatan','Bebalang'],
        ['7103102009','Manganitu Selatan','Mawira'], ['7103102010','Manganitu Selatan','Pindang'], ['7103102011','Manganitu Selatan','Lapepahe'], ['7103102012','Manganitu Selatan','Lehimi Tariang'], ['7103102013','Manganitu Selatan','Lapango I'],
        ['7103112001','Tatoareng','Kalama'], ['7103112002','Tatoareng','Kahakitang'], ['7103112003','Tatoareng','Mahengetang'], ['7103112004','Tatoareng','Para'],
        ['7103112005','Tatoareng','Dalako Bembanehe'], ['7103112006','Tatoareng','Taleko Batusaiki'], ['7103112007','Tatoareng','Para I'],
        ['7103122001','Tamako','Nagha I'], ['7103122002','Tamako','Nagha II'], ['7103122003','Tamako','Pokol'], ['7103122004','Tamako','Balane'], ['7103122005','Tamako','Binala'],
        ['7103122006','Tamako','Ulung Peliang'], ['7103122007','Tamako','Menggawa'], ['7103122008','Tamako','Kalinda'], ['7103122009','Tamako','Bebu'], ['7103122010','Tamako','Makalekuhe'],
        ['7103122011','Tamako','Pananaru'], ['7103122012','Tamako','Dagho'], ['7103122013','Tamako','Mahumu'], ['7103122014','Tamako','Lelipang'], ['7103122015','Tamako','Menggawa II'],
        ['7103122016','Tamako','Kalama Darat'], ['7103122017','Tamako','Kalinda I'], ['7103122018','Tamako','Mahumu I'], ['7103122019','Tamako','Mahumu II'], ['7103122020','Tamako','Hesang'],
        ['7103132001','Manganitu','Tawoali'], ['7103132002','Manganitu','Barangka'], ['7103132003','Manganitu','Nahepese'], ['7103132004','Manganitu','Manumpitaeng'],
        ['7103132005','Manganitu','Taloarane'], ['7103132006','Manganitu','Mala'], ['7103132007','Manganitu','Karatung I'], ['7103132008','Manganitu','Karatung II'],
        ['7103132009','Manganitu','Kauhis'], ['7103132010','Manganitu','Sesiwung'], ['7103132011','Manganitu','Lebo'], ['7103132012','Manganitu','Barangkalang'],
        ['7103132013','Manganitu','Belengang'], ['7103132014','Manganitu','Bakalaeng'], ['7103132015','Manganitu','Hiung'], ['7103132016','Manganitu','Pinebentengang'],
        ['7103132017','Manganitu','Taloarane I'], ['7103132018','Manganitu','Bengka'],
        ['7103142001','Tabukan Tengah','Bowongkali'], ['7103142002','Tabukan Tengah','Kulur I'], ['7103142003','Tabukan Tengah','Kulur II'], ['7103142004','Tabukan Tengah','Bira'],
        ['7103142005','Tabukan Tengah','Kuma'], ['7103142006','Tabukan Tengah','Bungalawang'], ['7103142007','Tabukan Tengah','Miulu'], ['7103142008','Tabukan Tengah','Gunung'],
        ['7103142009','Tabukan Tengah','Talengen'], ['7103142010','Tabukan Tengah','Biru'], ['7103142011','Tabukan Tengah','Tariang Baru'], ['7103142012','Tabukan Tengah','Sensong'],
        ['7103142013','Tabukan Tengah','Rendingan'], ['7103142014','Tabukan Tengah','Palahanaeng'], ['7103142015','Tabukan Tengah','Timbelang'], ['7103142016','Tabukan Tengah','Pelelangen'],
        ['7103142017','Tabukan Tengah','Malueng'], ['7103142018','Tabukan Tengah','Kuma I'],
        ['7103152001','Tabukan Selatan','Simueng'], ['7103152002','Tabukan Selatan','Bentung'], ['7103152003','Tabukan Selatan','Lesabe'], ['7103152004','Tabukan Selatan','Malammenggu'],
        ['7103152005','Tabukan Selatan','Palareng'], ['7103152007','Tabukan Selatan','Binebas'], ['7103152008','Tabukan Selatan','Mandoi'], ['7103152015','Tabukan Selatan','Batuwingkung'],
        ['7103152016','Tabukan Selatan','Birahi'], ['7103152017','Tabukan Selatan','Laotongan'], ['7103152018','Tabukan Selatan','Bukide'], ['7103152019','Tabukan Selatan','Kalagheng'],
        ['7103152020','Tabukan Selatan','Lesabe I'], ['7103152021','Tabukan Selatan','Bulo'],
        ['7103162001','Kendahe','Kendahe I'], ['7103162002','Kendahe','Kendahe II'], ['7103162003','Kendahe','Talawid'], ['7103162004','Kendahe','Tariang Lama'],
        ['7103162005','Kendahe','Pempalaraeng'], ['7103162006','Kendahe','Mohong Sawang'], ['7103162007','Kendahe','Lipang'], ['7103162008','Kendahe','Kawaluso'],
        ['7103171006','Tahuna','Sawang Bendar'], ['7103171007','Tahuna','Soataloara I'], ['7103171008','Tahuna','Apengsembeka'], ['7103171009','Tahuna','Mahena'],
        ['7103171015','Tahuna','Bungalawang'], ['7103171016','Tahuna','Santiago'], ['7103171017','Tahuna','Manente'], ['7103171018','Tahuna','Soataloara II'],
        ['7103192001','Tabukan Selatan Tengah','Hangke'], ['7103192002','Tabukan Selatan Tengah','Salurang'], ['7103192003','Tabukan Selatan Tengah','Tambung'],
        ['7103192004','Tabukan Selatan Tengah','Beeng'], ['7103192005','Tabukan Selatan Tengah','Lehupu'], ['7103192006','Tabukan Selatan Tengah','Bowone'],
        ['7103192007','Tabukan Selatan Tengah','Beeng Laut'], ['7103192008','Tabukan Selatan Tengah','Tenda'], ['7103192009','Tabukan Selatan Tengah','Aha Patung'],
        ['7103202001','Tabukan Selatan Tenggara','Pintareng'], ['7103202002','Tabukan Selatan Tenggara','Basauh'], ['7103202003','Tabukan Selatan Tenggara','Tumalede'],
        ['7103202004','Tabukan Selatan Tenggara','Sampakang'], ['7103202005','Tabukan Selatan Tenggara','Dalokaweng'], ['7103202006','Tabukan Selatan Tenggara','Malisade'],
        ['7103231001','Tahuna Barat','Pananekeng'], ['7103231002','Tahuna Barat','Angges'], ['7103231003','Tahuna Barat','Kolongan Mitung'],
        ['7103231004','Tahuna Barat','Kolongan Beha'], ['7103231005','Tahuna Barat','Kolongan Beha Baru'], ['7103231006','Tahuna Barat','Kolongan Akembawi'],
        ['7103241001','Tahuna Timur','Lesa'], ['7103241002','Tahuna Timur','Enengpahembang'], ['7103241003','Tahuna Timur','Tapuang'], ['7103241004','Tahuna Timur','Tidore'],
        ['7103241005','Tahuna Timur','Tona I'], ['7103241006','Tahuna Timur','Tona II'], ['7103241007','Tahuna Timur','Dumuhung'], ['7103241008','Tahuna Timur','Batulewehe'],
        ['7103252001','Kepulauan Marore','Marore'], ['7103252002','Kepulauan Marore','Kawio'], ['7103252003','Kepulauan Marore','Matutuang'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $districts = [];
            foreach (self::DISTRICTS as $name => $code) {
                $district = DB::table('wilayahs')->where('tipe', 'kecamatan')->where('nama', $name)->first();
                if (! $district) {
                    throw new RuntimeException("Kecamatan {$name} tidak ditemukan; master desa dibatalkan.");
                }
                DB::table('wilayahs')->where('id', $district->id)->update(['kode_bps' => $code, 'updated_at' => now()]);
                $districts[$name] = (int) $district->id;
            }

            foreach (self::VILLAGES as [$code, $districtName, $name]) {
                DB::table('wilayahs')->updateOrInsert(
                    ['kode_bps' => $code],
                    [
                        'parent_id' => $districts[$districtName],
                        'tipe' => 'desa',
                        'nama' => $name,
                        'aktif' => true,
                        'metadata' => json_encode([
                            'sumber' => 'Kepmendagri 300.2.2-2430 Tahun 2025',
                            'kode_wilayah_kemendagri' => $this->formatCode($code),
                            'bukan_koordinat_batas_desa' => true,
                        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }

            $demo = DB::table('wilayahs')->where('kode_bps', 'SIM-710310-01')->first();
            if ($demo) {
                $demoValues = DB::table('dataset_values')->where('wilayah_id', $demo->id)->where('notes', 'like', 'DATA SIMULASI%')->count();
                $hasPoints = DB::table('mobile_field_points')->where('desa_id', $demo->id)->exists();
                $hasUsers = DB::table('users')->where('desa_id', $demo->id)->exists();
                if ($hasPoints || $hasUsers) {
                    throw new RuntimeException('Desa simulasi sudah dipakai akun/titik; tidak dihapus.');
                }
                if ($demoValues > 0) {
                    DB::table('dataset_values')->where('wilayah_id', $demo->id)->where('notes', 'like', 'DATA SIMULASI%')->delete();
                }
                DB::table('wilayahs')->where('id', $demo->id)->delete();
            }
        });
    }

    private function formatCode(string $code): string
    {
        return substr($code, 0, 2).'.'.substr($code, 2, 2).'.'.substr($code, 4, 2).'.'.substr($code, 6, 4);
    }
}
