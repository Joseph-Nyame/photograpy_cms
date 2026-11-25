<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Settings\UpdateSettingsAction;
use App\DTOs\Settings\UpdateSettingsDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(SettingsService $settings): Response
    {
        return Inertia::render('AdminSettings', [
            'settings' => $settings->form(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action): RedirectResponse
    {
        $result = $action->execute(UpdateSettingsDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
