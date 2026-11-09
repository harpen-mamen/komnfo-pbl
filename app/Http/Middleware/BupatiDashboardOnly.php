<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BupatiDashboardOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->hasRole('admin_bupati')) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        $leadershipPages = [
            'filament.admin.pages.executive-dashboard',
            'filament.admin.pages.rpjmd-monitoring',
            'filament.admin.pages.apbd-monitoring',
            'filament.admin.pages.infrastruktur-digital',
            'filament.admin.pages.kecamatan-kampung',
            'filament.admin.pages.pelaporan-pimpinan',
        ];
        $isLeadershipPage = in_array($routeName, $leadershipPages, true);
        $isLivewireUpdate = str_ends_with($routeName, 'livewire.update');
        $isLogout = $request->isMethod('POST') && str_ends_with($request->path(), '/logout');

        if ($isLeadershipPage || $isLivewireUpdate || $isLogout) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Akun Admin Bupati hanya memiliki akses ke halaman Monitoring Pimpinan.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->isMethod('GET')) {
            return redirect()->route('filament.admin.pages.executive-dashboard');
        }

        abort(Response::HTTP_FORBIDDEN, 'Akun Admin Bupati hanya memiliki akses ke halaman Monitoring Pimpinan.');
    }
}
