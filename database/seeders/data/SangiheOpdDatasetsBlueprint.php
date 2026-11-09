<?php

/**
 * BLUEPRINT Dataset untuk 32 OPD di Kabupaten Kepulauan Sangihe.
 *
 * Format: OPD_CODE => [datasets => [CODE => [config]]]
 *
 * Disusun berdasarkan tupoksi Permendagri 90/2019 dan Perpres 39/2019
 * (Satu Data Indonesia). Dataset khusus Kepulauan Sangihe ditandai
 * dengan komentar [KHAS SANGIHE].
 *
 * File ini dipecah menjadi 3 bagian karena ukurannya besar.
 * Semua bagian digabung saat di-load oleh seeder.
 */

return array_merge(
    require __DIR__.'/SangiheOpdDatasetsPart1.php',
    require __DIR__.'/SangiheOpdDatasetsPart2.php',
    require __DIR__.'/SangiheOpdDatasetsPart3.php'
);
