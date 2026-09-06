<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'category')) {
            Schema::table('events', fn (Blueprint $table) => $table->string('category', 30)->default('announcement')->after('title'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'category')) {
            Schema::table('events', fn (Blueprint $table) => $table->dropColumn('category'));
        }
    }
};
