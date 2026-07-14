<?php

namespace App\Filament\Resources\Concerns;

trait HasPeriodOptions
{
    public static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public static function semesterOptions(): array
    {
        return [
            1 => 'Semester 1',
            2 => 'Semester 2',
        ];
    }

    public static function yearOptions(int $range = 5): array
    {
        $year = (int) now()->year;

        return collect(range($year - $range, $year + $range))->mapWithKeys(fn (int $item) => [$item => (string) $item])->all();
    }
}
