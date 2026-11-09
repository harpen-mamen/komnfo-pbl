<?php

namespace App\Console\Commands;

use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ApplyAdminRecovery extends Command
{
    protected $signature = 'app:apply-admin-recovery {--apply : Apply the already tested recovery to the audited kominfo database}';
    protected $description = 'Apply the isolated-preview-tested additive recovery after backing up existing schema and rows.';

    public function handle(): int
    {
        if (! $this->option('apply')) {
            $this->info('Dry run: creates missing admin/dataset tables, seeds 32 OPD and dataset definitions, and imports repository-transcribed 2024 statistics. Existing users and roles are preserved. Use --apply only after reviewing the preview.');
            return self::SUCCESS;
        }
        if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'kominfo') {
            throw new RuntimeException('Recovery is restricted to the audited kominfo MySQL database.');
        }
        foreach (['datasets', 'dataset_values', 'opds', 'wilayahs'] as $table) {
            if (Schema::hasTable($table)) {
                throw new RuntimeException('Preflight state changed: '.$table.' exists. Audit before proceeding; no changes made.');
            }
        }
        $previewConfig = config('database.connections.mysql');
        $previewConfig['database'] = 'kominfo_recovery_preview_20261025';
        $previewConfig['url'] = null;
        config(['database.connections.recovery_verified' => $previewConfig]);
        $preview = DB::connection('recovery_verified');
        if ($preview->table('opds')->count() !== 32 || $preview->table('datasets')->count() !== 178 || $preview->table('dataset_values')->count() !== 251) {
            throw new RuntimeException('Verified recovery preview is incomplete.');
        }

        $beforeUsers = DB::table('users')->orderBy('id')->get()->toJson();
        $backup = storage_path('backups/admin-before-recovery-'.now()->format('Ymd-His').'.json');
        File::ensureDirectoryExists(dirname($backup), 0700);
        $tables = [];
        foreach (DB::select('SHOW TABLES') as $row) {
            $table = array_values((array) $row)[0];
            $quoted = '`'.str_replace('`', '``', $table).'`';
            $tables[$table] = [
                'schema' => array_values((array) DB::selectOne('SHOW CREATE TABLE '.$quoted))[1],
                'rows' => DB::table($table)->get(),
            ];
        }
        File::put($backup, json_encode(['database' => 'kominfo', 'created_at' => now()->toIso8601String(), 'tables' => $tables], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        $this->info('Backup saved: '.$backup);

        foreach ((new PreviewAdminRecovery())->migrationFiles() as $file) {
            if ($this->call('migrate', ['--database' => 'mysql', '--path' => 'database/migrations/'.$file, '--force' => true]) !== 0) {
                $this->error('Stopped at '.$file.'. Existing data backup: '.$backup);
                return self::FAILURE;
            }
        }
        foreach ([
            'WilayahSangiheSeeder', 'DatasetCategorySangiheSeeder', 'DatasetSektoralSangiheSeeder',
            'OpdDatasetSangiheSeeder', 'DatasetFieldUpgradeSeeder', 'DatasetSpatialFieldsSeeder',
            'OfficialDistrictStatisticsSeeder', 'StatistikSektoral2024PanganSeeder',
        ] as $seeder) {
            if ($this->call('db:seed', ['--database' => 'mysql', '--class' => 'Database\\Seeders\\'.$seeder, '--force' => true]) !== 0) {
                return self::FAILURE;
            }
        }
        // New nullable profile/scope columns are expected; compare original fields.
        $originalUsers = json_decode($beforeUsers, true);
        foreach ($originalUsers as $original) {
            $current = (array) DB::table('users')->find($original['id']);
            foreach ($original as $column => $value) {
                if (($current[$column] ?? null) !== $value) {
                    throw new RuntimeException('Original user field changed unexpectedly: '.$column);
                }
            }
        }
        $this->info('Recovery applied; original account fields and password hashes preserved.');
        foreach (['opds', 'datasets', 'dataset_fields', 'dataset_values', 'wilayahs'] as $table) {
            $this->line($table.': '.DB::table($table)->count());
        }
        return self::SUCCESS;
    }
}
