<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $indexes = [
        'jobs' => [
            'idx_jobs_employer_id' => ['employer_id'],
        ],
        'job_offers' => [
            'idx_job_offers_employer_status' => ['employer_id', 'status'],
            'idx_job_offers_alumni_status' => ['alumni_id', 'status'],
        ],
        'employer_activity_logs' => [
            'idx_employer_logs_created' => ['created_at'],
            'idx_employer_logs_employer_action' => ['employer_id', 'action'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! $this->exists($table, $name)) {
                    DB::statement(sprintf('ALTER TABLE `%s` ADD INDEX `%s` (`%s`)', $table, $name, implode('`, `', $columns)));
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if ($this->exists($table, $name)) {
                    DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $table, $name));
                }
            }
        }
    }

    private function exists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = database()')
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
