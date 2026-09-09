<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accord_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accord_id')->constrained('accords')->cascadeOnDelete();
            $table->string('evenement'); // ex: "Accord reçu", "Appréciation enregistrée", "Envoyé", "Signature enregistrée"
            $table->text('commentaire')->nullable();
            $table->foreignId('modifie_par')->constrained('users');
            $table->timestamp('date_modification')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accord_historiques');
    }
};
