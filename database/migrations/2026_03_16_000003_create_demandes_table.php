<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citoyen_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('description');
            $table->string('type');
            $table->enum('statut', ['pendante', 'acceptee', 'rejetee', 'en_cours', 'terminer'])->default('pendante');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->foreignId('agent_assigne_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('date_limite')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamps();

            $table->index('citoyen_id');
            $table->index('agent_assigne_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
