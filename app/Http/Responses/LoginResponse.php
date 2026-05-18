<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->hasRole('Fakultas')) {
            return redirect()->intended('/pelaksanaan/evaluasi-diri');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
