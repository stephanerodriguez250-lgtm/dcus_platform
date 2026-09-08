<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_partages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fichier_original_id')->nullable()->constrained('archive_fichiers')->nullOnDelete();
            $table->foreignId('fichier_copie_id')->constrained('archive_fichiers')->cascadeOnDelete();
            $table->foreignId('partage_par')->constrained('users');
            $table->foreignId('destinataire_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_partages');
    }
};
