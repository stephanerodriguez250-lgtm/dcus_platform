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
            $table->string('institution_partenaire'); // Université / institution partenaire à l'origine de l'accord
            $table->string('reference'); // Référence MESRS
            $table->date('date_arrivee');
            $table->time('heure_arrivee');
            $table->string('chemin_fichier')->nullable();
            $table->string('nom_fichier')->nullable();
            $table->date('envoye_le')->nullable(); // Étape 3 : accord + fiche transmis au destinataire
            $table->date('date_signature')->nullable();
            $table->string('chemin_fichier_signe')->nullable(); // document signé, chargé à l'étape 3
            $table->string('nom_fichier_signe')->nullable();
            $table->unsignedSmallInteger('duree_valeur')->nullable();
            $table->enum('duree_unite', ['mois', 'ans'])->nullable();
            $table->date('date_expiration')->nullable(); // calculée depuis date_signature + durée
            $table->timestamp('alerte_expiration_envoyee_le')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accords');
    }
};
