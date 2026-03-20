<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->onDelete('cascade');
            $table->foreignId('citoyen_id')->constrained('users')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('devise')->default('XOF')->comment('Devise (XOF, EUR, etc.)');
            $table->enum('methode_paiement', ['virement', 'cheque', 'especes', 'carte', 'mobile_money'])->default('virement');
            $table->enum('statut', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->string('numero_transaction')->nullable()->unique();
            $table->dateTime('date_paiement')->nullable();
            $table->string('reference_recu')->unique()->comment('Numéro de reçu unique');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('demande_id');
            $table->index('citoyen_id');
            $table->index('statut');
            $table->index('reference_recu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
