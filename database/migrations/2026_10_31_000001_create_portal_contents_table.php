<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 32)->index();
            $table->string('title', 200);
            $table->string('slug', 220)->nullable()->index();
            $table->string('summary', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('category', 80)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'is_published', 'sort_order'], 'portal_contents_public_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_contents');
    }
};
