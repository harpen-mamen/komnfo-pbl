<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Kominfo Admins ===\n";
$admins = User::role('admin')->get(['id','name','email']);
if ($admins->isEmpty()) {
    echo "No users with role 'admin' found.\n";
} else {
    foreach ($admins as $a) {
        echo "#{$a->id} {$a->name} <{$a->email}>\n";
    }
}

echo "\n=== Notifications table summary ===\n";
$count = DB::table('notifications')->count();
echo "Total notifications: $count\n";

$rows = DB::table('notifications')->orderByDesc('created_at')->limit(20)->get();
if ($rows->isEmpty()) {
    echo "No notifications found.\n";
} else {
    foreach ($rows as $r) {
        echo "[{$r->id}] {$r->type} | notifiable_id={$r->notifiable_id} | created_at={$r->created_at}\n";
        $data = json_decode($r->data, true);
        if ($data) {
            $title = $data['title'] ?? ($data['message'] ?? null);
            if ($title) {
                echo "    -> " . preg_replace('/\s+/', ' ', trim($title)) . "\n";
            }
        }
    }
}

echo "\n=== Done ===\n";
