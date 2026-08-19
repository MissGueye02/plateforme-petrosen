<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alter existing notifications table to add application-specific columns
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (! Schema::hasColumn('notifications', 'utilisateur_id')) {
                    $table->foreignId('utilisateur_id')->nullable()->after('id')->constrained('utilisateurs')->nullOnDelete();
                }
                if (! Schema::hasColumn('notifications', 'message')) {
                    $table->text('message')->nullable()->after('utilisateur_id');
                }
                if (! Schema::hasColumn('notifications', 'lu')) {
                    $table->boolean('lu')->default(false)->after('message');
                }
                if (! Schema::hasColumn('notifications', 'type')) {
                    $table->string('type')->nullable()->after('lu');
                }
            });
        } else {
            // Fallback: create notifications table if it somehow doesn't exist
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('utilisateur_id')->nullable()->constrained('utilisateurs')->nullOnDelete();
                $table->text('message')->nullable();
                $table->boolean('lu')->default(false);
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // remove the columns we added, if present
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'type')) {
                    $table->dropColumn('type');
                }
                if (Schema::hasColumn('notifications', 'lu')) {
                    $table->dropColumn('lu');
                }
                if (Schema::hasColumn('notifications', 'message')) {
                    $table->dropColumn('message');
                }
                if (Schema::hasColumn('notifications', 'utilisateur_id')) {
                    $table->dropForeign([ 'utilisateur_id' ]);
                    $table->dropColumn('utilisateur_id');
                }
            });
        }
    }
};
