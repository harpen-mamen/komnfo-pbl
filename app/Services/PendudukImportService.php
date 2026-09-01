<?php

namespace App\Services;

use App\Models\ImportPendudukLog;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Support\AdminKecamatan;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PendudukImportService
{
    public const MAX_ROWS = 10000;
    public const HEADERS = [
        'nomor_kk',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'pendidikan',
        'pekerjaan',
        'penghasilan',
        'nomor_hp',
    ];

    public function import(string $path): array
    {
        if (! is_file($path) || filesize($path) > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('File import tidak ditemukan atau melebihi batas 10 MB.');
        }

        $rows = $this->readRows($path);
        if (count($rows) > self::MAX_ROWS) {
            throw new \InvalidArgumentException('File import maksimal berisi '.self::MAX_ROWS.' baris data.');
        }
        $created = 0;
        $failed = 0;

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = $this->normalizeRow($row);
            $keluarga = Keluarga::query()
                ->visibleToCurrentUser()
                ->where('nomor_kk', $data['nomor_kk'] ?? null)
                ->first();

            if (! $keluarga) {
                $this->logFailure($data, $line, 'Nomor KK tidak ditemukan di kecamatan admin.');
                $failed++;
                continue;
            }

                
            $validator = Validator::make($data, [
                'nomor_kk' => ['required'],
                'nik' => ['required', 'max:32', Rule::unique('penduduk', 'nik')],
                'nama_lengkap' => ['required', 'max:255'],
                'jenis_kelamin' => ['required', Rule::in(array_keys(Penduduk::JENIS_KELAMIN))],
                'tempat_lahir' => ['required', 'max:255'],
                'tanggal_lahir' => ['required', 'date'],
                'agama' => ['required', Rule::in(array_keys(Penduduk::AGAMA))],
                'status_perkawinan' => ['required', Rule::in(array_keys(Penduduk::STATUS_PERKAWINAN))],
                'pendidikan' => ['required', Rule::in(array_keys(Penduduk::PENDIDIKAN))],
                'penghasilan' => ['nullable', 'numeric', 'min:0'],
                'nomor_hp' => ['nullable', 'max:32'],
            ]);

            if ($validator->fails()) {
                $this->logFailure($data, $line, $validator->errors()->first(), $keluarga->rumah?->desa_id);
                $failed++;
                continue;
            }

            Penduduk::query()->create([
                'keluarga_id' => $keluarga->id,
                'nik' => $data['nik'],
                'nama_lengkap' => $data['nama_lengkap'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => Carbon::parse($data['tanggal_lahir'])->toDateString(),
                'agama' => $data['agama'],
                'status_perkawinan' => $data['status_perkawinan'],
                'pendidikan' => $data['pendidikan'],
                'pekerjaan' => $data['pekerjaan'] ?: null,
                'penghasilan' => $data['penghasilan'] ?: null,
                'status_dalam_keluarga' => 'Lainnya',
                'nomor_hp' => $data['nomor_hp'] ?: null,
                'status_penduduk' => 'Tetap',
                'created_by' => Auth::id(),
            ]);

            $keluarga->increment('jumlah_anggota');
            $created++;
        }

        return ['created' => $created, 'failed' => $failed];
    }

    private function readRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['xls', 'xlsx'], true) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return $this->readSpreadsheetRows($path);
        }

        $content = file_get_contents($path);
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = html_entity_decode(strip_tags($content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $delimiter = str_contains(strtok($content, "\r\n") ?: '', "\t") ? "\t" : ',';
        $handle = fopen('php://temp', 'rb+');
        fwrite($handle, $content);
        rewind($handle);

        $header = array_map(fn ($value) => strtolower(trim((string) $value)), fgetcsv($handle, 0, $delimiter) ?: []);
        $rows = [];

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (array_filter($line) === []) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);

        return $rows;
    }

    private function readSpreadsheetRows(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);
        $header = array_map(fn ($value) => strtolower(trim((string) $value)), array_shift($rawRows) ?: []);
        $rows = [];

        foreach ($rawRows as $line) {
            if (array_filter($line, fn ($value) => trim((string) $value) !== '') === []) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        return $rows;
    }

    private function normalizeRow(array $row): array
    {
        $normalized = collect(self::HEADERS)
            ->mapWithKeys(fn (string $key) => [$key => trim((string) Arr::get($row, $key, ''))])
            ->all();

        if ($normalized['nama_lengkap'] === '') {
            $normalized['nama_lengkap'] = trim((string) Arr::get($row, 'nama', ''));
        }

        return $normalized;
    }

    private function logFailure(array $data, int $line, string $reason, ?int $desaId = null): void
    {
        ImportPendudukLog::query()->create([
            'desa_id' => $desaId,
            'kecamatan_id' => AdminKecamatan::kecamatanId(),
            'nomor_kk' => $data['nomor_kk'] ?? null,
            'nik' => $data['nik'] ?? null,
            'nama' => $data['nama_lengkap'] ?? null,
            'baris' => $line,
            'alasan' => $reason,
            'created_by' => Auth::id(),
        ]);
    }
}
