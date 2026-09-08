<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accords', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('institution_partenaire'); // Pays / Ministère / Université étrangère
            $table->string('pays_partenaire');
            $table->string('universite_beneficiaire')->nullable(); // Université béninoise concernée
            $table->text('description')->nullable();
            $table->date('date_identification')->nullable();
            $table->date('date_signature')->nullable();
            $table->date('date_expiration')->nullable();
            $table->enum('statut', [
                'identifie',
                'en_negotiation',
                'signe',
                'en_execution',
                'cloture',
                'abandonne',
            ])->default('identifie');
            $table->foreignId('reunion_id')->nullable()->constrained('reunions')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accords');
    }
};
