<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfile;
use App\Http\Middleware\BupatiDashboardOnly;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('SIPANDU')
            ->brandLogo(new HtmlString('<span class="sipandu-brand"><img src="/logo.png" alt="Logo SIPANDU"><span class="sipandu-brand__name">Sangihe</span></span>'))
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                Action::make('profile')
                    ->label('Profil Saya')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->visible(fn (): bool => ! auth()->user()?->hasAnyRole(['admin_bupati', 'admin_portal'])),
            ])
            ->colors([
                'primary' => Color::hex('#23625f'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigation(function (): bool | NavigationBuilder {
                if (auth()->user()?->hasRole('admin_portal')) {
                    $resource = \App\Filament\Resources\PortalContentResource::class;
                    $listUrl = fn (array $filters): string => $resource::getUrl('index', ['filters' => $filters]);
                    $section = fn (string $label, string $icon, array $filters, string $type, ?string $slug = null): NavigationItem => NavigationItem::make($label)
                        ->group('Website Publik')
                        ->icon($icon)
                        ->url(fn (): string => $listUrl($filters))
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.resources.portal-contents.index')
                            && request()->query('filters.type.value') === $type
                            && ($slug === null || request()->query('filters.slug.value') === $slug));

                    return (new NavigationBuilder)->items([
                        $section('Beranda', 'heroicon-o-home', ['type' => ['value' => 'hero']], 'hero'),
                        $section('Berita', 'heroicon-o-newspaper', ['type' => ['value' => 'news']], 'news'),
                        $section('Layanan Publik', 'heroicon-o-building-library', ['type' => ['value' => 'service']], 'service'),
                        $section('Sambutan Bupati', 'heroicon-o-chat-bubble-left-right', ['type' => ['value' => 'page'], 'slug' => ['value' => 'sambutan-bupati']], 'page', 'sambutan-bupati'),
                        $section('Sejarah Sangihe', 'heroicon-o-book-open', ['type' => ['value' => 'history']], 'history'),
                        $section('Visi & Misi', 'heroicon-o-flag', ['type' => ['value' => 'vision']], 'vision'),
                        $section('Profil Pejabat', 'heroicon-o-user-group', ['type' => ['value' => 'official']], 'official'),
                        $section('Dinas & OPD', 'heroicon-o-building-office-2', ['type' => ['value' => 'opd_profile']], 'opd_profile'),
                        $section('Geografi', 'heroicon-o-map', ['type' => ['value' => 'page'], 'slug' => ['value' => 'geografi']], 'page', 'geografi'),
                        $section('Lambang Daerah', 'heroicon-o-shield-check', ['type' => ['value' => 'page'], 'slug' => ['value' => 'lambang-daerah']], 'page', 'lambang-daerah'),
                        $section('Halaman Tentang Daerah', 'heroicon-o-information-circle', ['type' => ['value' => 'page'], 'slug' => ['value' => 'tentang-daerah']], 'page', 'tentang-daerah'),
                    ]);
                }

                if (! auth()->user()?->hasRole('admin_bupati')) {
                    return true;
                }

                return (new NavigationBuilder)
                    ->items([
                        ...\App\Filament\Pages\ExecutiveDashboard::getNavigationItems(),
                        ...\App\Filament\Pages\RpjmdMonitoring::getNavigationItems(),
                        ...\App\Filament\Pages\ApbdMonitoring::getNavigationItems(),
                        ...\App\Filament\Pages\InfrastrukturDigitalMonitoring::getNavigationItems(),
                        ...\App\Filament\Pages\KecamatanKampungMonitoring::getNavigationItems(),
                        ...\App\Filament\Pages\PelaporanMonitoring::getNavigationItems(),
                    ]);
            })
            ->theme(asset('css/filament/admin.css').'?v='.(is_file(public_path('css/filament/admin.css')) ? filemtime(public_path('css/filament/admin.css')) : '1'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                BupatiDashboardOnly::class,
                \App\Http\Middleware\PortalAdminOnly::class,
            ]);
    }
}
