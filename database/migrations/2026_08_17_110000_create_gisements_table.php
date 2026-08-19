<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gisements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('localisation')->nullable();
            $table->string('statut_licence')->default('en cours');
            $table->foreignId('bloc_petrolier_id')->constrained('blocs_petroliers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gisements');
    }
};
