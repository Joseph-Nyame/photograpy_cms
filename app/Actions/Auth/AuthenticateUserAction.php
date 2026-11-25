<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Support\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserAction
{
    public function execute(LoginDTO $dto, Request $request): Result
    {
        if (! Auth::attempt(['email' => $dto->email, 'password' => $dto->password], $dto->remember)) {
            return Result::fail('Those credentials do not match our records.');
        }

        $request->session()->regenerate();

        return Result::ok(Auth::user(), 'Welcome back.');
    }
}
