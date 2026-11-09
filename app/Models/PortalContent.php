<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalContent extends Model
{
    protected $table = 'portal_contents';

    protected $fillable = [
        'type', 'title', 'slug', 'summary', 'body', 'category', 'image_path', 'video_path',
        'year', 'sort_order', 'is_published', 'published_at', 'created_by', 'updated_by',
        'abbreviation', 'address', 'phone', 'contact_email', 'website',
        'abbreviation', 'address', 'phone', 'contact_email', 'website',
    ];

    protected $casts = [
        'year' => 'integer',
        'sort_order' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
