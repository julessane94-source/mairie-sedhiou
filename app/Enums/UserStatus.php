<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case SUSPENDU = 'suspendu';

    /**
     * Retourne le label français du statut
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::SUSPENDU => 'Suspendu',
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
