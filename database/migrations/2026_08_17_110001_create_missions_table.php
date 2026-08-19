<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['proposee','acceptee','refusee'])->default('proposee');
            $table->foreignId('ingenieur_id')->constrained('utilisateurs')->cascadeOnDelete();
            $table->foreignId('chef_projet_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
