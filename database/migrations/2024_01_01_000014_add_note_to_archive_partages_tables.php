<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archive_partages', function (Blueprint $table) {
            $table->text('note')->nullable()->after('destinataire_id');
        });

        Schema::table('archive_dossier_partages', function (Blueprint $table) {
            $table->text('note')->nullable()->after('destinataire_id');
        });
    }

    public function down(): void
    {
        Schema::table('archive_partages', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('archive_dossier_partages', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
