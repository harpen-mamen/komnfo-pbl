<?php

namespace Tests\Unit;

use App\Services\InfographicChartLayout;
use PHPUnit\Framework\TestCase;

class InfographicChartLayoutTest extends TestCase
{
    public function test_categories_do_not_offer_temporal_or_composition_charts(): void
    {
        $layout = (new InfographicChartLayout)->build(['values' => [6358, 936], 'labels' => ['Tabukan Utara', 'Nusa Tabukan'], 'type' => 'bar'], 1);
        self::assertSame('dot', $layout['default']);
        self::assertSame(['bar', 'column', 'dot'], array_keys($layout['options']));
        self::assertSame([], $layout['segments']);
    }

    public function test_temporal_charts_offer_lines_and_preserve_negative_values(): void
    {
        $layout = (new InfographicChartLayout)->build(['values' => [-5, 0, 10], 'labels' => ['2022', '2023', '2024'], 'type' => 'area', 'grouping' => 'time']);
        self::assertSame('area', $layout['default']);
        self::assertArrayHasKey('line', $layout['options']);
        self::assertGreaterThan($layout['zero'], $layout['points'][0]['y']);
        self::assertLessThan($layout['zero'], $layout['points'][2]['y']);
        self::assertSame([-5, 0, 10], array_column($layout['points'], 'value'));
    }

    public function test_donut_requires_positive_total_and_no_negative_values(): void
    {
        $service = new InfographicChartLayout;
        $layout = $service->build(['values' => [30, 70], 'type' => 'pie']);
        self::assertSame('doughnut', $layout['default']);
        self::assertSame([30.0, 70.0], array_column($layout['segments'], 'share'));
        foreach ([[0, 0], [-1, 3]] as $values) {
            self::assertArrayNotHasKey('doughnut', $service->build(['values' => $values, 'type' => 'pie'])['options']);
        }
    }
}
