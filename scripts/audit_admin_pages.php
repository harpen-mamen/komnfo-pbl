<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$config = config('database.connections.mysql');
$config['database'] = 'kominfo_recovery_preview_20261025'; $config['url'] = null;
config(['database.connections.recovery_preview'=>$config]);
Illuminate\Support\Facades\DB::setDefaultConnection(in_array('--active', $argv, true) ? 'mysql' : 'recovery_preview');
$base = App\Models\User::where('email','kominfo@sipdt.test')->firstOrFail();
$failed = [];
$codes = [];
$navigation = [];
foreach (['admin_kominfo','super_admin','admin_opd'] as $role) {
    $user = clone $base;
    $user->opd_id = App\Models\Opd::where('kode','DINKES')->value('id');
    $user->setRelation('roles', new Illuminate\Database\Eloquent\Collection([new Spatie\Permission\Models\Role(['name'=>$role,'guard_name'=>'web'])]));
    Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
    $paths = [];
    foreach (app('router')->getRoutes() as $route) {
        if (! in_array('GET', $route->methods(), true)) continue;
        $uri = $route->uri();
        if (! str_starts_with($uri, 'admin') || str_contains($uri, '{')) continue;
        $paths[] = '/'.$uri;
    }
    if ($role === 'admin_kominfo') {
        foreach (App\Models\Opd::where('aktif',true)->pluck('id') as $id) {
            $paths[]='/admin/data-opd/'.$id;
            $paths[]='/admin/infografis-opd/'.$id;
        }
    }
    foreach (array_unique($paths) as $path) {
        $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create($path,'GET'));
        $status = $response->getStatusCode();
        $codes[$role][$path] = $status;
        if ($status >= 500) {
            preg_match('/<title>(.*?)<\/title>/s',$response->getContent(),$match);
            $failed[] = [$role,$path,$status,html_entity_decode(strip_tags($match[1]??'Unknown'))];
            echo json_encode(end($failed),JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
}
echo json_encode(['results'=>$codes,'failures'=>count($failed)],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
Illuminate\Support\Facades\File::put(storage_path('app/source-imports/2026-10-25/admin-page-audit'.(in_array('--active', $argv, true) ? '-active' : '').'.json'), json_encode(['results'=>$codes,'errors'=>$failed],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
exit($failed ? 1 : 0);
