<?php

namespace App\Services;

/** SVG layout shared by infographic cards; never changes or aggregates source values. */
class InfographicChartLayout
{
    public function build(array $chart, int $variant = 0): array
    {
        $values = array_values($chart['values'] ?? []);
        $count = count($values);
        $minimum = min(0, ...($values ?: [0]));
        $maximum = max(0, ...($values ?: [0]));
        $range = ($maximum - $minimum) ?: 1;
        $y = fn ($value) => 248 - (($value - $minimum) / $range * 208);
        $step = 560 / max(1, $count);
        $points = [];
        foreach ($values as $index => $value) {
            $points[] = [
                'x' => 80 + $step * ($index + 0.5), 'y' => $y($value),
                'value' => $value, 'label' => (string) ($chart['labels'][$index] ?? ''),
            ];
        }
        $time = ($chart['grouping'] ?? '') === 'time';
        $labels = array_map('strval', $chart['labels'] ?? []);
        $longLabels = $labels !== [] && max(array_map(fn (string $label): int => mb_strlen($label), $labels)) > 18;
        $composition = in_array($chart['type'] ?? '', ['pie', 'doughnut'], true)
            && $count >= 2 && $count <= 10 && $minimum >= 0 && array_sum($values) > 0;
        $options = ['bar' => 'Batang horizontal', 'column' => 'Batang vertikal', 'dot' => 'Titik perbandingan'];
        if ($time) $options += ['line' => 'Garis', 'area' => 'Area'];
        if ($composition) $options['doughnut'] = 'Donat';
        $default = match (true) {
            $time => ($chart['type'] ?? '') === 'area' ? 'area' : 'line',
            $composition => 'doughnut',
            ($chart['type'] ?? '') === 'histogram' => 'column',
            $count > 10 || $longLabels => 'bar',
            default => $variant % 3 === 0 ? 'column' : ($variant % 3 === 1 ? 'dot' : 'bar'),
        };
        $ticks = [];
        for ($index = 0; $index <= 4; $index++) {
            $value = $minimum + $range * $index / 4;
            $ticks[] = ['value' => $value, 'y' => $y($value)];
        }
        $segments = [];
        $offset = 0;
        if ($composition) {
            foreach ($points as $point) {
                $share = $point['value'] / array_sum($values) * 100;
                $segments[] = $point + ['share' => $share, 'offset' => $offset];
                $offset += $share;
            }
        }
        $polyline = implode(' ', array_map(fn ($p) => $p['x'].','.$p['y'], $points));
        return compact('points', 'ticks', 'segments', 'options', 'default', 'polyline', 'step') + [
            'zero' => $y(0),
            'area' => $count ? $points[0]['x'].','.$y(0).' '.$polyline.' '.$points[$count - 1]['x'].','.$y(0) : '',
        ];
    }
}
