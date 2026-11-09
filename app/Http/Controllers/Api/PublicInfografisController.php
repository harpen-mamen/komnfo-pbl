<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PublicDatasetCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicInfografisController extends Controller
{
    public function index(Request $request, PublicDatasetCatalogService $catalog): JsonResponse
    {
        $items = collect($catalog->all());
        if ($category = $request->query('kategori')) {
            $items = $items->filter(fn (array $item) => strcasecmp($item['category'], $category) === 0);
        }

        return response()->json(['success' => true, 'data' => $items->values()]);
    }

    public function show(string $slug, PublicDatasetCatalogService $catalog): JsonResponse
    {
        $item = $catalog->find($slug);
        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Infografis tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function categories(PublicDatasetCatalogService $catalog): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $catalog->categories()]);
    }
}
