<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('GradConn requires MySQL or MariaDB to preserve its existing SQL workflows.');
        }
        $schema = json_decode(file_get_contents(database_path('schema/gradconn.json')), true, flags: JSON_THROW_ON_ERROR);
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
    }

    public function down(): void
    {
        throw new RuntimeException('Restore the pre-migration database backup to roll back without discarding alumni data.');
    }
};
