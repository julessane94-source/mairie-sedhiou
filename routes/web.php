<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Citoyen;
use App\Http\Controllers\Agent;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification (Laravel Breeze ou Fortify)
require __DIR__.'/auth.php';

// ============================================
// ESPACE ADMIN
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class . '@index')->name('dashboard');
    
    // Gestion des utilisateurs
    Route::resource('utilisateurs', Admin\UtilisateurController::class);
    Route::patch('utilisateurs/{user}/role', Admin\UtilisateurController::class . '@changerRole')->name('utilisateurs.changerRole');
    Route::patch('utilisateurs/{user}/statut', Admin\UtilisateurController::class . '@changerStatut')->name('utilisateurs.changerStatut');
    
    // Gestion des demandes
    Route::resource('demandes', Admin\DemandeController::class)->only(['index', 'show']);
});

// ============================================
// ESPACE CITOYEN
// ============================================
Route::middleware(['auth', 'role:citoyen'])->prefix('citoyen')->name('citoyen.')->group(function () {
    Route::get('/dashboard', Citoyen\DashboardController::class . '@index')->name('dashboard');
    
    // Gestion des demandes
    Route::resource('demandes', Citoyen\DemandeController::class);
    
    // Gestion du profil
    Route::get('/profil/edit', Citoyen\ProfilController::class . '@edit')->name('profil.edit');
    Route::patch('/profil', Citoyen\ProfilController::class . '@update')->name('profil.update');
    
    // Messages
    Route::post('/demandes/{demande}/messages', Citoyen\MessageController::class . '@store')->name('messages.store');
    
    // Paiements
    Route::get('/paiements', Citoyen\PaymentController::class . '@index')->name('payments.index');
    Route::get('/demandes/{demande}/paiement/creer', Citoyen\PaymentController::class . '@create')->name('payments.create');
    Route::post('/demandes/{demande}/paiement', Citoyen\PaymentController::class . '@store')->name('payments.store');
    Route::get('/paiements/{payment}', Citoyen\PaymentController::class . '@show')->name('payments.show');
    Route::post('/paiements/{payment}/marquer-paye', Citoyen\PaymentController::class . '@markAsPaid')->name('payments.markAsPaid');
    Route::post('/paiements/{payment}/annuler', Citoyen\PaymentController::class . '@cancel')->name('payments.cancel');
    Route::get('/paiements/{payment}/recu/telechargement', Citoyen\PaymentController::class . '@downloadReceipt')->name('payments.receipt.download');
    Route::get('/paiements/{payment}/recu/apercu', Citoyen\PaymentController::class . '@previewReceipt')->name('payments.receipt.preview');
});

// ============================================
// ESPACE AGENT
// ============================================
Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', Agent\DashboardController::class . '@index')->name('dashboard');
    
    // Gestion des demandes
    Route::resource('demandes', Agent\DemandeController::class)->only(['index', 'show']);
    Route::post('demandes/{demande}/assigner', Agent\DemandeController::class . '@assigner')->name('demandes.assigner');
    Route::post('demandes/{demande}/accepter', Agent\DemandeController::class . '@accepter')->name('demandes.accepter');
    Route::post('demandes/{demande}/rejeter', Agent\DemandeController::class . '@rejeter')->name('demandes.rejeter');
    
    // Messages
    Route::post('/demandes/{demande}/messages', Agent\MessageController::class . '@store')->name('messages.store');
});
