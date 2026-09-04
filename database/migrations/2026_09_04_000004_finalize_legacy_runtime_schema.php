<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumns();
        $this->createSocialTables();

        if (Schema::hasTable('event_reactions')) {
            DB::statement("INSERT IGNORE INTO post_reactions (post_type, post_id, user_id, reaction_type, created_at)
                SELECT 'event', event_id, user_id, reaction_type, created_at FROM event_reactions");
        }
        if (Schema::hasTable('event_comments')) {
            DB::statement("INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment, created_at)
                SELECT 'event', source.event_id, NULL, source.user_id, source.comment, source.created_at
                FROM event_comments source
                WHERE NOT EXISTS (
                    SELECT 1 FROM post_comments target
                    WHERE target.post_type = 'event' AND target.post_id = source.event_id
                      AND target.user_id = source.user_id AND target.comment = source.comment
                      AND target.created_at = source.created_at
                )");
        }

        DB::statement("ALTER TABLE job_offers MODIFY status ENUM('sent', 'accepted', 'declined', 'expired', 'done') NOT NULL DEFAULT 'sent'");
    }

    private function addColumns(): void
    {
        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        }
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'post_start_date')) $table->dateTime('post_start_date')->nullable();
            if (! Schema::hasColumn('events', 'post_end_date')) $table->dateTime('post_end_date')->nullable();
            if (! Schema::hasColumn('events', 'is_archived')) $table->boolean('is_archived')->default(false);
            if (! Schema::hasColumn('events', 'archived_at')) $table->dateTime('archived_at')->nullable();
        });
        Schema::table('interviews', function (Blueprint $table) {
            if (! Schema::hasColumn('interviews', 'offer_id')) $table->unsignedInteger('offer_id')->nullable();
        });
        DB::statement('ALTER TABLE interviews MODIFY application_id INT NULL, MODIFY job_id INT NULL');
    }

    private function createSocialTables(): void
    {
        if (! Schema::hasTable('post_reactions')) {
            Schema::create('post_reactions', function (Blueprint $table) {
                $table->id();
                $table->string('post_type', 30)->default('event');
                $table->unsignedInteger('post_id');
                $table->unsignedInteger('user_id');
                $table->string('reaction_type', 20)->default('like');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['post_type', 'post_id', 'user_id']);
                $table->index(['post_type', 'post_id']);
            });
        }
        if (! Schema::hasTable('post_comments')) {
            Schema::create('post_comments', function (Blueprint $table) {
                $table->id();
                $table->string('post_type', 30)->default('event');
                $table->unsignedInteger('post_id');
                $table->unsignedInteger('parent_comment_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->text('comment');
                $table->timestamp('created_at')->useCurrent();
                $table->index(['post_type', 'post_id']);
                $table->index('parent_comment_id');
            });
        }
        if (! Schema::hasTable('post_notifications')) {
            Schema::create('post_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('recipient_user_id');
                $table->unsignedInteger('sender_user_id');
                $table->string('post_type', 30)->default('event');
                $table->unsignedInteger('post_id');
                $table->string('notification_type', 50)->default('comment');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->useCurrent();
                $table->index('recipient_user_id');
                $table->index(['post_type', 'post_id']);
            });
        }
    }

    public function down(): void
    {
        // Additive upgrade changes are retained; a fresh baseline rollback removes the owned schema.
    }
};
