<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'code')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('code')->nullable()->unique()->after('libelle');
            });
        }

        $mapping = [
            'administrateur' => 'administrateur',
            'ingenieur terrain' => 'ingenieur_terrain',
            'ingénieur terrain' => 'ingenieur_terrain',
            'chef de projet' => 'chef_projet',
            'partenaire' => 'partenaire',
            'auditeur itie' => 'auditeur_itie',
        ];

        foreach (DB::table('roles')->orderBy('id')->get(['id', 'libelle', 'code']) as $role) {
            if (! empty($role->code)) {
                continue;
            }

            $key = Str::lower(Str::ascii(trim($role->libelle)));
            $code = $mapping[$key] ?? Str::slug($role->libelle, '_') . '_' . $role->id;

            // Plusieurs anciennes lignes peuvent représenter le même rôle.
            // On garde la première occurrence comme rôle canonique et on donne
            // un code legacy aux doublons afin de ne jamais casser la contrainte unique.
            if (DB::table('roles')->where('code', $code)->exists()) {
                $code .= '_legacy_' . $role->id;
            }

            DB::table('roles')->where('id', $role->id)->update([
                'code' => $code,
                'updated_at' => now(),
            ]);
        }

        // Le rôle Partenaire doit exister sans modifier les utilisateurs existants.
        $partenaire = DB::table('roles')->where('code', 'partenaire')->first();
        if (! $partenaire) {
            DB::table('roles')->insert([
                'libelle' => 'Partenaire',
                'code' => 'partenaire',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'code')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            });
        }
    }
};
