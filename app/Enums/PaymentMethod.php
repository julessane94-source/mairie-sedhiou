<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case VIREMENT = 'virement';
    case CHEQUE = 'cheque';
    case ESPECES = 'especes';
    case CARTE = 'carte';
    case MOBILE_MONEY = 'mobile_money';

    /**
     * Retourne le label français de la méthode
     */
    public function label(): string
    {
        return match($this) {
            self::VIREMENT => 'Virement bancaire',
            self::CHEQUE => 'Chèque',
            self::ESPECES => 'Espèces',
            self::CARTE => 'Carte bancaire',
            self::MOBILE_MONEY => 'Paiement mobile',
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
