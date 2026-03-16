<?php

use Illuminate\Support\Facades\Route;

// Routes d'authentification simples
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            return redirect()->intended(route(auth()->user()->role . '.dashboard'));
        }

        return back()->withErrors(['email' => 'Identifiants invalides'])->onlyInput('email');
    })->name('login.post');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);

        $user->profil()->create([]);

        auth()->login($user);

        return redirect()->route('citoyen.dashboard');
    })->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});
