<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            // Type précis de la source
            $table->enum('source_type', [
                'codir_interne',
                'codir_externe',
                'reunion_interne',
                'reunion_externe',
                'note_ministerielle',
            ])->nullable()->after('reunion_id');
            // Champs pour la note ministérielle directe
            $table->string('note_numero')->nullable()->after('source_type');
            $table->date('note_date')->nullable()->after('note_numero');
            $table->string('note_expediteur')->nullable()->after('note_date');
            $table->string('note_objet')->nullable()->after('note_expediteur');
            $table->string('note_fichier')->nullable()->after('note_objet'); // chemin stockage
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'note_numero',
                'note_date',
                'note_expediteur',
                'note_objet',
                'note_fichier',
            ]);
        });
    }
};
