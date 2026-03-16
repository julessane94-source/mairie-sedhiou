<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticationController;

// Routes d'authentification avec rate limiting
Route::middleware('guest')->group(function () {
    // Affichage du formulaire de connexion
    Route::get('/login', [AuthenticationController::class, 'showLoginForm'])
        ->name('login');

    // Soumission du formulaire de connexion - Throttle: 5 tentatives par minute
    Route::post('/login', [AuthenticationController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Affichage du formulaire d'inscription
    Route::get('/register', [AuthenticationController::class, 'showRegisterForm'])
        ->name('register');

    // Soumission du formulaire d'inscription - Throttle: 3 inscriptions par minute
    Route::post('/register', [AuthenticationController::class, 'register'])
        ->middleware('throttle:3,1')
        ->name('register.post');
});

// Déconnexion - Accessible aux utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout'])
        ->name('logout');
});
