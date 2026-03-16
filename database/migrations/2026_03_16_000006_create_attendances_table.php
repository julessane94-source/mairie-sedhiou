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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->date('date_presence');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->enum('statut', ['present', 'absent', 'congé', 'retard', 'repos'])->default('absent');
            $table->decimal('heures_travaillees', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('justificatif')->nullable(); // Chemin fichier si justif nécessaire
            $table->timestamps();

            // Index pour requêtes rapides
            $table->index('agent_id');
            $table->index('date_presence');
            $table->unique(['agent_id', 'date_presence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
