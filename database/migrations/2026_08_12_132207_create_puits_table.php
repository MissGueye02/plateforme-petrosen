<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bloc_petrolier_id')->constrained('blocs_petroliers')->cascadeOnDelete();
            $table->string('nom');
            $table->decimal('pression', 10, 2)->nullable();
            $table->decimal('profondeur', 10, 2)->nullable();
            $table->decimal('seuil_volume', 10, 2)->default(1000);
            $table->decimal('seuil_pression', 10, 2)->default(500);
            $table->string('statut')->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puits');
    }
};
