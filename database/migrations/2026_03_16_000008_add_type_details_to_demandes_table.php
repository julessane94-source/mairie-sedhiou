<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            // Ajouter les colonnes pour les détails des types de demandes
            $table->json('documents_requis')->nullable()->after('motif_rejet');
            $table->integer('frais_estimes')->default(0)->after('documents_requis');
            $table->integer('delai_traitement_estime')->nullable()->after('frais_estimes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn(['documents_requis', 'frais_estimes', 'delai_traitement_estime']);
        });
    }
};