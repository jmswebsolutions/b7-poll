<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(StoreRegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->only('name', 'email', 'password'));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('polls.index');
    }
}
