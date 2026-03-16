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
    // Dashboard avec statistiques & diagnostics
    Route::get('/dashboard', Admin\DashboardController::class . '@index')->name('dashboard');
    
    // ============ GESTION DES AGENTS ============
    Route::resource('agents', Admin\AgentController::class);
    Route::post('agents/{user}/assigner-demande', Admin\AgentController::class . '@assignerDemande')->name('agents.assigner-demande');
    Route::post('agents/{user}/retirer-demande', Admin\AgentController::class . '@deasigner')->name('agents.deasigner');
    Route::patch('agents/{user}/statut', Admin\AgentController::class . '@changerStatut')->name('agents.changerStatut');
    
    // ============ POINTAGE DES AGENTS ============
    Route::prefix('pointage')->name('pointage.')->group(function () {
        Route::get('/', Admin\AttendanceController::class . '@index')->name('index');
        Route::get('/{agent}', Admin\AttendanceController::class . '@show')->name('show');
        Route::post('/{agent}/presence', Admin\AttendanceController::class . '@marquerPresence')->name('marquer');
        Route::post('/{agent}/checkin', Admin\AttendanceController::class . '@checkIn')->name('checkin');
        Route::post('/{agent}/checkout', Admin\AttendanceController::class . '@checkOut')->name('checkout');
        Route::post('/{attendance}/justifier', Admin\AttendanceController::class . '@justifierAbsence')->name('justifier');
        Route::get('/rapport', Admin\AttendanceController::class . '@rapport')->name('rapport');
    });
    
    // ============ PARAMÈTRES DE PLATEFORME ============
    Route::prefix('parametres')->name('settings.')->group(function () {
        Route::get('/', Admin\SettingsController::class . '@index')->name('index');
        Route::patch('/{cle}', Admin\SettingsController::class . '@update')->name('update');
        
        Route::get('/application', Admin\SettingsController::class . '@application')->name('application');
        Route::post('/application', Admin\SettingsController::class . '@updateApplication')->name('application.update');
        
        Route::get('/operations', Admin\SettingsController::class . '@operations')->name('operations');
        Route::post('/operations', Admin\SettingsController::class . '@updateOperations')->name('operations.update');
        
        Route::get('/securite', Admin\SettingsController::class . '@security')->name('security');
        Route::post('/securite', Admin\SettingsController::class . '@updateSecurity')->name('security.update');
        
        Route::get('/notifications', Admin\SettingsController::class . '@notifications')->name('notifications');
        Route::post('/notifications', Admin\SettingsController::class . '@updateNotifications')->name('notifications.update');
        
        Route::get('/logs', Admin\SettingsController::class . '@logs')->name('logs');
        Route::post('/logs/effacer', Admin\SettingsController::class . '@clearLogs')->name('logs.clear');
        
        Route::post('/backup', Admin\SettingsController::class . '@backup')->name('backup');
    });
    
    // ============ GESTION DES UTILISATEURS ============
    Route::resource('utilisateurs', Admin\UtilisateurController::class);
    Route::patch('utilisateurs/{user}/role', Admin\UtilisateurController::class . '@changerRole')->name('utilisateurs.changerRole');
    Route::patch('utilisateurs/{user}/statut', Admin\UtilisateurController::class . '@changerStatut')->name('utilisateurs.changerStatut');
    
    // ============ GESTION DES DEMANDES ============
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
