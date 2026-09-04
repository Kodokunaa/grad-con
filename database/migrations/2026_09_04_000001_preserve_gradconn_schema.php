<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('GradConn requires MySQL or MariaDB to preserve its existing SQL workflows.');
        }
        $schema = json_decode(file_get_contents(database_path('schema/gradconn.json')), true, flags: JSON_THROW_ON_ERROR);
        $ownsBaseline = ! Schema::hasTable('users');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($schema['create'] as $sql) {
                DB::unprepared($sql);
            }
            foreach ($schema['alter'] as $sql) {
                try {
                    DB::unprepared($sql);
                } catch (QueryException $e) {
                    // Existing databases already contain some additive changes.
                    if (! in_array((int) ($e->errorInfo[1] ?? 0), [1060, 1061], true)) {
                        throw $e;
                    }
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        Schema::create('gradconn_migration_state', function ($table) use ($ownsBaseline) {
            $table->boolean('baseline_owned')->default($ownsBaseline);
        });
        DB::table('gradconn_migration_state')->insert(['baseline_owned' => $ownsBaseline]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('gradconn_migration_state')) {
            throw new RuntimeException('Baseline ownership is unknown. Restore the pre-migration backup instead of deleting tables.');
        }
        $owned = (bool) DB::table('gradconn_migration_state')->value('baseline_owned');
        Schema::drop('gradconn_migration_state');
        if (! $owned) {
            return;
        }
        $schema = json_decode(file_get_contents(database_path('schema/gradconn.json')), true, flags: JSON_THROW_ON_ERROR);
        $tables = [];
        foreach ($schema['create'] as $sql) {
            if (preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sql, $match)) {
                $tables[] = $match[1];
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach (array_reverse(array_unique($tables)) as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
