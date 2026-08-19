<?php

// Migration kept as a no-op because a rapports table already exists in the project
// (see 2026_08_13_002029_create_rapports_table.php). This file is left in place
// to avoid migration order surprises but its up() method intentionally does nothing.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // rapports table already created by an earlier migration; skipping.
    }

    public function down(): void
    {
        // no-op
    }
};
