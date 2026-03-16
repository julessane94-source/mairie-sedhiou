<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes à la table users
        Schema::table('users', function (Blueprint $table) {
            // Ajouter prenom et nom après le champ name
            $table->string('prenom')->nullable()->after('name');
            $table->string('nom')->nullable()->after('prenom');
            $table->string('numero_citoyen')->unique()->nullable()->after('role')->comment('Généré à partir de la date de naissance et du numéro de registre');
        });

        // Ajouter les colonnes à la table profils
        Schema::table('profils', function (Blueprint $table) {
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            $table->string('numero_registre')->nullable()->unique()->after('lieu_naissance')->comment('Numéro de registre civil');
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
