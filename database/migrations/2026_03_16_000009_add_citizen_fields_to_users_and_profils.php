<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === AJOUTER À LA TABLE USERS ===
        // Données d'identité du citoyen
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom')->nullable()->after('name')->comment('Prénom du citoyen');
            }
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->nullable()->after('prenom')->comment('Nom du citoyen');
            }
            if (!Schema::hasColumn('users', 'numero_citoyen')) {
                $table->string('numero_citoyen')->unique()->nullable()->after('email')->comment('Généré: YYYYMMDD-REGISTRE-CHECKSUM');
            }
        });

        // === AJOUTER À LA TABLE PROFILS ===
        // Données spécialisées citoyens
        Schema::table('profils', function (Blueprint $table) {
            if (!Schema::hasColumn('profils', 'lieu_naissance')) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance')->comment('Lieu de naissance');
            }
            if (!Schema::hasColumn('profils', 'numero_registre')) {
                $table->string('numero_registre')->unique()->nullable()->after('lieu_naissance')->comment('Numéro de registre civil');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prenom', 'nom', 'numero_citoyen']);
        });

        Schema::table('profils', function (Blueprint $table) {
            $table->dropColumn(['lieu_naissance', 'numero_registre']);
        });
    }
};
