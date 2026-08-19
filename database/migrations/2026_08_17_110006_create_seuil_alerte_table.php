<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seuil_alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puits_id')->constrained('puits')->cascadeOnDelete();
            $table->decimal('valeur_seuil', 15, 4);
            $table->foreignId('configure_par_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seuil_alertes');
    }
};
