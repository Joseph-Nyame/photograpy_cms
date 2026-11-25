<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, AdminSearchService $search): JsonResponse
    {
        $term = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ])['q'];

        return response()->json([
            'groups' => $search->search($term),
        ]);
    }
}
