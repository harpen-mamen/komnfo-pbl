<?php

/**
 * Script untuk membuat admin OPD baru
 * Usage: php artisan tinker scripts/create_opd_admin.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Opd;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// Get OPD
$opds = Opd::where('aktif', true)->get(['id', 'kode', 'nama', 'kategori']);

echo "=== Create OPD Admin ===\n\n";

echo "Available OPDs:\n";
foreach ($opds as $opd) {
    echo "  [{$opd->kode}] {$opd->nama} (kategori: {$opd->kategori})\n";
}

echo "\n=== Role Mappings ===\n";
echo "  kesehatan     -> admin_kesehatan / operator_kesehatan\n";
echo "  pendidikan    -> admin_pendidikan / operator_pendidikan\n";
echo "  pu            -> admin_pu / operator_pu\n";
echo "  sosial        -> admin_sosial / operator_sosial\n";
echo "  ekonomi       -> admin_ekonomi / operator_ekonomi\n";
echo "  kependudukan  -> admin_kependudukan / operator_kependudukan\n";
echo "  lingkungan    -> admin_lingkungan / operator_lingkungan\n";

echo "\nScript ready. Run with Tinker to create users.\n";
