<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('projets', 'rapport_id')) {
            Schema::table('projets', function (Blueprint $table) {
                $table->foreignId('rapport_id')
                    ->nullable()
                    ->after('partenaire_id')
                    ->constrained('rapports')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projets', 'rapport_id')) {
            Schema::table('projets', function (Blueprint $table) {
                $table->dropForeign(['rapport_id']);
                $table->dropColumn('rapport_id');
            });
        }
    }
};
