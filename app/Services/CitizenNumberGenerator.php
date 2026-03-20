<?php

namespace App\Services;

use Carbon\Carbon;

class CitizenNumberGenerator
{
    /**
     * Génère un numéro de citoyen unique basé sur :
     * - La date de naissance (YYYYMMDD)
     * - Le numéro de registre
     * - Une checksum pour l'unicité
     * 
     * Format : YYYYMMDD-REGISTRE-CHECKSUM
     * Exemple : 19900515-12345-AB1
     */
    public static function generate(string $dataNaissance, string $numeroRegistre): string
    {
        // Formater la date de naissance
        $dateFormatted = Carbon::createFromFormat('Y-m-d', $dataNaissance)->format('Ymd');

        // Nettoyer le numéro de registre (garder que les chiffres)
        $registreClean = preg_replace('/\D/', '', $numeroRegistre);

        // Générer une checksum à partir de la date et du registre
        $checksum = self::generateChecksum($dateFormatted, $registreClean);

        // Combiner en un numéro de citoyen
        return "{$dateFormatted}-{$registreClean}-{$checksum}";
    }

    /**
     * Génère une checksum alphanumérique
     */
    private static function generateChecksum(string $date, string $registre): string
    {
        // Combiner date et registre
        $combined = $date . $registre;

        // Calculer une somme de contrôle
        $hash = hash('crc32', $combined);
        
        // Convertir en caractères alphanumériques lisibles
        $checksum = strtoupper(substr($hash, 0, 3));

        return $checksum;
    }

    /**
     * Valide le format d'un numéro de citoyen
     */
    public static function validate(string $numeroCitoyen): bool
    {
        // Format : YYYYMMDD-REGISTRE-CHECKSUM
        return preg_match('/^\d{8}-\d+-[A-Z0-9]{3}$/', $numeroCitoyen) === 1;
    }

    /**
     * Extrait les données du numéro de citoyen
     */
    public static function extract(string $numeroCitoyen): array
    {
        if (!self::validate($numeroCitoyen)) {
            return [];
        }

        [$date, $registre, $checksum] = explode('-', $numeroCitoyen);

        return [
            'date_naissance' => Carbon::createFromFormat('Ymd', $date)->format('Y-m-d'),
            'numero_registre' => $registre,
            'checksum' => $checksum,
        ];
    }
}
