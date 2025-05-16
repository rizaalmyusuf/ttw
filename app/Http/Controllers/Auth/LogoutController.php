<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;

class LogoutController extends Controller
{
    public function __invoke(): LogoutResponse
    {
        $user = User::find(auth()->guard()->user()->id);
        $user->status = 0;
        $user->save();

        Filament::auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}
