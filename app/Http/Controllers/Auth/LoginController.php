<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\LogoutUserAction;
use App\DTOs\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request, AuthenticateUserAction $action): RedirectResponse
    {
        $result = $action->execute(LoginDTO::fromRequest($request), $request);

        if (! $result->success) {
            return back()
                ->withErrors(['email' => $result->message])
                ->onlyInput('email');
        }

        return redirect()
            ->intended(route('admin.dashboard'))
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function destroy(Request $request, LogoutUserAction $action): RedirectResponse
    {
        $result = $action->execute($request);

        return redirect()->route('login')
            ->with('status', $result->message)
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
