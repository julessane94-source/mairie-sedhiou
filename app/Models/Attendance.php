<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'agent_id',
        'date_presence',
        'heure_debut',
        'heure_fin',
        'statut',
        'heures_travaillees',
        'notes',
        'justificatif'
    ];

    protected $casts = [
        'date_presence' => 'date',
        'heure_debut' => 'time',
        'heure_fin' => 'time',
    ];

    /**
     * L'agent concerné par la présence
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Statuts disponibles
     */
    public static function getStatuses(): array
    {
        return ['present', 'absent', 'congé', 'retard', 'repos'];
    }

    /**
     * Vérifier si l'agent était présent
     */
    public function isPresent(): bool
    {
        return $this->statut === 'present';
    }

    /**
     * Vérifier si l'absence est justifiée
     */
    public function isJustified(): bool
    {
        return !is_null($this->justificatif) || $this->statut === 'congé';
    }

    /**
     * Calculer les heures travaillées
     */
    public function calculateWorkingHours(): float
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }

        $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->heure_debut);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->heure_fin);

        return $end->diffInHours($start);
    }

    /**
     * Scopes
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date_presence', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date_presence', $year)
                     ->whereMonth('date_presence', $month);
    }

    public function scopePresent($query)
    {
        return $query->where('statut', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('statut', 'absent');
    }

    public function scopeJustified($query)
    {
        return $query->whereNotNull('justificatif')->orWhere('statut', 'congé');
    }
}
