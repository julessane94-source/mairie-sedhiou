<?php

namespace App\Enums;

use App\Models\PlatformSettings;

enum DemandeType: string
{
    // === ÉTAT CIVIL ===
    case DECLARATION_NAISSANCE = 'declaration_naissance';
    case COPIE_EXTRAIT_NAISSANCE = 'copie_extrait_naissance';
    case CERTIFICAT_MARIAGE = 'certificat_mariage';
    case DECLARATION_MARIAGE = 'declaration_mariage';
    case CERTIFICAT_DECES = 'certificat_deces';
    case DECLARATION_DECES = 'declaration_deces';
    case CERTIFICAT_CELIBAT = 'certificat_celibat';
    case CERTIFICAT_RESIDENCE = 'certificat_residence';
    case CERTIFICAT_VIE = 'certificat_vie';

    // === DOCUMENTS D'IDENTITÉ ===
    case CARTE_IDENTITE = 'carte_identite';
    case PASSEPORT = 'passeport';
    case RENOUVELEMENT_CARTE_ID = 'renouvellement_carte_id';
    case DUPLICATA_CARTE_ID = 'duplicata_carte_id';

    // === URBANISME ET CONSTRUCTION ===
    case PERMIS_CONSTRUCTION = 'permis_construction';
    case AUTORISATION_TRAVAUX = 'autorisation_travaux';
    case CERTIFICAT_CONFORMITE = 'certificat_conformite';
    case PLAN_CADASTRAL = 'plan_cadastral';

    // === COMMERCE ET ENTREPRISES ===
    case LICENCE_COMMERCIALE = 'licence_commerciale';
    case PATENTE = 'patente';
    case REGISTRE_COMMERCE = 'registre_commerce';
    case AUTORISATION_FONCTIONNEMENT = 'autorisation_fonctionnement';

    // === SOCIAL ET AIDES ===
    case ALLOCATION_FAMILIALE = 'allocation_familiale';
    case CARTE_HANDICAPE = 'carte_handicape';
    case AIDE_SOCIALE = 'aide_sociale';
    case BOURSE_ETUDIANTE = 'bourse_etudiante';

    // === AUTRES SERVICES ===
    case PLAINTE = 'plainte';
    case SUGGESTION = 'suggestion';
    case INFORMATION = 'information';
    case AUTRE = 'autre';

    /**
     * Retourne le label français du type de demande
     */
    public function label(): string
    {
        return match($this) {
            // État civil
            self::DECLARATION_NAISSANCE => 'Déclaration de naissance',
            self::COPIE_EXTRAIT_NAISSANCE => 'Copie d\'extrait de naissance',
            self::CERTIFICAT_MARIAGE => 'Certificat de mariage',
            self::DECLARATION_MARIAGE => 'Déclaration de mariage',
            self::CERTIFICAT_DECES => 'Certificat de décès',
            self::DECLARATION_DECES => 'Déclaration de décès',
            self::CERTIFICAT_CELIBAT => 'Certificat de célibat',
            self::CERTIFICAT_RESIDENCE => 'Certificat de résidence',
            self::CERTIFICAT_VIE => 'Certificat de vie',

            // Documents d'identité
            self::CARTE_IDENTITE => 'Carte d\'identité nationale',
            self::PASSEPORT => 'Passeport',
            self::RENOUVELEMENT_CARTE_ID => 'Renouvellement carte d\'identité',
            self::DUPLICATA_CARTE_ID => 'Duplicata carte d\'identité',

            // Urbanisme
            self::PERMIS_CONSTRUCTION => 'Permis de construire',
            self::AUTORISATION_TRAVAUX => 'Autorisation de travaux',
            self::CERTIFICAT_CONFORMITE => 'Certificat de conformité',
            self::PLAN_CADASTRAL => 'Plan cadastral',

            // Commerce
            self::LICENCE_COMMERCIALE => 'Licence commerciale',
            self::PATENTE => 'Patente',
            self::REGISTRE_COMMERCE => 'Registre du commerce',
            self::AUTORISATION_FONCTIONNEMENT => 'Autorisation de fonctionnement',

            // Social
            self::ALLOCATION_FAMILIALE => 'Allocation familiale',
            self::CARTE_HANDICAPE => 'Carte de personne handicapée',
            self::AIDE_SOCIALE => 'Aide sociale',
            self::BOURSE_ETUDIANTE => 'Bourse étudiante',

            // Autres
            self::PLAINTE => 'Plainte',
            self::SUGGESTION => 'Suggestion',
            self::INFORMATION => 'Demande d\'information',
            self::AUTRE => 'Autre',
        };
    }

    /**
     * Retourne la catégorie du type de demande
     */
    public function categorie(): string
    {
        return match($this) {
            self::DECLARATION_NAISSANCE, self::COPIE_EXTRAIT_NAISSANCE, self::CERTIFICAT_MARIAGE,
            self::DECLARATION_MARIAGE, self::CERTIFICAT_DECES, self::DECLARATION_DECES,
            self::CERTIFICAT_CELIBAT, self::CERTIFICAT_RESIDENCE, self::CERTIFICAT_VIE => 'État Civil',

            self::CARTE_IDENTITE, self::PASSEPORT, self::RENOUVELEMENT_CARTE_ID,
            self::DUPLICATA_CARTE_ID => 'Identité',

            self::PERMIS_CONSTRUCTION, self::AUTORISATION_TRAVAUX, self::CERTIFICAT_CONFORMITE,
            self::PLAN_CADASTRAL => 'Urbanisme',

            self::LICENCE_COMMERCIALE, self::PATENTE, self::REGISTRE_COMMERCE,
            self::AUTORISATION_FONCTIONNEMENT => 'Commerce',

            self::ALLOCATION_FAMILIALE, self::CARTE_HANDICAPE, self::AIDE_SOCIALE,
            self::BOURSE_ETUDIANTE => 'Social',

            default => 'Autre',
        };
    }

    /**
     * Retourne les documents requis pour ce type de demande
     */
    public function documentsRequis(): array
    {
        return match($this) {
            self::DECLARATION_NAISSANCE => [
                'Certificat médical de naissance',
                'Carte d\'identité des parents',
                'Acte de reconnaissance (si applicable)',
                'Justificatif de domicile'
            ],
            self::COPIE_EXTRAIT_NAISSANCE => [
                'Carte d\'identité',
                'Justificatif de la demande'
            ],
            self::DECLARATION_MARIAGE => [
                'Carte d\'identité des deux conjoints',
                'Certificat de célibat',
                'Certificat médical prénuptial',
                'Justificatif de domicile',
                'Témoins (2 minimum)'
            ],
            self::CERTIFICAT_MARIAGE => [
                'Carte d\'identité',
                'Numéro d\'acte de mariage'
            ],
            self::DECLARATION_DECES => [
                'Certificat médical de décès',
                'Carte d\'identité du défunt',
                'Carte d\'identité du déclarant',
                'Justificatif de lien familial'
            ],
            self::CERTIFICAT_DECES => [
                'Carte d\'identité du demandeur',
                'Numéro d\'acte de décès'
            ],
            self::CARTE_IDENTITE => [
                'Certificat de naissance',
                'Justificatif de domicile',
                'Photos d\'identité (2)',
                'Timbres fiscaux'
            ],
            self::PASSEPORT => [
                'Carte d\'identité',
                'Certificat de naissance',
                'Photos d\'identité (2)',
                'Justificatif de domicile',
                'Timbres fiscaux'
            ],
            self::PERMIS_CONSTRUCTION => [
                'Titre foncier ou certificat d\'occupation',
                'Plan d\'architecte',
                'Étude d\'impact environnemental',
                'Autorisation du propriétaire',
                'Certificat d\'urbanisme'
            ],
            self::LICENCE_COMMERCIALE => [
                'Carte d\'identité',
                'Justificatif de domicile',
                'Registre du commerce',
                'Autorisation d\'exercice',
                'Timbres fiscaux'
            ],
            default => ['Carte d\'identité', 'Justificatif de domicile']
        };
    }

    /**
     * Retourne le délai de traitement estimé (en jours)
     */
    public function delaiTraitement(): int
    {
        return match($this) {
            self::DECLARATION_NAISSANCE, self::DECLARATION_DECES => 1,
            self::COPIE_EXTRAIT_NAISSANCE, self::CERTIFICAT_MARIAGE, self::CERTIFICAT_DECES => 2,
            self::CARTE_IDENTITE, self::PASSEPORT => 15,
            self::PERMIS_CONSTRUCTION => 30,
            self::LICENCE_COMMERCIALE => 7,
            default => 5,
        };
    }

    /**
     * Retourne les frais associés (en FCFA)
     */
    public function frais(): int
    {
        return match($this) {
            self::COPIE_EXTRAIT_NAISSANCE => 5000,
            self::CERTIFICAT_MARIAGE => 10000,
            self::CERTIFICAT_DECES => 5000,
            self::CARTE_IDENTITE => 15000,
            self::PASSEPORT => 50000,
            self::PERMIS_CONSTRUCTION => 100000,
            self::LICENCE_COMMERCIALE => 25000,
            self::PATENTE => 20000,
            default => 0,
        };
    }

    /**
     * Retourne les valeurs pour les options select groupées par catégorie
     */
    public static function optionsGrouped(): array
    {
        $options = [];
        $categories = [
            'État Civil' => [],
            'Identité' => [],
            'Urbanisme' => [],
            'Commerce' => [],
            'Social' => [],
            'Autre' => [],
        ];

        foreach (self::cases() as $case) {
            $categories[$case->categorie()][] = [
                'value' => $case->value,
                'label' => $case->label(),
                'delai' => $case->delaiTraitement(),
                'frais' => $case->frais(),
            ];
        }

        // Supprimer les catégories vides
        return array_filter($categories);
    }

    /**
     * Retourne les valeurs pour les options select groupées par catégorie (ressort de la mairie seulement)
     */
    public static function optionsGroupedMunicipal(): array
    {
        $categories = [
            'État Civil' => [],
            'Urbanisme' => [],
            'Commerce' => [],
            'Social' => [],
            'Autre' => [],
        ];

        foreach (self::enabledMunicipalTypes() as $case) {
            $categories[$case->categorie()][] = [
                'value' => $case->value,
                'label' => $case->label(),
                'delai' => $case->delaiTraitement(),
                'frais' => $case->frais(),
            ];
        }

        // Supprimer les catégories vides
        return array_filter($categories);
    }

    /**
     * Retourne les valeurs pour les options select simples
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * Vérifie si ce type de demande est du ressort de la mairie
     */
    public function isAuRessortDeLaMairie(): bool
    {
        // Les demandes qui NE SONT PAS du ressort de la mairie
        $notMunicipal = [
            self::CARTE_IDENTITE,
            self::PASSEPORT,
            self::RENOUVELEMENT_CARTE_ID,
            self::DUPLICATA_CARTE_ID,
            self::REGISTRE_COMMERCE,
            self::ALLOCATION_FAMILIALE,
            self::BOURSE_ETUDIANTE,
        ];

        return !in_array($this, $notMunicipal);
    }

    /**
     * Retourne les demandes du ressort de la mairie
     */
    public static function municipalTypes(): array
    {
        return array_filter(
            self::cases(),
            fn($case) => $case->isAuRessortDeLaMairie()
        );
    }

    /**
     * Retourne les demandes municipales activées par l'admin
     */
    public static function enabledMunicipalTypes(): array
    {
        $allMunicipalTypes = self::municipalTypes();
        $activeServices = PlatformSettings::get('services_actifs');

        if (!is_array($activeServices)) {
            return $allMunicipalTypes;
        }

        return array_filter(
            $allMunicipalTypes,
            fn($case) => in_array($case->value, $activeServices, true)
        );
    }
}