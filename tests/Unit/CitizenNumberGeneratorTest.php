<?php

namespace Tests\Unit;

use App\Services\CitizenNumberGenerator;
use Tests\TestCase;

class CitizenNumberGeneratorTest extends TestCase
{
    /**
     * Test - Génère numéro de citoyen valide
     */
    public function test_generates_valid_citizen_number(): void
    {
        $dataNaissance = '1990-05-15';
        $numeroRegistre = '12345';

        $numeroCitoyen = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);

        $this->assertNotNull($numeroCitoyen);
        $this->assertTrue(CitizenNumberGenerator::validate($numeroCitoyen));
    }

    /**
     * Test - Format correct (YYYYMMDD-REGISTRE-CHECKSUM)
     */
    public function test_citizen_number_has_correct_format(): void
    {
        $dataNaissance = '1990-05-15';
        $numeroRegistre = '12345';

        $numeroCitoyen = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);

        // Format : YYYYMMDD-REGISTRE-CHECKSUM
        $this->assertMatchesRegularExpression(
            '/^\d{8}-\d+-[A-Z0-9]{3}$/',
            $numeroCitoyen
        );
    }

    /**
     * Test - Date de naissance formatée correctement
     */
    public function test_date_formatted_in_citizen_number(): void
    {
        $dataNaissance = '1990-05-15';
        $numeroRegistre = '12345';

        $numeroCitoyen = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);

        // Devrait commencer par 19900515
        $this->assertStringStartsWith('19900515', $numeroCitoyen);
    }

    /**
     * Test - Numéro registre dans le numéro de citoyen
     */
    public function test_registre_number_in_citizen_number(): void
    {
        $dataNaissance = '2000-12-25';
        $numeroRegistre = '99999';

        $numeroCitoyen = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);

        // Devrait contenir 99999
        $this->assertStringContainsString('99999', $numeroCitoyen);
    }

    /**
     * Test - Deux numéros différents pour mêmes données
     */
    public function test_same_input_generates_same_number(): void
    {
        $dataNaissance = '1985-03-20';
        $numeroRegistre = '54321';

        $numero1 = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);
        $numero2 = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);

        $this->assertEquals($numero1, $numero2);
    }

    /**
     * Test - Différents registres génèrent différents checksums
     */
    public function test_different_registres_generate_different_numbers(): void
    {
        $dataNaissance = '1995-07-10';
        $numeroRegistre1 = '11111';
        $numeroRegistre2 = '22222';

        $numero1 = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre1);
        $numero2 = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre2);

        $this->assertNotEquals($numero1, $numero2);
    }

    /**
     * Test - Valide format invalide
     */
    public function test_rejects_invalid_format(): void
    {
        $this->assertFalse(CitizenNumberGenerator::validate('invalid'));
        $this->assertFalse(CitizenNumberGenerator::validate('20000101-12345'));
        $this->assertFalse(CitizenNumberGenerator::validate('20000101-12345-'));
    }

    /**
     * Test - Extrait données du numéro de citoyen
     */
    public function test_extracts_data_from_citizen_number(): void
    {
        $dataNaissance = '1990-05-15';
        $numeroRegistre = '12345';

        $numeroCitoyen = CitizenNumberGenerator::generate($dataNaissance, $numeroRegistre);
        $extracted = CitizenNumberGenerator::extract($numeroCitoyen);

        $this->assertEquals($dataNaissance, $extracted['date_naissance']);
        $this->assertEquals($numeroRegistre, $extracted['numero_registre']);
        $this->assertArrayHasKey('checksum', $extracted);
    }

    /**
     * Test - Extraction d'un numero invalide retourne tableau vide
     */
    public function test_extract_invalid_number_returns_empty(): void
    {
        $extracted = CitizenNumberGenerator::extract('invalid');

        $this->assertEmpty($extracted);
    }

    /**
     * Test - Traite différentes dates
     */
    public function test_handles_various_dates(): void
    {
        $dates = [
            '1900-01-01',
            '1950-06-15',
            '2000-12-31',
            '2020-02-29', // Année bissextile
        ];

        foreach ($dates as $date) {
            $numero = CitizenNumberGenerator::generate($date, '12345');
            $this->assertTrue(CitizenNumberGenerator::validate($numero));
        }
    }

    /**
     * Test - Registre avec caractères spéciaux (tirets, slashes)
     */
    public function test_handles_registre_with_special_chars(): void
    {
        $numero1 = CitizenNumberGenerator::generate('1990-05-15', 'SN/2024-123');
        $numero2 = CitizenNumberGenerator::generate('1990-05-15', 'SN-2024-123');

        // Les versions nettoyées devraient être identiques
        $this->assertNotEmpty($numero1);
        $this->assertNotEmpty($numero2);
    }
}
