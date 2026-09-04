<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniFeedController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni');
            $user_id = (int) (\gc_context()->session['user']['id'] ?? 0);
            $profileColumn = \gc_alumni_feed_get_user_profile_column($pdo);
            $currentUserPhoto = '';
            $userProfileSelect = $profileColumn ? ", `{$profileColumn}` AS profile_photo" : ', NULL AS profile_photo';
            $userStmt = $pdo->prepare("SELECT id, fullname, course {$userProfileSelect} FROM users WHERE id=? AND role='alumni' LIMIT 1");
            $userStmt->execute([$user_id]);
            $currentUser = $userStmt->fetch(\PDO::FETCH_ASSOC);
            if (! $currentUser) {
                \gc_finish('Unauthorized access.');
            }
            $alumni_course = trim($currentUser['course'] ?? '');
            $currentFullname = $currentUser['fullname'] ?? 'Alumni';
            $currentUserPhoto = $currentUser['profile_photo'] ?? '';
            try {
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_reactions (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        user_id INT NOT NULL,\r\n        reaction_type VARCHAR(20) NOT NULL DEFAULT 'like',\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        UNIQUE KEY unique_post_user_reaction (post_type, post_id, user_id),\r\n        INDEX idx_post_reactions_post (post_type, post_id),\r\n        INDEX idx_post_reactions_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_comments (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        parent_comment_id INT NULL DEFAULT NULL,\r\n        user_id INT NOT NULL,\r\n        comment TEXT NOT NULL,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_comments_post (post_type, post_id),\r\n        INDEX idx_post_comments_parent (parent_comment_id),\r\n        INDEX idx_post_comments_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_notifications (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        recipient_user_id INT NOT NULL,\r\n        sender_user_id INT NOT NULL,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        notification_type VARCHAR(50) NOT NULL DEFAULT 'comment',\r\n        message TEXT NOT NULL,\r\n        is_read TINYINT(1) NOT NULL DEFAULT 0,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_notifications_recipient (recipient_user_id),\r\n        INDEX idx_post_notifications_post (post_type, post_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                foreach (['post_comments' => ['post_type' => "ALTER TABLE post_comments ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => 'ALTER TABLE post_comments ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type', 'parent_comment_id' => 'ALTER TABLE post_comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER post_id', 'user_id' => 'ALTER TABLE post_comments ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER parent_comment_id', 'comment' => 'ALTER TABLE post_comments ADD COLUMN comment TEXT NOT NULL AFTER user_id', 'created_at' => 'ALTER TABLE post_comments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER comment'], 'post_reactions' => ['post_type' => "ALTER TABLE post_reactions ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => 'ALTER TABLE post_reactions ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type', 'user_id' => 'ALTER TABLE post_reactions ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER post_id', 'reaction_type' => "ALTER TABLE post_reactions ADD COLUMN reaction_type VARCHAR(20) NOT NULL DEFAULT 'like' AFTER user_id", 'created_at' => 'ALTER TABLE post_reactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reaction_type'], 'post_notifications' => ['recipient_user_id' => 'ALTER TABLE post_notifications ADD COLUMN recipient_user_id INT NOT NULL DEFAULT 0 AFTER id', 'sender_user_id' => 'ALTER TABLE post_notifications ADD COLUMN sender_user_id INT NOT NULL DEFAULT 0 AFTER recipient_user_id', 'post_type' => "ALTER TABLE post_notifications ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER sender_user_id", 'post_id' => 'ALTER TABLE post_notifications ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type', 'notification_type' => "ALTER TABLE post_notifications ADD COLUMN notification_type VARCHAR(50) NOT NULL DEFAULT 'comment' AFTER post_id", 'message' => 'ALTER TABLE post_notifications ADD COLUMN message TEXT NOT NULL AFTER notification_type', 'is_read' => 'ALTER TABLE post_notifications ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message', 'created_at' => 'ALTER TABLE post_notifications ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_read']] as $table => $columns) {
                    foreach ($columns as $column => $sql) {
                        if (! \gc_alumni_feed_column_exists($pdo, $table, $column)) {
                            \gc_context()->schemaChange($pdo, $sql);
                        }
                    }
                }
                if (\gc_alumni_feed_table_exists($pdo, 'event_reactions')) {
                    \gc_context()->schemaChange($pdo, "INSERT IGNORE INTO post_reactions (post_type, post_id, user_id, reaction_type, created_at)\r\n                    SELECT 'event', event_id, user_id, reaction_type, created_at FROM event_reactions");
                }
                if (\gc_alumni_feed_table_exists($pdo, 'event_comments')) {
                    \gc_context()->schemaChange($pdo, "INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment, created_at)\r\n                    SELECT 'event', ec.event_id, NULL, ec.user_id, ec.comment, ec.created_at\r\n                    FROM event_comments ec\r\n                    WHERE NOT EXISTS (\r\n                        SELECT 1 FROM post_comments pc\r\n                        WHERE pc.post_type='event'\r\n                          AND pc.post_id=ec.event_id\r\n                          AND pc.user_id=ec.user_id\r\n                          AND pc.comment=ec.comment\r\n                          AND pc.created_at=ec.created_at\r\n                    )");
                }
            } catch (\Throwable $ex) {
                if ($ex instanceof PageResponse) {
                    throw $ex;
                }
                \gc_finish('Database setup error: '.\gc_e(\gc_public_error($ex)));
            }
            $allowedReactions = ['like' => ['emoji' => '👍', 'label' => 'Like'], 'love' => ['emoji' => '❤️', 'label' => 'Love'], 'haha' => ['emoji' => '😂', 'label' => 'Haha'], 'angry' => ['emoji' => '😡', 'label' => 'Angry']];
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['ajax_action'])) {
                \gc_header('Content-Type: application/json');
                $action = (string) (\gc_context()->post['ajax_action'] ?? '');
                $postType = (string) (\gc_context()->post['post_type'] ?? '');
                $postId = (int) (\gc_context()->post['post_id'] ?? 0);
                if (! in_array($postType, ['event', 'training'], true) || $postId <= 0 || $user_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
                    \gc_finish();
                }
                try {
                    if ($action === 'react') {
                        global $allowedReactions;
                        $reactionType = (string) (\gc_context()->post['reaction_type'] ?? 'like');
                        if (! isset($allowedReactions[$reactionType])) {
                            echo json_encode(['success' => false, 'message' => 'Invalid reaction.']);
                            \gc_finish();
                        }
                        $existing = \gc_alumni_feed_get_user_reaction($pdo, $postType, $postId, $user_id);
                        if ($existing === $reactionType) {
                            $del = $pdo->prepare('DELETE FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=?');
                            $del->execute([$postType, $postId, $user_id]);
                            $userReaction = '';
                        } elseif ($existing !== '') {
                            $upd = $pdo->prepare('UPDATE post_reactions SET reaction_type=?, created_at=NOW() WHERE post_type=? AND post_id=? AND user_id=?');
                            $upd->execute([$reactionType, $postType, $postId, $user_id]);
                            $userReaction = $reactionType;
                        } else {
                            $ins = $pdo->prepare('INSERT INTO post_reactions (post_type, post_id, user_id, reaction_type) VALUES (?, ?, ?, ?)');
                            $ins->execute([$postType, $postId, $user_id, $reactionType]);
                            $userReaction = $reactionType;
                        }
                        $counts = \gc_alumni_feed_get_reaction_counts($pdo, $postType, $postId);
                        $comments = \gc_alumni_feed_get_comments($pdo, $postType, $postId);
                        echo json_encode(['success' => true, 'user_reaction' => $userReaction, 'reaction_label' => $userReaction ? $allowedReactions[$userReaction]['label'] : 'Like', 'reaction_emoji' => $userReaction ? $allowedReactions[$userReaction]['emoji'] : '👍', 'engagement_html' => \gc_alumni_feed_render_engagement_html($counts, count($comments), $allowedReactions)]);
                        \gc_finish();
                    }
                    if ($action === 'comment') {
                        $comment = trim((string) (\gc_context()->post['comment'] ?? ''));
                        $parentCommentId = (int) (\gc_context()->post['parent_comment_id'] ?? 0);
                        $parentCommentId = $parentCommentId > 0 ? $parentCommentId : null;
                        if ($comment === '') {
                            echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
                            \gc_finish();
                        }
                        if ($parentCommentId !== null) {
                            $parentCheck = $pdo->prepare('SELECT id FROM post_comments WHERE id=? AND post_type=? AND post_id=? LIMIT 1');
                            $parentCheck->execute([$parentCommentId, $postType, $postId]);
                            if (! $parentCheck->fetchColumn()) {
                                echo json_encode(['success' => false, 'message' => 'The comment you are replying to was not found.']);
                                \gc_finish();
                            }
                        }
                        $ins = $pdo->prepare('INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment) VALUES (?, ?, ?, ?, ?)');
                        $ins->execute([$postType, $postId, $parentCommentId, $user_id, $comment]);
                        if ($parentCommentId !== null) {
                            $replyOwnerStmt = $pdo->prepare('SELECT user_id FROM post_comments WHERE id=? LIMIT 1');
                            $replyOwnerStmt->execute([$parentCommentId]);
                            $replyOwnerId = (int) ($replyOwnerStmt->fetchColumn() ?: 0);
                            if ($replyOwnerId > 0 && $replyOwnerId !== $user_id) {
                                $replyMsg = $currentFullname.' replied to your comment.';
                                $replyNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'reply', ?)");
                                $replyNotif->execute([$replyOwnerId, $user_id, $postType, $postId, $replyMsg]);
                            }
                        }
                        $posterId = 0;
                        $postTitle = '';
                        if ($postType === 'event') {
                            $ownerStmt = $pdo->prepare('SELECT posted_by, title FROM events WHERE id=? LIMIT 1');
                        } else {
                            $ownerStmt = $pdo->prepare('SELECT posted_by, title FROM trainings WHERE id=? LIMIT 1');
                        }
                        $ownerStmt->execute([$postId]);
                        $owner = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
                        if ($owner) {
                            $posterId = (int) ($owner['posted_by'] ?? 0);
                            $postTitle = (string) ($owner['title'] ?? 'your post');
                        }
                        $notificationCreated = false;
                        if ($posterId > 0 && $posterId !== $user_id) {
                            $notifMsg = $currentFullname.($parentCommentId ? ' replied to a comment on your ' : ' commented on your ').$postType.': '.$postTitle;
                            $notif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'comment', ?)");
                            $notif->execute([$posterId, $user_id, $postType, $postId, $notifMsg]);
                            $notificationCreated = true;
                        }
                        $mentionedUserIds = \gc_alumni_feed_get_mentioned_user_ids($pdo, $comment, $user_id);
                        if (! empty($mentionedUserIds)) {
                            $mentionNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'mention', ?)");
                            foreach ($mentionedUserIds as $mentionedId) {
                                if ($mentionedId === $posterId) {
                                    continue;
                                }
                                $mentionMsg = $currentFullname.' mentioned you in a comment on '.$postType.': '.($postTitle ?: 'a post');
                                $mentionNotif->execute([$mentionedId, $user_id, $postType, $postId, $mentionMsg]);
                                $notificationCreated = true;
                            }
                        }
                        $counts = \gc_alumni_feed_get_reaction_counts($pdo, $postType, $postId);
                        $comments = \gc_alumni_feed_get_comments($pdo, $postType, $postId);
                        echo json_encode(['success' => true, 'comments_html' => \gc_alumni_feed_render_comments_html($comments, $user_id), 'engagement_html' => \gc_alumni_feed_render_engagement_html($counts, count($comments), $allowedReactions), 'notification_created' => $notificationCreated, 'comment_count' => count($comments)]);
                        \gc_finish();
                    }
                    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
                    \gc_finish();
                } catch (\Throwable $ex) {
                    if ($ex instanceof PageResponse) {
                        throw $ex;
                    }
                    echo json_encode(['success' => false, 'message' => \gc_public_error($ex)]);
                    \gc_finish();
                }
            }
            $feedProfileSelect = $profileColumn ? ", u.`{$profileColumn}` AS poster_photo" : ', NULL AS poster_photo';
            $jobStmt = $pdo->prepare('SELECT id, title, employer_company, location, description FROM jobs WHERE is_open = 1 ORDER BY id DESC LIMIT 5');
            $jobStmt->execute();
            $sidebarJobs = $jobStmt->fetchAll(\PDO::FETCH_ASSOC);
            $feedStmt = $pdo->prepare("\r\n    SELECT\r\n        e.id,\r\n        e.title,\r\n        e.content,\r\n        e.image,\r\n        e.created_at,\r\n        e.posted_by,\r\n        u.fullname AS poster\r\n        {$feedProfileSelect},\r\n        'event' AS post_type,\r\n        NULL AS training_date,\r\n        NULL AS location,\r\n        'Open for All' AS target_course,\r\n        e.post_start_date,\r\n        e.post_end_date\r\n    FROM events e\r\n    JOIN users u ON u.id = e.posted_by\r\n    WHERE e.is_archived = 0\r\n      AND (e.post_start_date IS NULL OR e.post_start_date <= NOW())\r\n      AND (e.post_end_date IS NULL OR e.post_end_date >= NOW())\r\n\r\n    UNION ALL\r\n\r\n    SELECT\r\n        t.id,\r\n        t.title,\r\n        t.content,\r\n        t.image,\r\n        t.created_at,\r\n        t.posted_by,\r\n        u.fullname AS poster\r\n        {$feedProfileSelect},\r\n        'training' AS post_type,\r\n        t.training_date,\r\n        t.location,\r\n        t.target_course,\r\n        NULL AS post_start_date,\r\n        NULL AS post_end_date\r\n    FROM trainings t\r\n    JOIN users u ON u.id = t.posted_by\r\n    WHERE t.target_course = ? OR t.target_course = 'Open for All'\r\n\r\n    ORDER BY created_at DESC, id DESC\r\n");
            $feedStmt->execute([$alumni_course]);
            $feed = $feedStmt->fetchAll(\PDO::FETCH_ASSOC);
            $postData = [];
            foreach ($feed as $item) {
                $key = $item['post_type'].'_'.(int) $item['id'];
                $counts = \gc_alumni_feed_get_reaction_counts($pdo, $item['post_type'], (int) $item['id']);
                $comments = \gc_alumni_feed_get_comments($pdo, $item['post_type'], (int) $item['id']);
                $postData[$key] = ['counts' => $counts, 'comments' => $comments, 'user_reaction' => \gc_alumni_feed_get_user_reaction($pdo, $item['post_type'], (int) $item['id'], $user_id)];
            }
            $mentionUsersStmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> '' ORDER BY fullname ASC");
            $mentionUsers = [];
            foreach ($mentionUsersStmt->fetchAll(\PDO::FETCH_ASSOC) as $mu) {
                $mentionUsers[] = ['id' => (int) $mu['id'], 'name' => (string) $mu['fullname']];
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni.feed', get_defined_vars());
        });
    }
}
