<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE jobs MODIFY target_course VARCHAR(120) NOT NULL DEFAULT 'Open for All'");
        if (! Schema::hasColumn('jobs', 'email_address')) {
            Schema::table('jobs', fn (Blueprint $t) => $t->string('email_address')->nullable());
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Use the documented database backup rollback.');
    }
};
