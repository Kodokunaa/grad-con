<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Support\PageResponse;
use Illuminate\Http\Request;

final class AlumniOfficerEventsListController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();

            \gc_require_role('alumni_officer');
            $msg = '';
            $error = '';
            $currentUserId = \gc_alumni_officer_events_list_get_current_user_id();
            $currentFullname = \gc_context()->session['user']['fullname'] ?? \gc_context()->session['fullname'] ?? 'Alumni Officer';
            $profileColumn = \gc_alumni_officer_events_list_get_user_profile_column($pdo);
            $currentUserPhoto = '';
            if ($profileColumn && $currentUserId > 0) {
                try {
                    $photoStmt = $pdo->prepare("SELECT `{$profileColumn}` FROM users WHERE id=? LIMIT 1");
                    $photoStmt->execute([$currentUserId]);
                    $currentUserPhoto = (string) ($photoStmt->fetchColumn() ?: '');
                } catch (\Throwable $e) {
                    if ($e instanceof PageResponse) {
                        throw $e;
                    }
                    $currentUserPhoto = '';
                }
            }
            try {
                \gc_alumni_officer_events_list_ensure_column($pdo, 'events', 'is_archived', 'TINYINT(1) NOT NULL DEFAULT 0');
                \gc_alumni_officer_events_list_ensure_column($pdo, 'events', 'archived_at', 'DATETIME NULL');
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_reactions (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        parent_comment_id INT NULL DEFAULT NULL,\r\n        user_id INT NOT NULL,\r\n        reaction_type VARCHAR(20) NOT NULL DEFAULT 'like',\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        UNIQUE KEY unique_post_user_reaction (post_type, post_id, user_id),\r\n        INDEX idx_post_reactions_post (post_type, post_id),\r\n        INDEX idx_post_reactions_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_comments (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        parent_comment_id INT NULL DEFAULT NULL,\r\n        user_id INT NOT NULL,\r\n        comment TEXT NOT NULL,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_comments_post (post_type, post_id),\r\n        INDEX idx_post_comments_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_notifications (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        recipient_user_id INT NOT NULL,\r\n        sender_user_id INT NOT NULL,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        notification_type VARCHAR(50) NOT NULL DEFAULT 'comment',\r\n        message TEXT NOT NULL,\r\n        is_read TINYINT(1) NOT NULL DEFAULT 0,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_notifications_recipient (recipient_user_id),\r\n        INDEX idx_post_notifications_post (post_type, post_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                foreach (['post_comments' => ['post_type' => "ALTER TABLE post_comments ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => 'ALTER TABLE post_comments ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type', 'parent_comment_id' => 'ALTER TABLE post_comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER post_id', 'user_id' => 'ALTER TABLE post_comments ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER parent_comment_id', 'comment' => 'ALTER TABLE post_comments ADD COLUMN comment TEXT NOT NULL AFTER user_id', 'created_at' => 'ALTER TABLE post_comments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER comment'], 'post_reactions' => ['post_type' => "ALTER TABLE post_reactions ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => 'ALTER TABLE post_reactions ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type', 'user_id' => 'ALTER TABLE post_reactions ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER post_id', 'reaction_type' => "ALTER TABLE post_reactions ADD COLUMN reaction_type VARCHAR(20) NOT NULL DEFAULT 'like' AFTER user_id", 'created_at' => 'ALTER TABLE post_reactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reaction_type']] as $table => $columns) {
                    foreach ($columns as $column => $sql) {
                        if (! \gc_alumni_officer_events_list_column_exists($pdo, $table, $column)) {
                            \gc_context()->schemaChange($pdo, $sql);
                        }
                    }
                }
                if (\gc_alumni_officer_events_list_table_exists($pdo, 'event_reactions')) {
                    \gc_context()->schemaChange($pdo, "INSERT IGNORE INTO post_reactions (post_type, post_id, user_id, reaction_type, created_at)\r\n                    SELECT 'event', event_id, user_id, reaction_type, created_at FROM event_reactions");
                }
                if (\gc_alumni_officer_events_list_table_exists($pdo, 'event_comments')) {
                    \gc_context()->schemaChange($pdo, "INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment, created_at)\r\n                    SELECT 'event', ec.event_id, NULL, ec.user_id, ec.comment, ec.created_at\r\n                    FROM event_comments ec\r\n                    WHERE NOT EXISTS (\r\n                        SELECT 1 FROM post_comments pc\r\n                        WHERE pc.post_type='event'\r\n                          AND pc.post_id=ec.event_id\r\n                          AND pc.user_id=ec.user_id\r\n                          AND pc.comment=ec.comment\r\n                          AND pc.created_at=ec.created_at\r\n                    )");
                }
            } catch (\Throwable $e) {
                if ($e instanceof PageResponse) {
                    throw $e;
                }
                $error = 'Database setup error: '.\gc_public_error($e);
            }
            $allowedReactions = ['like' => ['emoji' => '👍', 'label' => 'Like'], 'love' => ['emoji' => '❤️', 'label' => 'Love'], 'haha' => ['emoji' => '😂', 'label' => 'Haha'], 'angry' => ['emoji' => '😡', 'label' => 'Angry']];
            // React to event using AJAX so the page will NOT refresh, will NOT scroll to top, and will NOT show popup alerts
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['toggle_reaction'])) {
                $eventId = (int) (\gc_context()->post['event_id'] ?? 0);
                $reactionType = (string) (\gc_context()->post['reaction_type'] ?? 'like');
                $isAjaxReaction = isset(\gc_context()->post['ajax_reaction']) && (string) \gc_context()->post['ajax_reaction'] === '1';
                $reactionResponse = ['success' => false, 'message' => '', 'reaction' => '', 'label' => 'Like', 'emoji' => '👍', 'counts' => ['like' => 0, 'love' => 0, 'haha' => 0, 'angry' => 0, 'total' => 0]];
                if ($eventId > 0 && $currentUserId > 0 && isset($allowedReactions[$reactionType])) {
                    try {
                        if (! \gc_alumni_officer_events_list_is_event_visible_on_feed($pdo, $eventId)) {
                            throw new RuntimeException('This event is not yet visible on the feed.');
                        }
                        $existing = \gc_alumni_officer_events_list_get_user_reaction($pdo, 'event', $eventId, $currentUserId);
                        if ($existing === $reactionType) {
                            $del = $pdo->prepare("DELETE FROM post_reactions WHERE post_type='event' AND post_id=? AND user_id=?");
                            $del->execute([$eventId, $currentUserId]);
                            $newReaction = '';
                        } elseif ($existing !== '') {
                            $upd = $pdo->prepare("UPDATE post_reactions SET reaction_type=?, created_at=NOW() WHERE post_type='event' AND post_id=? AND user_id=?");
                            $upd->execute([$reactionType, $eventId, $currentUserId]);
                            $newReaction = $reactionType;
                        } else {
                            $addReaction = $pdo->prepare("INSERT INTO post_reactions (post_type, post_id, user_id, reaction_type) VALUES ('event', ?, ?, ?)");
                            $addReaction->execute([$eventId, $currentUserId, $reactionType]);
                            $newReaction = $reactionType;
                        }
                        $reactionCounts = \gc_alumni_officer_events_list_get_reaction_counts($pdo, 'event', $eventId);
                        $reactionResponse['success'] = true;
                        $reactionResponse['reaction'] = $newReaction;
                        $reactionResponse['label'] = $newReaction && isset($allowedReactions[$newReaction]) ? $allowedReactions[$newReaction]['label'] : 'Like';
                        $reactionResponse['emoji'] = $newReaction && isset($allowedReactions[$newReaction]) ? $allowedReactions[$newReaction]['emoji'] : '👍';
                        $reactionResponse['counts'] = $reactionCounts;
                        // Do not set $msg here. This keeps the page clean and removes the "Reaction updated" popup/message.
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $reactionResponse['message'] = 'Reaction error: '.\gc_public_error($e);
                        if (! $isAjaxReaction) {
                            $error = $reactionResponse['message'];
                        }
                    }
                } else {
                    $reactionResponse['message'] = 'Unable to react. Please make sure you are logged in.';
                    if (! $isAjaxReaction) {
                        $error = $reactionResponse['message'];
                    }
                }
                if ($isAjaxReaction) {
                    \gc_header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($reactionResponse);
                    \gc_finish();
                }
                // Fallback for users without JavaScript: stay near the same post instead of returning to the top.
                if ($eventId > 0 && empty($error)) {
                    \gc_header('Location: '.strtok(\request()->server->all()['REQUEST_URI'], '?').'#event-post-'.$eventId);
                    \gc_finish();
                }
            }
            // Add comment using the SAME table as alumni feed.php
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['add_comment'])) {
                $eventId = (int) (\gc_context()->post['event_id'] ?? 0);
                $comment = trim((string) (\gc_context()->post['comment'] ?? ''));
                $parentId = ! empty(\gc_context()->post['parent_comment_id']) ? (int) \gc_context()->post['parent_comment_id'] : null;
                if ($eventId <= 0) {
                    $error = 'Invalid event selected.';
                } elseif ($currentUserId <= 0) {
                    $error = 'Unable to comment. Please make sure you are logged in.';
                } elseif ($comment === '') {
                    $error = 'Comment cannot be empty.';
                } else {
                    try {
                        if (! \gc_alumni_officer_events_list_is_event_visible_on_feed($pdo, $eventId)) {
                            throw new RuntimeException('This event is not yet visible on the feed.');
                        }
                        if ($parentId) {
                            // Allow replying to a main comment OR replying to an existing reply.
                            // If the selected comment is already a reply, keep the new reply under the original main comment
                            // so the thread stays clean like Facebook nested replies.
                            $checkParent = $pdo->prepare("SELECT id, parent_comment_id FROM post_comments WHERE id=? AND post_type='event' AND post_id=? LIMIT 1");
                            $checkParent->execute([$parentId, $eventId]);
                            $parentRow = $checkParent->fetch(\PDO::FETCH_ASSOC);
                            if (! $parentRow) {
                                throw new RuntimeException('Invalid comment reply selected.');
                            }
                            if (! empty($parentRow['parent_comment_id'])) {
                                $parentId = (int) $parentRow['parent_comment_id'];
                            }
                        }
                        $addComment = $pdo->prepare("INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment) VALUES ('event', ?, ?, ?, ?)");
                        $addComment->execute([$eventId, $parentId, $currentUserId, $comment]);
                        $ownerStmt = $pdo->prepare('SELECT posted_by, title FROM events WHERE id=? LIMIT 1');
                        $ownerStmt->execute([$eventId]);
                        $owner = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
                        $posterId = 0;
                        $postTitle = 'your event';
                        if ($owner) {
                            $posterId = (int) ($owner['posted_by'] ?? 0);
                            $postTitle = (string) ($owner['title'] ?? 'your event');
                            if ($posterId > 0 && $posterId !== $currentUserId) {
                                $notifMsg = $parentId ? $currentFullname.' replied to a comment on your event: '.$postTitle : $currentFullname.' commented on your event: '.$postTitle;
                                $notif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, 'event', ?, 'comment', ?)");
                                $notif->execute([$posterId, $currentUserId, $eventId, $notifMsg]);
                            }
                        }
                        $mentionedUserIds = \gc_alumni_officer_events_list_get_mentioned_user_ids($pdo, $comment, $currentUserId);
                        if (! empty($mentionedUserIds)) {
                            $mentionNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, 'event', ?, 'mention', ?)");
                            foreach ($mentionedUserIds as $mentionedId) {
                                if ($mentionedId === $posterId) {
                                    continue;
                                }
                                $mentionMsg = $currentFullname.' mentioned you in a comment on event: '.$postTitle;
                                $mentionNotif->execute([$mentionedId, $currentUserId, $eventId, $mentionMsg]);
                            }
                        }
                        $msg = $parentId ? 'Reply posted successfully.' : 'Comment posted successfully.';
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Comment error: '.\gc_public_error($e);
                    }
                }
            }
            // Delete comment from the shared comments table
            if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['delete_comment'])) {
                $commentId = (int) (\gc_context()->post['comment_id'] ?? 0);
                if ($commentId > 0 && $currentUserId > 0) {
                    try {
                        $findComment = $pdo->prepare("SELECT user_id FROM post_comments WHERE id = ? AND post_type='event' LIMIT 1");
                        $findComment->execute([$commentId]);
                        $commentRow = $findComment->fetch(\PDO::FETCH_ASSOC);
                        if ($commentRow) {
                            $deleteComment = $pdo->prepare("DELETE FROM post_comments WHERE (id = ? OR parent_comment_id = ?) AND post_type='event'");
                            $deleteComment->execute([$commentId, $commentId]);
                            $msg = 'Comment deleted successfully.';
                        } else {
                            $error = 'Comment not found.';
                        }
                    } catch (\Throwable $e) {
                        if ($e instanceof PageResponse) {
                            throw $e;
                        }
                        $error = 'Delete comment error: '.\gc_public_error($e);
                    }
                }
            }
            // Archive event instead of permanently deleting it
            if (isset(\gc_context()->query['delete'])) {
                $delete_id = (int) (\gc_context()->query['delete'] ?? 0);
                if ($delete_id > 0) {
                    $find = $pdo->prepare('SELECT id FROM events WHERE id = ? LIMIT 1');
                    $find->execute([$delete_id]);
                    $event = $find->fetch(\PDO::FETCH_ASSOC);
                    if ($event) {
                        $archiveStmt = $pdo->prepare('UPDATE events SET is_archived = 1, archived_at = NOW() WHERE id = ?');
                        $archiveStmt->execute([$delete_id]);
                        $msg = 'Event archived successfully.';
                    } else {
                        $error = 'Event not found.';
                    }
                }
            }
            $eventProfileSelect = $profileColumn ? ", u.`{$profileColumn}` AS profile_photo" : ', NULL AS profile_photo';
            $stmt = $pdo->query("SELECT e.*, u.fullname {$eventProfileSelect}\r\n                     FROM events e\r\n                     LEFT JOIN users u ON u.id = e.posted_by\r\n                     WHERE e.is_archived = 0\r\n                       AND (e.post_start_date IS NULL OR e.post_start_date <= NOW())\r\n                       AND (e.post_end_date IS NULL OR e.post_end_date >= NOW())\r\n                     ORDER BY e.id DESC");
            $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $postData = [];
            foreach ($events as $event) {
                $eventId = (int) $event['id'];
                $postData[$eventId] = ['counts' => \gc_alumni_officer_events_list_get_reaction_counts($pdo, 'event', $eventId), 'user_reaction' => \gc_alumni_officer_events_list_get_user_reaction($pdo, 'event', $eventId, $currentUserId), 'comments' => \gc_alumni_officer_events_list_get_comments($pdo, 'event', $eventId)];
            }
            $mentionUsersStmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> '' ORDER BY fullname ASC");
            $mentionUsers = [];
            foreach ($mentionUsersStmt->fetchAll(\PDO::FETCH_ASSOC) as $mentionUser) {
                $mentionUsers[] = ['id' => (int) $mentionUser['id'], 'name' => (string) $mentionUser['fullname']];
            }
            echo \gc_partial('header', \get_defined_vars());
            echo \gc_partial('alumni_officer_sidebar', \get_defined_vars());

            return $this->pageView('pages.alumni_officer.events_list', get_defined_vars());
        });
    }
}
