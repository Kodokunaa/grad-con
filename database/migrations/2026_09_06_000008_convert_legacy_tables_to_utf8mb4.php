<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $tables = DB::table('information_schema.columns')
            ->selectRaw('TABLE_NAME AS legacy_table_name')
            ->whereRaw('table_schema = database()')
            ->whereNotNull('character_set_name')
            ->where('character_set_name', '<>', 'utf8mb4')
            ->distinct()
            ->orderBy('legacy_table_name')
            ->pluck('legacy_table_name');

        foreach ($tables as $table) {
            $quotedTable = '`'.str_replace('`', '``', $table).'`';
            DB::statement("ALTER TABLE {$quotedTable} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down(): void
    {
        // Unicode conversion is intentionally irreversible because converting
        // existing utf8mb4 data back to latin1 can corrupt or discard text.
    }
};
