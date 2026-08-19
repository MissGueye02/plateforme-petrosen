<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table "alertes"
 * Créée automatiquement quand une Production dépasse le seuil du puits.
 * NB : si cette table existe déjà depuis une étape précédente, adapter
 * cette migration (ex: renommer en add_production_id_to_alertes_table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('puits_id')
                ->constrained('puits')
                ->cascadeOnDelete();

            $table->foreignId('production_id')
                ->nullable()
                ->constrained('productions')
                ->cascadeOnDelete()
                ->comment('Production à l\'origine du déclenchement');

            $table->string('type')->default('depassement_seuil');
            $table->text('message');

            $table->enum('statut', ['nouvelle', 'traitee', 'ignoree'])
                ->default('nouvelle');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
