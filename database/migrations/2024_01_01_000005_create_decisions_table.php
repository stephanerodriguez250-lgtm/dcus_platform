<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            // Peut être lié à un CODIR ou une Réunion
            $table->foreignId('codir_id')->nullable()->constrained('codirs')->cascadeOnDelete();
            $table->foreignId('reunion_id')->nullable()->constrained('reunions')->cascadeOnDelete();
            $table->string('intitule');                    // Description de la décision
            $table->string('responsable')->nullable();     // Personne chargée
            $table->date('echeance')->nullable();          // Date limite
            $table->enum('statut', [
                'assignee',
                'en_cours',
                'validee',
                'cloturee',
                'annulee',
            ])->default('assignee');
            $table->integer('progression')->default(0);    // 0 à 100%
            $table->text('commentaire')->nullable();       // Dernier commentaire
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Historique des mises à jour des décisions
        Schema::create('decision_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('decisions')->cascadeOnDelete();
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut');
            $table->integer('progression')->default(0);
            $table->text('commentaire')->nullable();
            $table->foreignId('modifie_par')->constrained('users');
            $table->timestamp('date_modification')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decision_historiques');
        Schema::dropIfExists('decisions');
    }
};
