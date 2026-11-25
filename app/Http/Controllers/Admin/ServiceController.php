<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Services\CreateServiceAction;
use App\Actions\Services\DuplicateServiceAction;
use App\Actions\Services\ToggleServiceActiveAction;
use App\Actions\Services\UpdateServiceAction;
use App\DTOs\Services\SaveServiceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Services\AdminCatalogService;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(AdminCatalogService $catalog): Response
    {
        return Inertia::render('AdminServices', [
            'services' => $catalog->services(),
        ]);
    }

    public function store(ServiceRequest $request, CreateServiceAction $action): RedirectResponse
    {
        return $this->flash($action->execute(SaveServiceDTO::fromRequest($request)));
    }

    public function update(ServiceRequest $request, Service $service, UpdateServiceAction $action): RedirectResponse
    {
        return $this->flash($action->execute($service, SaveServiceDTO::fromRequest($request)));
    }

    public function duplicate(Service $service, DuplicateServiceAction $action): RedirectResponse
    {
        return $this->flash($action->execute($service));
    }

    public function toggle(Service $service, ToggleServiceActiveAction $action): RedirectResponse
    {
        return $this->flash($action->execute($service));
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
