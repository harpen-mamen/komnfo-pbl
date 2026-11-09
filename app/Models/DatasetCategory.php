<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatasetCategory extends Model
{
    use HasFactory;

    protected $table = "dataset_categories";

    protected $fillable = [
        "kode",
        "nama",
        "kelompok",
        "urutan",
        "deskripsi",
        "aktif",
    ];

    protected $casts = [
        "aktif" => "boolean",
        "urutan" => "integer",
    ];

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class, "category_id");
    }

    public static function kelompokOptions(): array
    {
        return [
            "pelayanan_dasar" => "Pelayanan Dasar",
            "non_pelayanan_dasar" => "Wajib Non-Pelayanan Dasar",
            "urusan_pilihan" => "Urusan Pilihan",
        ];
    }
}
