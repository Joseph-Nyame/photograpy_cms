<?php

namespace App\Http\Controllers;

use App\Actions\Contact\CreateContactMessageAction;
use App\DTOs\Contact\CreateContactMessageDTO;
use App\Http\Requests\Contact\CreateContactMessageRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Contact');
    }

    public function store(CreateContactMessageRequest $request, CreateContactMessageAction $action): RedirectResponse
    {
        $result = $action->execute(CreateContactMessageDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
