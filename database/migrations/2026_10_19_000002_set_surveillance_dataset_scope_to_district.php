<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('datasets')
            ->where('kode', 'SURVEILANS_PENYAKIT')
            ->whereIn('opd_id', DB::table('opds')->select('id')->where('kode', 'DINKES'))
            ->whereNull('deleted_at')
            ->update(['cakupan_wilayah' => 'kecamatan', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('datasets')
            ->where('kode', 'SURVEILANS_PENYAKIT')
            ->whereIn('opd_id', DB::table('opds')->select('id')->where('kode', 'DINKES'))
            ->whereNull('deleted_at')
            ->update(['cakupan_wilayah' => 'kabupaten', 'updated_at' => now()]);
    }
};
