<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocs_petroliers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('statut')->default('actif'); // actif, inactif, en_exploration
            $table->decimal('superficie', 10, 2)->nullable();
            $table->string('localisation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocs_petroliers');
    }
};
