<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->dateTime('date_planifiee')->nullable();
            $table->string('statut')->default('planifiee');
            $table->foreignId('alerte_id')->nullable()->constrained('alertes')->nullOnDelete();
            $table->foreignId('ingenieur_affecte_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->foreignId('valide_par_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
