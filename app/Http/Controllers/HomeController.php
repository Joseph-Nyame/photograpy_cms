<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalogService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(PublicCatalogService $catalog): Response
    {
        return Inertia::render('Home', [
            'categories' => $catalog->categories(),
            'services' => $catalog->services(),
        ]);
    }
}
