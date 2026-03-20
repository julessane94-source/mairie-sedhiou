<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action')->comment('create, update, delete');
            $table->string('model_type')->comment('App\\Models\\Payment, etc.');
            $table->unsignedBigInteger('model_id');
            $table->json('old_values')->nullable()->comment('Anciennes valeurs (pour update)');
            $table->json('new_values')->nullable()->comment('Nouvelles valeurs');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('logged_at')->useCurrent();

            $table->index('user_id');
            $table->index('model_type');
            $table->index('model_id');
            $table->index('action');
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
