<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
$table->string('numero_registre')->unique()->after('numero_citoyen');
$table->date('date_naissance')->after('adresse');
$table->string('lieu_naissance')->after('date_naissance');

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('citoyens', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citoyens', function (Blueprint $table) {
            //
        });
    }
};
