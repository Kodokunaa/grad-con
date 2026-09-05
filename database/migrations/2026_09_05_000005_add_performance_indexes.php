<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $indexes = [
        'users' => [
            'idx_users_role_active' => ['role', 'is_active'],
            'idx_users_role_status' => ['role', 'status'],
            'idx_users_employment' => ['role', 'is_active', 'employment_status', 'job_aligned'],
            'idx_users_email' => ['email'],
        ],
        'jobs' => [
            'idx_jobs_open_dates' => ['is_open', 'start_date', 'end_date'],
            'idx_jobs_target_open' => ['target_course', 'is_open'],
        ],
        'applications' => [
            'idx_applications_alumni_status' => ['alumni_id', 'status'],
            'idx_applications_job_status' => ['job_id', 'status'],
        ],
        'events' => [
            'idx_events_archive_dates' => ['is_archived', 'post_start_date', 'post_end_date'],
            'idx_events_created' => ['created_at'],
        ],
        'interviews' => [
            'idx_interviews_employer_status_date' => ['employer_id', 'status', 'interview_date'],
            'idx_interviews_alumni_status_date' => ['alumni_id', 'status', 'interview_date'],
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
