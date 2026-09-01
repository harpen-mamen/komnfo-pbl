<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExportAnalyticsRoutesTest extends TestCase
{
    public function test_export_analytics_download_routes_are_registered(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('filament.pages.export-analitik.export-penduduk-csv'));
        $this->assertNotNull(Route::getRoutes()->getByName('filament.pages.export-analitik.export-statistics-json'));
    }
}
