<?php

namespace App\Actions\Auth;

use App\Support\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUserAction
{
    public function execute(Request $request): Result
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Result::ok(null, 'You have been signed out.');
    }
}
