<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('puits_id')
                ->constrained('puits')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('utilisateurs')
                ->comment('Ingenieur terrain ayant effectue la saisie');

            $table->date('date_production');
            $table->decimal('volume', 10, 2)->comment('Volume produit, en m3');
            $table->decimal('pression', 10, 2)->comment('Pression mesuree, en bar');

            // Niveau calcule apres verification du seuil : normal | anormal
            $table->enum('niveau', ['normal', 'anormal'])->default('normal');

            $table->timestamps();

            $table->index(['puits_id', 'date_production']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
