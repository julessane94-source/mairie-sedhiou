<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'citoyen_id',
        'montant',
        'devise',
        'methode_paiement',
        'statut',
        'numero_transaction',
        'date_paiement',
        'reference_recu',
        'description',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class);
    }

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citoyen_id');
    }

    public function isPaid(): bool
    {
        return $this->statut === 'paid';
    }

    public function isPending(): bool
    {
        return $this->statut === 'pending';
    }
}
