<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_dossier_partages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_original_id')->nullable()->constrained('archive_folders')->nullOnDelete();
            $table->foreignId('dossier_copie_id')->constrained('archive_folders')->cascadeOnDelete();
            $table->string('zip_path')->nullable();
            $table->foreignId('partage_par')->constrained('users');
            $table->foreignId('destinataire_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_dossier_partages');
    }
};
