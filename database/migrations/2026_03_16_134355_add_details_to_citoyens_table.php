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
        // Les colonnes sont déjà créées dans la migration de création de la table
        // Cette migration n'a rien à faire
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
