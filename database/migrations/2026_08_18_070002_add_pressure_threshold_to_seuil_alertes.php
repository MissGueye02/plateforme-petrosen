<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seuil_alertes', function (Blueprint $table) {
            $table->decimal('valeur_pression', 15, 4)->nullable()->after('valeur_seuil');
        });
    }

    public function down(): void
    {
        Schema::table('seuil_alertes', fn (Blueprint $table) => $table->dropColumn('valeur_pression'));
    }
};
