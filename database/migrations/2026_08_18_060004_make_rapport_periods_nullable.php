<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rapports')) {
            return;
        }

        DB::statement('ALTER TABLE rapports ALTER COLUMN periode_debut DROP NOT NULL');
        DB::statement('ALTER TABLE rapports ALTER COLUMN periode_fin DROP NOT NULL');
    }

    public function down(): void
    {
        // Irréversible volontairement : les rapports techniques peuvent désormais
        // exister sans période. On évite de rendre NULL invalides les données.
    }
};
