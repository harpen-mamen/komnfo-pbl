<?php

namespace Tests\Unit;

use App\Models\Dataset;
use App\Services\DataInput\FieldAutoCalculator;
use App\Services\DataInput\RoadInputDefinition;
use PHPUnit\Framework\TestCase;

class OpdInputContractTest extends TestCase
{
    public function test_reported_total_is_not_overwritten(): void
    {
        $values=['kk_laki'=>5000,'kk_perempuan'=>1035,'total'=>6038];
        self::assertSame($values,FieldAutoCalculator::apply(new Dataset(['kode'=>'3.12']),$values));
    }

    public function test_missing_outputs_are_calculated_only_from_complete_inputs(): void
    {
        $dataset=new Dataset(['kode'=>'3.13']);
        self::assertSame(7,FieldAutoCalculator::apply($dataset,['penduduk_laki'=>3,'penduduk_perempuan'=>4])['total']);
        self::assertSame(0,FieldAutoCalculator::apply($dataset,['penduduk_laki'=>0,'penduduk_perempuan'=>0])['total']);
        self::assertArrayNotHasKey('total',FieldAutoCalculator::apply($dataset,['penduduk_laki'=>3]));
        self::assertSame(['total'],FieldAutoCalculator::getAutoCalcFields('3.13'));
    }

    public function test_reported_fish_value_is_retained_even_when_price_is_missing(): void
    {
        $dataset=new Dataset(['kode'=>'4.47']);
        $values=['volume_kg'=>2605.36,'harga_rata_rp_kg'=>45000,'nilai_produksi_rp'=>117241109.51];
        self::assertSame($values,FieldAutoCalculator::apply($dataset,$values));
        $values['harga_rata_rp_kg']=null;
        self::assertSame($values,FieldAutoCalculator::apply($dataset,$values));
    }

    public function test_road_choice_generates_consistent_description_and_unit(): void
    {
        $values=RoadInputDefinition::normalize(['indikator_jalan'=>'jumlah_jembatan','nilai'=>5,'satuan'=>'km']);
        self::assertSame('unit',$values['satuan']);
        self::assertSame('Jumlah jembatan (unit)',$values['uraian']);
        self::assertSame('Jembatan',$values['kategori']);
        $legacy=RoadInputDefinition::normalize(['kategori'=>'Jembatan','uraian'=>'Panjang jembatan (m)','nilai'=>1714]);
        self::assertSame('panjang_jembatan',$legacy['indikator_jalan']);
        self::assertSame(1714,$legacy['nilai']);
    }
}
