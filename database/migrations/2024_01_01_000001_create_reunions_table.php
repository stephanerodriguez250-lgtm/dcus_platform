<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reunions', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->date('date');
            $table->time('heure')->nullable();
            $table->string('lieu');
            $table->text('ordre_du_jour');
            $table->text('compte_rendu')->nullable();
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee', 'annulee'])->default('planifiee');
            $table->string('convocateur')->nullable(); // Ex: Ministère de l'Enseignement Supérieur
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reunions');
    }
};
