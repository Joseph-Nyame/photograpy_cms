<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Clients\CreateClientAction;
use App\Actions\Clients\ToggleClientSubscriptionAction;
use App\DTOs\Clients\CreateClientDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Models\Client;
use App\Services\AdminClientService;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request, AdminClientService $clients): Response
    {
        return Inertia::render('AdminClients', [
            'clients' => $clients->clients(),
            'initialQuery' => $request->string('q')->toString(),
        ]);
    }

    public function store(StoreClientRequest $request, CreateClientAction $action): RedirectResponse
    {
        return $this->flash($action->execute(CreateClientDTO::fromRequest($request)));
    }

    public function toggleSubscription(Client $client, ToggleClientSubscriptionAction $action): RedirectResponse
    {
        return $this->flash($action->execute($client));
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
