<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accord_appreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accord_id')->constrained('accords')->cascadeOnDelete();
            $table->string('origine');
            $table->text('objet');
            $table->text('avis');
            $table->text('observations_forme')->nullable();
            $table->text('observations_fond')->nullable();
            $table->foreignId('redige_par')->constrained('users');
            $table->string('chemin_fiche_word')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accord_appreciations');
    }
};
