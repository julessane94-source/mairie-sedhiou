<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case CITOYEN = 'citoyen';

    /**
     * Retourne le label français du rôle
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrateur',
            self::AGENT => 'Agent',
            self::CITOYEN => 'Citoyen',
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
