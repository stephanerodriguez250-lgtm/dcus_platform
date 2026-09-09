<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accord_rapport_conformites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accord_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('resume');
            $table->text('points_conformes')->nullable();
            $table->text('points_non_conformes')->nullable();
            $table->string('chemin_rapport_word')->nullable();
            $table->foreignId('genere_par')->constrained('users');
            $table->timestamp('genere_le');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accord_rapport_conformites');
    }
};
