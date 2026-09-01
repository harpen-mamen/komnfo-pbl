<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicNewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PublicNews::query()
            ->where('terbit', true)
            ->latest('terbit_at');

        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        $news = $query->paginate(min((int) $request->query('per_page', 10), 50));

        return response()->json($news);
    }

    public function show(string $slug): JsonResponse
    {
        $news = PublicNews::query()
            ->where('slug', $slug)
            ->where('terbit', true)
            ->firstOrFail();

        return response()->json(['data' => $news]);
    }
}
