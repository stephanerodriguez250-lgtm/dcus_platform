<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accord_appreciations', function (Blueprint $table) {
            $table->string('chemin_fiche_ministere')->nullable()->after('chemin_fiche_word');
            $table->string('nom_fiche_ministere')->nullable()->after('chemin_fiche_ministere');
            $table->timestamp('avis_mesrs_valide_le')->nullable()->after('nom_fiche_ministere');
            $table->foreignId('avis_mesrs_valide_par')->nullable()->after('avis_mesrs_valide_le')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accord_appreciations', function (Blueprint $table) {
            $table->dropForeign(['avis_mesrs_valide_par']);
            $table->dropColumn(['chemin_fiche_ministere', 'nom_fiche_ministere', 'avis_mesrs_valide_le', 'avis_mesrs_valide_par']);
        });
    }
};
