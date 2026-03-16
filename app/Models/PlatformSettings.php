<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSettings extends Model
{
    protected $table = 'platform_settings';
    protected $fillable = ['cle', 'valeur', 'type', 'description', 'modifiable_par_admin'];

    /**
     * Récupérer une valeur de paramètre
     */
    public static function get($cle, $default = null)
    {
        $setting = self::where('cle', $cle)->first();
        
        if (!$setting) {
            return $default;
        }

        // Convertir selon le type
        return match ($setting->type) {
            'integer' => (int) $setting->valeur,
            'decimal' => (float) $setting->valeur,
            'boolean' => filter_var($setting->valeur, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->valeur, true),
            default => $setting->valeur,
        };
    }

    /**
     * Définir une valeur de paramètre
     */
    public static function set($cle, $valeur, $type = 'string', $description = null): void
    {
        self::updateOrCreate(
            ['cle' => $cle],
            [
                'valeur' => is_array($valeur) ? json_encode($valeur) : (string) $valeur,
                'type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * Tous les paramètres pour l'admin
     */
    public static function all(): array
    {
        return self::query()->get()->mapWithKeys(function ($setting) {
            return [$setting->cle => self::get($setting->cle)];
        })->toArray();
    }

    /**
     * Vérifier si modifiable
     */
    public function isModifiable(): bool
    {
        return $this->modifiable_par_admin;
    }
}
