<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->json('contenu')->nullable();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->foreignId('user_id')->constrained('utilisateurs');
            $table->string('statut')->default('genere'); // genere, exporte
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
