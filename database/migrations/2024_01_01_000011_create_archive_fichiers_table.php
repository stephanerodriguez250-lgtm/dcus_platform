<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_fichiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('archive_folders')->cascadeOnDelete();
            $table->string('intitule');
            $table->string('numero')->nullable();
            $table->text('description')->nullable();
            $table->string('nom_fichier');
            $table->string('chemin_fichier');
            $table->string('type_fichier');
            $table->unsignedInteger('taille');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_fichiers');
    }
};
