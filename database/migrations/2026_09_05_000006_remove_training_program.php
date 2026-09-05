<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['post_notifications', 'post_comments', 'post_reactions'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('post_type', 'training')->delete();
            }
        }

        Schema::dropIfExists('trainings');

        if (Schema::hasColumn('applications', 'applicant_trainings')) {
            Schema::table('applications', fn ($table) => $table->dropColumn('applicant_trainings'));
        }
        if (Schema::hasColumn('users', 'trainings')) {
            Schema::table('users', fn ($table) => $table->dropColumn('trainings'));
        }
    }

    public function down(): void
    {
        // The removed product feature and its data are intentionally not recreated.
    }
};
