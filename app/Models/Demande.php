<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'citoyen_id',
        'titre',
        'description',
        'type',
        'statut',
        'priorite',
        'agent_assigne_id',
        'date_limite',
        'motif_rejet',
    ];

    protected $casts = [
        'date_limite' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function citoyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citoyen_id');
    }

    public function agentAssigne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_assigne_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAccepte(): bool
    {
        return $this->statut === 'acceptee';
    }

    public function isRejetee(): bool
    {
        return $this->statut === 'rejetee';
    }

    public function isPendante(): bool
    {
        return $this->statut === 'pendante';
    }
}
