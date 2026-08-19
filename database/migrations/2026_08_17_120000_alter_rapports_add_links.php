<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rapports')) {
            Schema::table('rapports', function (Blueprint $table) {
                if (! Schema::hasColumn('rapports', 'type')) {
                    $table->string('type')->nullable()->after('titre'); // technique, itie, statistique
                }
                if (! Schema::hasColumn('rapports', 'mission_id')) {
                    $table->foreignId('mission_id')->nullable()->after('user_id')->constrained('missions')->nullOnDelete();
                }
                if (! Schema::hasColumn('rapports', 'production_id')) {
                    $table->foreignId('production_id')->nullable()->after('mission_id')->constrained('productions')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rapports')) {
            Schema::table('rapports', function (Blueprint $table) {
                if (Schema::hasColumn('rapports', 'production_id')) {
                    $table->dropForeign(['production_id']);
                    $table->dropColumn('production_id');
                }
                if (Schema::hasColumn('rapports', 'mission_id')) {
                    $table->dropForeign(['mission_id']);
                    $table->dropColumn('mission_id');
                }
                if (Schema::hasColumn('rapports', 'type')) {
                    $table->dropColumn('type');
                }
            });
        }
    }
};
