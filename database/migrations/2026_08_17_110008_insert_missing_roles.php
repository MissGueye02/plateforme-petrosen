<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = [
            'partenaire',
            'auditeur ITIE',
            'chef de projet',
        ];

        foreach ($roles as $libelle) {
            DB::table('roles')->insertOrIgnore(['libelle' => $libelle, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // We won't remove roles on down to avoid accidental data loss; keep migration irreversible.
    }
};
