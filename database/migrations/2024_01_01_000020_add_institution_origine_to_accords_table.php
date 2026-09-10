<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accords', function (Blueprint $table) {
            $table->string('institution_origine')->nullable()->after('institution_partenaire');
        });
    }

    public function down(): void
    {
        Schema::table('accords', function (Blueprint $table) {
            $table->dropColumn('institution_origine');
        });
    }
};
