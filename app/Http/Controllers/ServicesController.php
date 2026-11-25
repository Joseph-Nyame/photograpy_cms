<?php

namespace App\Http\Controllers;

use App\Services\PublicCatalogService;
use Inertia\Inertia;
use Inertia\Response;

class ServicesController extends Controller
{
    public function __invoke(PublicCatalogService $catalog): Response
    {
        return Inertia::render('Services', [
            'services' => $catalog->services(),
        ]);
    }
}
