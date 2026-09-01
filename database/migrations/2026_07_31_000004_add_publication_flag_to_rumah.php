<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('rumah', function(Blueprint $t){$t->boolean('publik')->default(false)->after('keterangan');}); } public function down(): void { Schema::table('rumah', fn(Blueprint $t)=>$t->dropColumn('publik')); } };
