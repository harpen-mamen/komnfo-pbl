<?php

namespace Tests\Unit;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Services\DynamicChartService;
use App\Services\InfographicPresentationService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class InfographicPresentationTest extends TestCase
{
    private function dataset(string $code, array $fields, array $config=[]): Dataset
    {
        $dataset = new Dataset(['kode'=>$code,'nama'=>'Indikator pengairan','period_type'=>'tahunan','config'=>$config]);
        $dataset->setRelation('fields',new Collection(array_map(fn($field)=>new DatasetField($field),$fields)));
        return $dataset;
    }

    public function test_bridge_count_and_length_are_never_added_together(): void
    {
        $dataset=$this->dataset('5.59',[]);
        $rows=collect([
            new DatasetValue(['tahun'=>2024,'values'=>['kategori'=>'Jalan Kabupaten','uraian'=>'Panjang jalan kabupaten (km)','nilai'=>379.97]]),
            new DatasetValue(['tahun'=>2024,'values'=>['kategori'=>'Jembatan','uraian'=>'Jumlah jembatan (unit)','nilai'=>170]]),
            new DatasetValue(['tahun'=>2024,'values'=>['kategori'=>'Jembatan','uraian'=>'Panjang jembatan (m)','nilai'=>1714]]),
        ]);
        $charts=(new InfographicPresentationService)->roadCharts($dataset,$rows);
        self::assertSame(['km','unit','m'],array_column($charts,'unit'));
        self::assertSame([[379.97],[170.0],[1714.0]],array_column($charts,'values'));
        self::assertSame('Jumlah jembatan',$charts[1]['title']);
        self::assertSame(3,$rows->count());
    }

    public function test_mixed_units_stay_separate_and_small_decimals_are_retained(): void
    {
        // Bind the presenter only; no application boot, migrations or database access.
        $container=new \Illuminate\Container\Container;
        $container->instance(InfographicPresentationService::class,new InfographicPresentationService);
        $previous=\Illuminate\Container\Container::getInstance();
        \Illuminate\Container\Container::setInstance($container);
        try {
            $dataset=$this->dataset('5.57',[
                ['nama'=>'jenis','label'=>'Jenis','tipe'=>'string','is_dimension'=>true],
                ['nama'=>'nilai','label'=>'Nilai','tipe'=>'decimal'],
                ['nama'=>'satuan','label'=>'Satuan','tipe'=>'string'],
            ],['chart_dimensions'=>['jenis'],'chart_unit_field'=>'satuan']);
            $rows=collect([
                new DatasetValue(['tahun'=>2024,'values'=>['jenis'=>'Rasio jaringan irigasi','nilai'=>0.82034,'satuan'=>'persen']]),
                new DatasetValue(['tahun'=>2024,'values'=>['jenis'=>'Luas irigasi','nilai'=>224.24,'satuan'=>'hektare']]),
                new DatasetValue(['tahun'=>2024,'values'=>['jenis'=>'Belum diketahui','nilai'=>null,'satuan'=>'hektare']]),
            ]);
            $charts=(new DynamicChartService)->statisticsForDataset($dataset,$rows);
            self::assertSame(['persen','hektare'],array_column($charts,'unit'));
            self::assertSame(0.82034,$charts[0]['values'][0]);
            self::assertSame([224.24],$charts[1]['values']);
            self::assertSame('Rasio jaringan irigasi',$charts[0]['title']);
            $nextYear=new DatasetValue(['tahun'=>2025,'values'=>['jenis'=>'Luas irigasi','nilai'=>100,'satuan'=>'hektare']]);
            $acrossYears=(new DynamicChartService)->statisticsForDataset($dataset,$rows->concat([$nextYear]));
            self::assertCount(3,$acrossYears);
            self::assertSame([[2024],[2024],[2025]],array_map(fn($chart)=>array_map('intval',$chart['years']),$acrossYears));
            self::assertSame([100.0],$acrossYears[2]['values']);
        } finally {
            \Illuminate\Container\Container::setInstance($previous);
        }
    }

    public function test_number_format_and_unknown_units_are_explicit(): void
    {
        self::assertSame('0,82034',InfographicPresentationService::number(0.82034));
        self::assertSame('1.714',InfographicPresentationService::number(1714));
        $dataset=$this->dataset('TEST',[]);
        $charts=(new InfographicPresentationService)->describeCharts($dataset,collect(),[['title'=>'Nilai','unit'=>'Numerik','labels'=>['Rincian A'],'values'=>[7],'type'=>'bar']]);
        self::assertSame('Rincian A',$charts[0]['title']);
        self::assertSame('',$charts[0]['unit']);
        self::assertStringContainsString('Satuan belum dicantumkan',$charts[0]['caution']);
    }
}
