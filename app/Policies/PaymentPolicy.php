<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        // Le citoyen peut voir ses propres paiements
        return $user->id === $payment->citoyen_id;
    }

    public function update(User $user, Payment $payment): bool
    {
        // Seul le citoyen qui a créé le paiement peut le modifier
        return $user->id === $payment->citoyen_id && !$payment->isPaid();
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Seul l'admin peut supprimer
        return $user->isAdmin();
    }
}
