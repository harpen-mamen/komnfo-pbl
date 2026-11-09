<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->hasRole('admin_portal')) {
            return $next($request);
        }

        if ($request->isMethod('POST') && str_ends_with($request->path(), '/logout')) {
            return $next($request);
        }

        if ($request->isMethod('GET') && $request->is('admin/portal-contents') && ! $request->query('filters')) {
            return redirect()->to('/admin/portal-contents?filters%5Btype%5D%5Bvalue%5D=hero');
        }

        if ($request->is('admin/portal-contents') || $request->is('admin/portal-contents/*')
            || $request->is('livewire/*') || $request->is('admin/livewire/*')) {
            return $next($request);
        }

        if ($request->isMethod('GET') && $request->is('admin')) {
            return redirect()->to('/admin/portal-contents?filters%5Btype%5D%5Bvalue%5D=hero');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Akun Admin Portal hanya dapat mengelola konten website publik.'], Response::HTTP_FORBIDDEN);
        }

        abort(Response::HTTP_FORBIDDEN, 'Akun Admin Portal hanya dapat mengelola konten website publik.');
    }
}
