<?php

namespace App\Console\Commands;

use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PreviewAdminRecovery extends Command
{
    protected $signature = 'app:preview-admin-recovery {--resume : Resume the existing isolated preview} {--schema-only : Apply preview schema without rerunning seeders}';
    protected $description = 'Build an isolated recovery preview; never migrate the active application database.';

    public function handle(): int
    {
        $active = DB::connection();
        $source = $active->getDatabaseName();
        $target = 'kominfo_recovery_preview_20261025';
        if ($active->getDriverName() !== 'mysql' || $source === $target) {
            throw new RuntimeException('Preview must start from the active MySQL connection, not the preview database.');
        }
        $exists = $active->selectOne('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$target]);
        if ($exists && ! $this->option('resume')) {
            $this->error('Preview already exists; refusing to overwrite it.');
            return self::FAILURE;
        }
        if (! $exists) {
            $active->statement('CREATE DATABASE `'.$target.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
        $settings = config('database.connections.mysql');
        $settings['database'] = $target;
        $settings['url'] = null;
        config(['database.connections.recovery_preview' => $settings]);
        $preview = DB::connection('recovery_preview');
        $preview->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($active->select('SHOW TABLES') as $row) {
                if ($exists) {
                    break;
                }
                $table = array_values((array) $row)[0];
                $quoted = '`'.str_replace('`', '``', $table).'`';
                $ddl = array_values((array) $active->selectOne('SHOW CREATE TABLE '.$quoted))[1];
                $preview->statement($ddl);
                foreach ($active->table($table)->get()->chunk(100) as $rows) {
                    $preview->table($table)->insert($rows->map(fn ($row) => (array) $row)->all());
                }
            }
        } finally {
            $preview->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $originalConnection = config('database.default');
        DB::setDefaultConnection('recovery_preview');
        try {
            foreach ($this->migrationFiles() as $file) {
                $code = $this->call('migrate', [
                    '--database' => 'recovery_preview',
                    '--path' => 'database/migrations/'.$file,
                    '--force' => true,
                ]);
                if ($code !== 0) {
                    return self::FAILURE;
                }
            }
            if ($this->option('schema-only')) {
                $this->info('Preview schema ready; no seeders executed.');
                return self::SUCCESS;
            }
            if ($preview->table('dataset_values')->exists()) {
                throw new RuntimeException('Preview contains dataset values; refusing to rerun definition seeders.');
            }
            foreach ([
                ...($preview->table('wilayahs')->exists() ? [] : ['WilayahSangiheSeeder']), 'DatasetCategorySangiheSeeder',
                'DatasetSektoralSangiheSeeder', 'OpdDatasetSangiheSeeder',
                'DatasetFieldUpgradeSeeder', 'DatasetSpatialFieldsSeeder',
                'OfficialDistrictStatisticsSeeder', 'StatistikSektoral2024PanganSeeder',
            ] as $seeder) {
                if ($this->call('db:seed', ['--database' => 'recovery_preview', '--class' => 'Database\\Seeders\\'.$seeder, '--force' => true]) !== 0) {
                    return self::FAILURE;
                }
            }
            foreach (['users', 'opds', 'datasets', 'dataset_fields', 'dataset_values', 'wilayahs', 'dataset_submissions'] as $table) {
                $this->line($table.': '.$preview->table($table)->count());
            }
            $this->info('Preview ready: '.$target.'. Active database unchanged: '.$source);
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
        return self::SUCCESS;
    }

    public function migrationFiles(): array
    {
        return [
            '2026_10_17_000001_create_wilayahs_and_categories.php',
            '2026_10_25_000001_recover_admin_foundation.php',
            '2026_08_07_000001_extend_opds_table_for_government.php',
            '2026_08_07_000003_extend_users_table.php',
            '2026_07_11_000011_create_notifications_table.php',
            '2026_07_13_000011_add_payload_snapshot_to_submission_batches.php',
            '2026_07_13_000012_add_package_metadata_to_submission_batches.php',
            '2026_07_14_000013_add_module_and_period_fields_to_submission_batches.php',
            '2026_08_16_000001_add_forward_and_publish_flags_to_submission_batches.php',
            '_archive_legacy/2026_10_12_000001_create_source_attributions_table.php',
            '2026_10_12_000002_create_datasets_tables.php',
            '2026_10_12_000007_seed_32_opds.php',
            '2026_10_13_000002_enhance_datasets_with_sdi_metadata.php',
            '2026_10_13_000003_create_kode_referensis_table.php',
            '2026_10_13_000004_create_forum_satu_data_tables.php',
            '2026_10_13_000005_create_workflow_logs_table.php',
            '2026_10_13_000006_create_import_logs_table.php',
            '2026_10_13_000007_create_device_assignments_table.php',
            '2026_10_13_000008_fix_enum_and_add_urutan.php',
            '2026_10_13_000009_add_enum_to_dataset_fields_tipe.php',
            '2026_10_17_000002_add_sdi_5_elements_to_dataset_fields.php',
            '2026_10_17_000005_add_workflow_columns_to_dataset_values.php',
            '2026_10_18_000001_make_batch_id_nullable_in_workflow_logs.php',
            '2026_10_18_000002_create_dataset_submissions_table.php',
            '2026_10_19_000001_create_dataset_spatial_demos_table.php',
            '2026_08_01_000004_create_jobs_table.php',
            '2026_08_02_000001_create_ai_conversations_and_messages_tables.php',
            '2026_10_25_000002_complete_recovered_workflow.php',
            '2026_10_25_000003_add_dataset_value_editor.php',
            '2026_10_25_000004_complete_admin_support_tables.php',
        ];
    }
}
