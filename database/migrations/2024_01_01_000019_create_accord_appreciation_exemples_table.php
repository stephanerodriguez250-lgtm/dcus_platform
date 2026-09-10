<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accord_appreciation_exemples', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('ordre')->unique();
            $table->string('origine');
            $table->text('objet');
            $table->text('observations_forme');
            $table->text('observations_fond');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accord_appreciation_exemples');
    }
};
