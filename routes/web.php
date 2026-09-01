<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (API-Only Mode)
|--------------------------------------------------------------------------
|
| This application is now an API-only backend serving:
|   - Next.js frontend (public web portal)        -> /api/v1/public/*
|   - Flutter mobile app (Citizen data collector)  -> /api/v1/mobile/*
|   - Filament admin panel (Kominfo dashboard)     -> /admin/*
|
| Filament automatically registers the /admin/* routes via its service
| provider. This file is intentionally minimal so Laravel's router does
| not throw "web routes are empty" warnings.
|
*/

// Health-check used by uptime monitors and the Next.js frontend.
Route::get('/up', fn () => response()->json([
    'status' => 'ok',
    'service' => 'sipdt-api',
    'time' => now()->toIso8601String(),
]))->name('health');

// A simple root redirect so any stray browser visit gets a friendly hint.
Route::get('/', fn () => response()->json([
    'message' => 'SIPDT Kominfo Kepulauan Sangihe API',
    'docs' => '/api/v1',
    'admin' => '/admin',
]))->name('root');
