<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Policies\DemandePolicy;
use App\Policies\PaymentPolicy;
use App\Models\Demande;
use App\Models\Payment;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Demande::class => DemandePolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
