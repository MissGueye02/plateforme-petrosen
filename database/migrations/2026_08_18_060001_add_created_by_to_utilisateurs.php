<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('utilisateurs', 'created_by')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('role_id')
                    ->constrained('utilisateurs')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('utilisateurs', 'created_by')) {
            Schema::table('utilisateurs', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
    }
};
