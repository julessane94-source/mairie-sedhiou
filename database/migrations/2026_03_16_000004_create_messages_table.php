<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->onDelete('cascade');
            $table->foreignId('expediteur_id')->constrained('users')->onDelete('cascade');
            $table->text('contenu');
            $table->enum('type_expediteur', ['citoyen', 'agent', 'admin']);
            $table->timestamps();

            $table->index('demande_id');
            $table->index('expediteur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
