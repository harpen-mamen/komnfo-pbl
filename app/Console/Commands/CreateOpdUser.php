<?php

namespace App\Console\Commands;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateOpdUser extends Command
{
    protected $signature = 'opd:create-user
                            {email : Email user}
                            {name : Nama user}
                            {opd_kode : Kode OPD (DINKES, DISDIK, DPUPR, DINSOS, DISPERINDAG, DUKCAPIL, DLH, DISTAN, DISHUB, DISTPAR, DISPORA, BKPSDMD, BAPPEDA, BAPENDA, BPBD, DISKOMINFO, DPMPTSP, DISTAMAN)}
                            {role=admin : Role (admin atau operator)}';

    protected $description = 'Buat user admin atau operator OPD Kabupaten Kepulauan Sangihe';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $opdKode = strtoupper($this->argument('opd_kode'));
        $roleType = strtolower($this->argument('role'));

        // Find OPD
        $opd = Opd::where('kode', $opdKode)->first();
        if (!$opd) {
            $this->error("OPD dengan kode '{$opdKode}' tidak ditemukan.");
            $this->info("Kode OPD yang tersedia:");
            foreach (Opd::where('aktif', true)->get() as $o) {
                $this->line("  - {$o->kode}: {$o->nama}");
            }
            return self::FAILURE;
        }

        // Determine role based on OPD kategori
        $roleName = $this->getRoleName($opd->kategori, $roleType);
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("Role '{$roleName}' tidak ditemukan.");
            return self::FAILURE;
        }

        // Check if user exists
        if (User::where('email', $email)->exists()) {
            $this->error("User dengan email '{$email}' sudah ada.");
            return self::FAILURE;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'opd_id' => $opd->id,
            'jabatan' => ucfirst($roleType) . ' ' . $opd->nama,
        ]);

        $user->assignRole($role);

        $this->info("User berhasil dibuat!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Nama', $user->name],
                ['Email', $user->email],
                ['OPD', $opd->nama],
                ['Kategori', Opd::kategoriLabels($opd->kategori)],
                ['Role', $roleName],
                ['Verifikator', $opd->is_verifikator ? 'Ya' : 'Tidak'],
                ['Password Default', 'password'],
            ]
        );

        return self::SUCCESS;
    }

    private function getRoleName(string $kategori, string $roleType): string
    {
        $prefix = $roleType === 'operator' ? 'operator' : 'admin';

        return match ($kategori) {
            Opd::KATEGORI_KESEHATAN => "{$prefix}_dinkes",
            Opd::KATEGORI_PENDIDIKAN => "{$prefix}_dispend",
            Opd::KATEGORI_PU => "{$prefix}_dpu",
            Opd::KATEGORI_SOSIAL => "{$prefix}_dinsos",
            Opd::KATEGORI_PERDAGANGAN => "{$prefix}_disperindag",
            Opd::KATEGORI_KEPENDUDUKAN => "{$prefix}_dukcapil",
            Opd::KATEGORI_LINGKUNGAN => "{$prefix}_dlh",
            Opd::KATEGORI_PERTANIAN => "{$prefix}_distan",
            Opd::KATEGORI_HUBUNGAN => "{$prefix}_dishub",
            Opd::KATEGORI_PARIWISATA => "{$prefix}_distpar",
            Opd::KATEGORI_PEMUDA => "{$prefix}_dispora",
            Opd::KATEGORI_KEPEGAWAIAN => "{$prefix}_bkpsdmd",
            Opd::KATEGORI_PERENCANAAN => "{$prefix}_bappeda",
            Opd::KATEGORI_KEUANGAN => "{$prefix}_bapenda",
            Opd::KATEGORI_BENCANA => "{$prefix}_bpbd",
            Opd::KATEGORI_KOMINFO => "{$prefix}_diskominfo",
            Opd::KATEGORI_INVESTASI => "{$prefix}_dpmptsp",
            Opd::KATEGORI_PERKEBUNAN => "{$prefix}_distaman",
            default => "{$prefix}_opd",
        };
    }
}
