<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\PublicCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GalleriesController extends Controller
{
    public function index(PublicCatalogService $catalog): Response
    {
        return Inertia::render('Galleries', [
            'categories' => $catalog->categories(),
            'category' => null,
            'images' => [],
            'pagination' => ['page' => 1, 'perPage' => 6, 'total' => 0, 'hasMore' => false, 'nextPage' => null],
        ]);
    }

    public function show(Request $request, Category $category, PublicCatalogService $catalog): Response
    {
        $gallery = $catalog->portfolioImages($category, max(1, (int) $request->query('page', 1)));

        return Inertia::render('Galleries', [
            'categories' => $catalog->categories(),
            'category' => $catalog->category($category),
            'images' => $gallery['images'],
            'pagination' => $gallery['pagination'],
        ]);
    }
}
