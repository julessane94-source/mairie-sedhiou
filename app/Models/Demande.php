<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\DemandeType;
use App\Enums\DemandeStatus;

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
        'documents_requis',
        'frais_estimes',
        'delai_traitement_estime',
    ];

    protected $casts = [
        'date_limite' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'documents_requis' => 'array',
        'frais_estimes' => 'integer',
        'delai_traitement_estime' => 'integer',
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

    /**
     * Retourne le type de demande sous forme d'enum
     */
    public function getTypeEnum(): ?DemandeType
    {
        return DemandeType::tryFrom($this->type);
    }

    /**
     * Retourne le statut sous forme d'enum
     */
    public function getStatutEnum(): ?DemandeStatus
    {
        return DemandeStatus::tryFrom($this->statut);
    }

    /**
     * Vérifie si la demande est acceptée
     */
    public function isAccepte(): bool
    {
        return $this->statut === DemandeStatus::ACCEPTEE->value;
    }

    /**
     * Vérifie si la demande est rejetée
     */
    public function isRejetee(): bool
    {
        return $this->statut === DemandeStatus::REJETEE->value;
    }

    /**
     * Vérifie si la demande est en attente
     */
    public function isPendante(): bool
    {
        return $this->statut === DemandeStatus::PENDANTE->value;
    }

    /**
     * Vérifie si la demande est en cours
     */
    public function isEnCours(): bool
    {
        return $this->statut === DemandeStatus::EN_COURS->value;
    }

    /**
     * Calcule la date limite si non définie
     */
    public function calculerDateLimite(): void
    {
        if (!$this->date_limite && $this->getTypeEnum()) {
            $delai = $this->getTypeEnum()->delaiTraitement();
            $this->date_limite = now()->addDays($delai);
            $this->save();
        }
    }

    /**
     * Initialise les informations du type de demande
     */
    public function initialiserTypeDemande(): void
    {
        $typeEnum = $this->getTypeEnum();
        if ($typeEnum) {
            $this->documents_requis = $typeEnum->documentsRequis();
            $this->frais_estimes = $typeEnum->frais();
            $this->delai_traitement_estime = $typeEnum->delaiTraitement();
            $this->save();
        }
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, DemandeType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeOfStatut($query, DemandeStatus $statut)
    {
        return $query->where('statut', $statut->value);
    }

    /**
     * Scope pour les demandes urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('priorite', 'urgente');
    }

    /**
     * Scope pour les demandes en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_limite', '<', now())
                    ->where('statut', '!=', DemandeStatus::ACCEPTEE->value)
                    ->where('statut', '!=', DemandeStatus::REJETEE->value);
    }
}
