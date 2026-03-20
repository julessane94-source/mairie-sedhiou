<?php

namespace App\Enums;

enum DemandeStatus: string
{
    case PENDANTE = 'pendante';
    case EN_COURS = 'en_cours';
    case ACCEPTEE = 'acceptee';
    case REJETEE = 'rejetee';

    /**
     * Retourne le label français du statut
     */
    public function label(): string
    {
        return match($this) {
            self::PENDANTE => 'En attente',
            self::EN_COURS => 'En cours',
            self::ACCEPTEE => 'Acceptée',
            self::REJETEE => 'Rejetée',
        };
    }

    /**
     * Retourne les valeurs pour les options select
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
