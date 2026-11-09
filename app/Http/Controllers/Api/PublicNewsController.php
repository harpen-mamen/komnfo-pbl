<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortalContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicNewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('portal_contents')) {
            return response()->json(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 0]]);
        }

        $query = PortalContent::query()->where('type', 'news')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
        if ($category = $request->query('kategori')) $query->where('category', $category);
        $perPage = max(1, min((int) $request->query('per_page', 10), 50));
        $page = $query->latest('published_at')->paginate($perPage);

        return response()->json([
            'data' => $page->getCollection()->map(fn (PortalContent $item): array => $this->present($item)),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        if (! Schema::hasTable('portal_contents')) return response()->json(['message' => 'Berita belum tersedia.'], 404);
        $item = PortalContent::query()->where('type', 'news')->where('slug', $slug)
            ->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->firstOrFail();
        return response()->json(['data' => $this->present($item, true)]);
    }

    private function present(PortalContent $item, bool $includeBody = false): array
    {
        return array_filter([
            'id' => $item->id,
            'judul' => $item->title,
            'slug' => $item->slug,
            'ringkasan' => $item->summary,
            'isi' => $includeBody ? $item->body : null,
            'gambar' => $item->image_path
                ? (str_starts_with($item->image_path, 'https://') || str_starts_with($item->image_path, 'http://')
                    ? $item->image_path
                    : '/media/' . ltrim($item->image_path, '/'))
                : null,
            'kategori' => $item->category,
            'terbit_at' => $item->published_at?->toIso8601String(),
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ], fn ($value) => $value !== null);
    }
}
