<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table principale des CODIR
        Schema::create('codirs', function (Blueprint $table) {
            $table->id();
            $table->string('objet');
            $table->date('date');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->string('lieu')->default('Salle de réunion DCUS');
            $table->string('presidente')->nullable();
            $table->string('rapporteur')->nullable();
            $table->text('synthese')->nullable();       // Synthèse des discussions
            $table->text('decisions')->nullable();      // Décisions prises
            $table->text('divers')->nullable();         // Points divers
            $table->date('prochaine_reunion')->nullable();
            $table->enum('statut', ['planifie', 'tenu', 'annule'])->default('planifie');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Participants à chaque CODIR
        Schema::create('codir_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codir_id')->constrained('codirs')->cascadeOnDelete();
            $table->string('nom_complet');       // Nom du participant
            $table->string('email')->nullable(); // Email pour notification
            $table->string('fonction')->nullable();
            $table->boolean('present')->default(true);
            $table->timestamps();
        });

        // Rapports (comptes rendus) uploadés
        Schema::create('codir_rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codir_id')->constrained('codirs')->cascadeOnDelete();
            $table->string('nom_fichier');       // Nom original du fichier
            $table->string('chemin_fichier');    // Chemin de stockage
            $table->string('type_fichier');      // docx ou pdf
            $table->unsignedBigInteger('taille')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });

        // Accès au téléchargement par utilisateur
        Schema::create('codir_acces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('codir_id')->constrained('codirs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accorde_par')->constrained('users');
            $table->timestamp('date_acces')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codir_acces');
        Schema::dropIfExists('codir_rapports');
        Schema::dropIfExists('codir_participants');
        Schema::dropIfExists('codirs');
    }
};
