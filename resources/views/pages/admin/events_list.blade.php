1<?php 
null;
null;
null;
\gc_require_role('admin');
$msg = "";
$error = "";
$currentUserId = \gc_admin_events_list_get_current_user_id();
$currentFullname = \gc_context()->session['user']['fullname'] ?? \gc_context()->session['fullname'] ?? 'Admin';
$profileColumn = \gc_admin_events_list_get_user_profile_column($pdo);
$currentUserPhoto = \gc_admin_events_list_get_current_user_photo($pdo, $currentUserId, $profileColumn);
try {
    if (!\gc_admin_events_list_column_exists($pdo, 'events', 'post_start_date')) {
        \gc_context()->schemaChange($pdo, "ALTER TABLE events ADD COLUMN post_start_date DATETIME NULL AFTER created_at");
    }
    if (!\gc_admin_events_list_column_exists($pdo, 'events', 'post_end_date')) {
        \gc_context()->schemaChange($pdo, "ALTER TABLE events ADD COLUMN post_end_date DATETIME NULL AFTER post_start_date");
    }
    if (!\gc_admin_events_list_column_exists($pdo, 'events', 'is_archived')) {
        \gc_context()->schemaChange($pdo, "ALTER TABLE events ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER post_end_date");
    }
    if (!\gc_admin_events_list_column_exists($pdo, 'events', 'archived_at')) {
        \gc_context()->schemaChange($pdo, "ALTER TABLE events ADD COLUMN archived_at DATETIME NULL AFTER is_archived");
    }
    \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_reactions (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        user_id INT NOT NULL,\r\n        reaction_type VARCHAR(20) NOT NULL DEFAULT 'like',\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        UNIQUE KEY unique_post_user_reaction (post_type, post_id, user_id),\r\n        INDEX idx_post_reactions_post (post_type, post_id),\r\n        INDEX idx_post_reactions_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_comments (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        parent_comment_id INT NULL,\r\n        user_id INT NOT NULL,\r\n        comment TEXT NOT NULL,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_comments_post (post_type, post_id),\r\n        INDEX idx_post_comments_parent (parent_comment_id),\r\n        INDEX idx_post_comments_user (user_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    \gc_context()->schemaChange($pdo, "CREATE TABLE IF NOT EXISTS post_notifications (\r\n        id INT AUTO_INCREMENT PRIMARY KEY,\r\n        recipient_user_id INT NOT NULL,\r\n        sender_user_id INT NOT NULL,\r\n        post_type VARCHAR(30) NOT NULL DEFAULT 'event',\r\n        post_id INT NOT NULL,\r\n        notification_type VARCHAR(50) NOT NULL DEFAULT 'comment',\r\n        message TEXT NOT NULL,\r\n        is_read TINYINT(1) NOT NULL DEFAULT 0,\r\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n        INDEX idx_post_notifications_recipient (recipient_user_id),\r\n        INDEX idx_post_notifications_post (post_type, post_id)\r\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    foreach (['post_comments' => ['post_type' => "ALTER TABLE post_comments ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => "ALTER TABLE post_comments ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type", 'parent_comment_id' => "ALTER TABLE post_comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER post_id", 'user_id' => "ALTER TABLE post_comments ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER parent_comment_id", 'comment' => "ALTER TABLE post_comments ADD COLUMN comment TEXT NOT NULL AFTER user_id", 'created_at' => "ALTER TABLE post_comments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER comment"], 'post_reactions' => ['post_type' => "ALTER TABLE post_reactions ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id", 'post_id' => "ALTER TABLE post_reactions ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type", 'user_id' => "ALTER TABLE post_reactions ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER post_id", 'reaction_type' => "ALTER TABLE post_reactions ADD COLUMN reaction_type VARCHAR(20) NOT NULL DEFAULT 'like' AFTER user_id", 'created_at' => "ALTER TABLE post_reactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reaction_type"]] as $table => $columns) {
        foreach ($columns as $column => $sql) {
            if (!\gc_admin_events_list_column_exists($pdo, $table, $column)) {
                \gc_context()->schemaChange($pdo, $sql);
            }
        }
    }
    if (\gc_admin_events_list_table_exists($pdo, 'event_reactions')) {
        \gc_context()->schemaChange($pdo, "INSERT IGNORE INTO post_reactions (post_type, post_id, user_id, reaction_type, created_at)\r\n                    SELECT 'event', event_id, user_id, reaction_type, created_at FROM event_reactions");
    }
    if (\gc_admin_events_list_table_exists($pdo, 'event_comments')) {
        \gc_context()->schemaChange($pdo, "INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment, created_at)\r\n                    SELECT 'event', ec.event_id, NULL, ec.user_id, ec.comment, ec.created_at\r\n                    FROM event_comments ec\r\n                    WHERE NOT EXISTS (\r\n                        SELECT 1 FROM post_comments pc\r\n                        WHERE pc.post_type='event'\r\n                          AND pc.post_id=ec.event_id\r\n                          AND pc.user_id=ec.user_id\r\n                          AND pc.comment=ec.comment\r\n                          AND pc.created_at=ec.created_at\r\n                    )");
    }
} catch (\Throwable $e) {
    if ($e instanceof \App\Support\PageResponse) {
        throw $e;
    }
    $error = "Database setup error: " . $e->getMessage();
}
$allowedReactions = ['like' => ['emoji' => '👍', 'label' => 'Like'], 'love' => ['emoji' => '❤️', 'label' => 'Love'], 'haha' => ['emoji' => '😂', 'label' => 'Haha'], 'angry' => ['emoji' => '😡', 'label' => 'Angry']];
if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['toggle_reaction'])) {
    $isAjaxReaction = isset(\gc_context()->post['ajax_reaction']) || !empty(\request()->server->all()['HTTP_X_REQUESTED_WITH']) && strtolower((string) \request()->server->all()['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $eventId = (int) (\gc_context()->post['event_id'] ?? 0);
    $reactionType = (string) (\gc_context()->post['reaction_type'] ?? 'like');
    $reactionResponse = ['success' => false, 'message' => 'Unable to react. Please make sure you are logged in.'];
    if ($eventId > 0 && $currentUserId > 0 && isset($allowedReactions[$reactionType])) {
        try {
            $visibilityStmt = $pdo->prepare("SELECT post_start_date, post_end_date FROM events WHERE id=? LIMIT 1");
            $visibilityStmt->execute([$eventId]);
            $visibilityRow = $visibilityStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$visibilityRow || !\gc_admin_events_list_event_is_visible_by_schedule($visibilityRow['post_start_date'] ?? null, $visibilityRow['post_end_date'] ?? null)) {
                throw new RuntimeException("This post is not visible yet because its scheduled posting time has not been reached.");
            }
            $existing = \gc_admin_events_list_get_user_reaction($pdo, 'event', $eventId, $currentUserId);
            if ($existing === $reactionType) {
                $del = $pdo->prepare("DELETE FROM post_reactions WHERE post_type='event' AND post_id=? AND user_id=?");
                $del->execute([$eventId, $currentUserId]);
                $newUserReaction = '';
                $reactionResponse['message'] = 'Reaction removed.';
            } elseif ($existing !== '') {
                $upd = $pdo->prepare("UPDATE post_reactions SET reaction_type=?, created_at=NOW() WHERE post_type='event' AND post_id=? AND user_id=?");
                $upd->execute([$reactionType, $eventId, $currentUserId]);
                $newUserReaction = $reactionType;
                $reactionResponse['message'] = 'Reaction updated.';
            } else {
                $addReaction = $pdo->prepare("INSERT INTO post_reactions (post_type, post_id, user_id, reaction_type) VALUES ('event', ?, ?, ?)");
                $addReaction->execute([$eventId, $currentUserId, $reactionType]);
                $newUserReaction = $reactionType;
                $reactionResponse['message'] = 'Reaction added.';
            }
            $reactionResponse['success'] = true;
            $reactionResponse['event_id'] = $eventId;
            $reactionResponse['user_reaction'] = $newUserReaction;
            $reactionResponse['counts'] = \gc_admin_events_list_get_reaction_counts($pdo, 'event', $eventId);
        } catch (\Throwable $e) {
            if ($e instanceof \App\Support\PageResponse) {
                throw $e;
            }
            $reactionResponse['message'] = "Reaction error: " . $e->getMessage();
            if (!$isAjaxReaction) {
                $error = $reactionResponse['message'];
            }
        }
    }
    if ($isAjaxReaction) {
        \gc_header('Content-Type: application/json; charset=utf-8');
        echo json_encode($reactionResponse);
        \gc_finish();
    }
    // Fallback for browsers with JavaScript disabled. No success alert is shown for reactions.
}
if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['add_comment'])) {
    $eventId = (int) (\gc_context()->post['event_id'] ?? 0);
    $parentCommentId = (int) (\gc_context()->post['parent_comment_id'] ?? 0);
    $comment = trim((string) (\gc_context()->post['comment'] ?? ''));
    if ($eventId <= 0) {
        $error = "Invalid event selected.";
    } elseif ($currentUserId <= 0) {
        $error = "Unable to comment. Please make sure you are logged in.";
    } elseif ($comment === '') {
        $error = "Comment cannot be empty.";
    } else {
        try {
            $visibilityStmt = $pdo->prepare("SELECT post_start_date, post_end_date FROM events WHERE id=? LIMIT 1");
            $visibilityStmt->execute([$eventId]);
            $visibilityRow = $visibilityStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$visibilityRow || !\gc_admin_events_list_event_is_visible_by_schedule($visibilityRow['post_start_date'] ?? null, $visibilityRow['post_end_date'] ?? null)) {
                throw new RuntimeException("This post is not visible yet because its scheduled posting time has not been reached.");
            }
            if ($parentCommentId > 0) {
                $parentCheck = $pdo->prepare("SELECT id FROM post_comments WHERE id=? AND post_type='event' AND post_id=? LIMIT 1");
                $parentCheck->execute([$parentCommentId, $eventId]);
                if (!$parentCheck->fetchColumn()) {
                    $parentCommentId = 0;
                }
            }
            $addComment = $pdo->prepare("INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment) VALUES ('event', ?, ?, ?, ?)");
            $addComment->execute([$eventId, $parentCommentId > 0 ? $parentCommentId : null, $currentUserId, $comment]);
            $ownerStmt = $pdo->prepare("SELECT posted_by, title FROM events WHERE id=? LIMIT 1");
            $ownerStmt->execute([$eventId]);
            $owner = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
            $posterId = 0;
            $postTitle = 'your event';
            if ($owner) {
                $posterId = (int) ($owner['posted_by'] ?? 0);
                $postTitle = (string) ($owner['title'] ?? 'your event');
                if ($posterId > 0 && $posterId !== $currentUserId) {
                    $notifMsg = $currentFullname . ($parentCommentId > 0 ? " replied to a comment on your event: " : " commented on your event: ") . $postTitle;
                    $notif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, 'event', ?, 'comment', ?)");
                    $notif->execute([$posterId, $currentUserId, $eventId, $notifMsg]);
                }
            }
            $mentionedUserIds = \gc_admin_events_list_get_mentioned_user_ids($pdo, $comment, $currentUserId);
            if (!empty($mentionedUserIds)) {
                $mentionNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, 'event', ?, 'mention', ?)");
                foreach ($mentionedUserIds as $mentionedId) {
                    if ($mentionedId === $posterId) {
                        continue;
                    }
                    $mentionMsg = $currentFullname . " mentioned you in a comment on event: " . $postTitle;
                    $mentionNotif->execute([$mentionedId, $currentUserId, $eventId, $mentionMsg]);
                }
            }
            $msg = $parentCommentId > 0 ? "Reply posted successfully." : "Comment posted successfully.";
        } catch (\Throwable $e) {
            if ($e instanceof \App\Support\PageResponse) {
                throw $e;
            }
            $error = "Comment error: " . $e->getMessage();
        }
    }
}
if (\request()->server->all()['REQUEST_METHOD'] === 'POST' && isset(\gc_context()->post['delete_comment'])) {
    $commentId = (int) (\gc_context()->post['comment_id'] ?? 0);
    if ($commentId > 0) {
        try {
            $deleteReplies = $pdo->prepare("DELETE FROM post_comments WHERE parent_comment_id = ? AND post_type='event'");
            $deleteReplies->execute([$commentId]);
            $deleteComment = $pdo->prepare("DELETE FROM post_comments WHERE id = ? AND post_type='event'");
            $deleteComment->execute([$commentId]);
            $msg = "Comment deleted successfully.";
        } catch (\Throwable $e) {
            if ($e instanceof \App\Support\PageResponse) {
                throw $e;
            }
            $error = "Delete comment error: " . $e->getMessage();
        }
    }
}
if (isset(\gc_context()->query["delete"])) {
    $delete_id = (int) (\gc_context()->query["delete"] ?? 0);
    if ($delete_id > 0) {
        try {
            $find = $pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
            $find->execute([$delete_id]);
            $event = $find->fetch(\PDO::FETCH_ASSOC);
            if ($event) {
                $archive = $pdo->prepare("UPDATE events SET is_archived = 1, archived_at = NOW() WHERE id = ?");
                $archive->execute([$delete_id]);
                $msg = "Event archived successfully.";
            } else {
                $error = "Event not found.";
            }
        } catch (\Throwable $e) {
            if ($e instanceof \App\Support\PageResponse) {
                throw $e;
            }
            $error = "Archive error: " . $e->getMessage();
        }
    }
}
$profileSelect = $profileColumn ? "u.`{$profileColumn}`" : "NULL";
/*
|--------------------------------------------------------------------------
| Visible Feed Posts Only
|--------------------------------------------------------------------------
| Posts with a future post_start_date will NOT appear yet.
| They become visible automatically once the scheduled date/time is reached.
| Posts with an expired post_end_date are also hidden from the feed.
*/
$eventsStmt = $pdo->query("\r\n    SELECT e.*, u.fullname AS poster, {$profileSelect} AS poster_profile\r\n    FROM events e\r\n    LEFT JOIN users u ON u.id = e.posted_by\r\n        WHERE e.is_archived = 0\r\n            AND (e.post_start_date IS NULL OR e.post_start_date = '' OR e.post_start_date <= NOW())\r\n      AND (e.post_end_date IS NULL OR e.post_end_date = '' OR e.post_end_date >= NOW())\r\n    ORDER BY e.id DESC\r\n");
$events = $eventsStmt->fetchAll(\PDO::FETCH_ASSOC);
$postData = [];
foreach ($events as $event) {
    $eventId = (int) $event['id'];
    $postData[$eventId] = ['counts' => \gc_admin_events_list_get_reaction_counts($pdo, 'event', $eventId), 'user_reaction' => \gc_admin_events_list_get_user_reaction($pdo, 'event', $eventId, $currentUserId), 'comments' => \gc_admin_events_list_get_comments($pdo, 'event', $eventId, $profileSelect)];
}
$mentionUsersStmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> '' ORDER BY fullname ASC");
$mentionUsers = [];
foreach ($mentionUsersStmt->fetchAll(\PDO::FETCH_ASSOC) as $mentionUser) {
    $mentionUsers[] = ['id' => (int) $mentionUser['id'], 'name' => (string) $mentionUser['fullname']];
}
echo \gc_partial('header', \get_defined_vars());
echo \gc_partial('admin_sidebar', \get_defined_vars());
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #f4f6fb;
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        color: #1f2937;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        min-height: 100vh;
        padding: 28px 22px 48px;
    }

    .wall-wrapper {
        max-width: 980px;
        margin: 0 auto;
    }

    .dashboard-hero {
        background:
            radial-gradient(circle at 10% 20%, rgba(255,255,255,.35), transparent 26%),
            linear-gradient(135deg, #111827 0%, #334155 45%, #f97316 100%);
        border-radius: 24px;
        padding: 30px;
        color: #fff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-hero::after {
        content: "";
        position: absolute;
        right: -60px;
        top: -60px;
        width: 190px;
        height: 190px;
        border-radius: 50%;
        background: rgba(255,255,255,.14);
    }

    .hero-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 32px;
        font-weight: 950;
        letter-spacing: -.03em;
        margin-bottom: 7px;
    }

    .hero-subtitle {
        color: rgba(255,255,255,.78);
        font-size: 14px;
        font-weight: 600;
    }

    .hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-primary-orange,
    .btn-light-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        padding: 12px 17px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 900;
        transition: .2s ease;
        white-space: nowrap;
    }

    .btn-primary-orange {
        background: #f97316;
        color: #fff;
        box-shadow: 0 8px 22px rgba(249, 115, 22, .30);
    }

    .btn-primary-orange:hover {
        background: #ea580c;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-light-outline {
        background: rgba(255,255,255,.13);
        color: #fff;
        border: 1px solid rgba(255,255,255,.26);
    }

    .btn-light-outline:hover {
        background: rgba(255,255,255,.22);
        color: #fff;
    }

    .toolbar-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 16px;
        box-shadow: 0 6px 22px rgba(15, 23, 42, .06);
    }

    .toolbar-grid {
        display: grid;
        grid-template-columns: 1fr 180px;
        gap: 12px;
    }

    .search-input,
    .filter-select {
        width: 100%;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        border-radius: 14px;
        padding: 12px 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        transition: .2s ease;
    }

    .search-input:focus,
    .filter-select:focus {
        background: #fff;
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, .13);
    }

    .alert-box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 5px solid;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
        box-shadow: 0 6px 22px rgba(15, 23, 42, .06);
        font-size: 14px;
        font-weight: 800;
    }

    .alert-success-custom { background: #ecfdf5; color: #065f46; border-left-color: #10b981; }
    .alert-danger-custom { background: #fef2f2; color: #7f1d1d; border-left-color: #ef4444; }

    .event-post {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        margin-bottom: 18px;
        overflow: visible;
        box-shadow: 0 8px 30px rgba(15, 23, 42, .07);
        transition: .18s ease;
    }

    .event-post:hover {
        box-shadow: 0 14px 42px rgba(15, 23, 42, .11);
        transform: translateY(-1px);
    }

    .post-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 18px 18px 12px;
    }

    .poster-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        border: 3px solid #fff;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .14);
        background: linear-gradient(135deg, #f97316, #16a34a);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 950;
    }

    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .comment-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
        background: linear-gradient(135deg, #f97316, #16a34a);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 950;
    }

    .comment-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .poster-name {
        font-size: 15px;
        font-weight: 950;
        color: #111827;
        margin: 0;
    }

    .post-meta {
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
        font-weight: 700;
    }

    .post-badges-right {
        display: flex;
        align-items: flex-end;
        flex-direction: column;
        gap: 8px;
    }

    .post-id-badge,
    .status-pill {
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .post-id-badge {
        background: #f3f4f6;
        color: #374151;
    }

    .status-active { background: #ecfdf5; color: #047857; }
    .status-scheduled { background: #eff6ff; color: #1d4ed8; }
    .status-expired { background: #fef2f2; color: #b91c1c; }

    .post-content {
        padding: 0 18px 14px;
    }

    .event-title {
        font-size: 22px;
        font-weight: 950;
        color: #111827;
        margin: 6px 0 10px;
        line-height: 1.25;
    }

    .event-text {
        color: #374151;
        font-size: 14px;
        line-height: 1.65;
        white-space: pre-line;
    }

    .schedule-line {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 13px 0 3px;
    }

    .schedule-line span {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        color: #374151;
        font-size: 12px;
        font-weight: 850;
        padding: 8px 11px;
        border-radius: 999px;
    }

    .event-image-wrap {
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        background: #f9fafb;
    }

    .event-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        display: block;
    }

    .no-image-banner {
        height: 190px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 8px;
        color: #9ca3af;
        background: linear-gradient(135deg, #f9fafb, #eef2f7);
        font-weight: 850;
    }

    .no-image-banner span {
        font-size: 38px;
    }

    .engagement-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 11px 18px;
        color: #6b7280;
        font-size: 13px;
        font-weight: 850;
    }

    .reaction-summary {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .reaction-icons {
        display: inline-flex;
        align-items: center;
    }

    .reaction-icons span {
        width: 23px;
        height: 23px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 2px solid #fff;
        font-size: 13px;
        margin-right: -5px;
        box-shadow: 0 1px 4px rgba(0,0,0,.16);
    }

    .post-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .reaction-form {
        position: relative;
        margin: 0;
        flex: 1 1 0;
        min-width: 0;
    }

    .reaction-picker {
        position: absolute;
        bottom: 47px;
        left: 50%;
        transform: translateX(-50%) scale(.95);
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 30px rgba(0,0,0,.18);
        border-radius: 999px;
        padding: 7px 9px;
        display: flex;
        gap: 7px;
        opacity: 0;
        pointer-events: none;
        transition: .18s ease;
        z-index: 50;
        white-space: nowrap;
    }

    .reaction-form:hover .reaction-picker {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) scale(1);
    }

    .reaction-option {
        border: none;
        background: transparent;
        font-size: 23px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        cursor: pointer;
        transition: .15s ease;
    }

    .reaction-option:hover {
        transform: scale(1.25) translateY(-3px);
        background: #f3f4f6;
    }

    .btn-action {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        border: none;
        border-radius: 12px;
        padding: 10px 12px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        transition: .2s ease;
        width: 100%;
    }

    .btn-comment {
        flex: 1 1 0;
        min-width: 0;
    }

    .post-manage-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
        padding-left: 8px;
        border-left: 1px solid #eef2f7;
    }

    .btn-icon-action {
        width: 38px;
        height: 38px;
        min-width: 38px;
        padding: 0;
        border-radius: 50%;
        font-size: 14px;
        line-height: 1;
        box-shadow: none;
    }

    .btn-icon-action .action-label {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .btn-like { background: #fff; color: #4b5563; }
    .btn-like:hover, .btn-like.active-like { background: #eff6ff; color: #1877f2; }
    .btn-like.active-love { background: #fff1f2; color: #e11d48; }
    .btn-like.active-haha { background: #fefce8; color: #ca8a04; }
    .btn-like.active-angry { background: #fef2f2; color: #dc2626; }

    .btn-comment { background: #fff; color: #4b5563; }
    .btn-comment:hover { background: #f3f4f6; color: #111827; }

    .btn-edit { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .btn-edit:hover { background: #f97316; color: #fff; border-color: #f97316; }

    .btn-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .btn-delete:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

    .comment-section {
        padding: 14px 18px 18px;
        background: #fff;
        border-radius: 0 0 20px 20px;
    }

    .comment-form {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .comment-input-wrap {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 4px 5px 4px 12px;
        transition: .2s ease;
    }

    .comment-input-wrap:focus-within {
        background: #fff;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, .12);
    }

    .comment-input {
        width: 100%;
        border: none;
        background: transparent;
        padding: 8px 6px;
        outline: none;
        font-size: 14px;
    }

    .comment-submit {
        border: none;
        background: #f97316;
        color: #fff;
        border-radius: 999px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        white-space: nowrap;
        transition: .2s ease;
    }

    .comment-submit:hover { background: #ea580c; }

    .comments-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin: 4px 0 10px 46px;
    }

    .view-comments-btn {
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        padding: 0;
    }

    .view-comments-btn:hover {
        color: #f97316;
        text-decoration: underline;
    }

    .comment-hint {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .comments-preview {
        margin-bottom: 10px;
    }

    .comments-collapse {
        display: none;
        padding-top: 2px;
    }

    .comments-collapse.show {
        display: block;
    }

    .comments-collapse.show + .comments-preview,
    .comments-preview.hide-preview {
        display: none;
    }

    .comments-list {
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    .comment-thread {
        width: 100%;
    }

    .comment-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
    }

    .comment-body-wrap {
        flex: 1;
        min-width: 0;
    }

    .comment-bubble {
        display: inline-block;
        max-width: 100%;
        background: #f3f4f6;
        border-radius: 16px;
        padding: 9px 12px;
    }

    .comment-top {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 3px;
    }

    .comment-name {
        font-size: 13px;
        color: #111827;
        font-weight: 950;
    }

    .comment-date {
        color: #6b7280;
        font-size: 11px;
        white-space: nowrap;
    }

    .comment-text {
        font-size: 13px;
        color: #374151;
        line-height: 1.45;
        white-space: pre-line;
        word-break: break-word;
    }

    .comment-tools {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 4px 0 4px 4px;
    }

    .comment-reply-btn,
    .comment-delete-btn {
        background: transparent;
        border: none;
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
        padding: 0;
    }

    .comment-reply-btn { color: #4b5563; }
    .comment-delete-btn { color: #dc2626; }
    .comment-reply-btn:hover,
    .comment-delete-btn:hover { text-decoration: underline; }
    .comment-reply-btn:hover { color: #f97316; }

    .comment-delete-form { margin: 0; }

    .reply-form {
        display: none;
        margin: 8px 0 8px;
    }

    .reply-form.show { display: block; }

    .reply-input-row {
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 620px;
    }

    .reply-input-row .comment-input-wrap {
        margin: 0;
    }

    .reply-avatar {
        width: 30px;
        height: 30px;
        font-size: 11px;
    }

    .reply-submit {
        padding: 8px 12px;
        font-size: 12px;
    }

    .replies-list {
        margin-top: 8px;
        margin-left: 43px;
        padding-left: 12px;
        border-left: 2px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .reply-item { gap: 8px; }
    .reply-bubble { background: #f8fafc; }

    .no-comments {
        color: #6b7280;
        font-size: 13px;
        font-weight: 800;
        padding-left: 46px;
    }

    .mention-text {
        color: #f97316;
        font-weight: 900;
    }

    .mention-box {
        position: absolute;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 12px 35px rgba(15, 23, 42, .18);
        max-height: 230px;
        overflow-y: auto;
        z-index: 99999;
        min-width: 240px;
        display: none;
        padding: 7px;
    }

    .mention-item {
        width: 100%;
        border: none;
        background: #fff;
        padding: 9px 10px;
        border-radius: 10px;
        text-align: left;
        cursor: pointer;
        font-size: 13px;
        font-weight: 850;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mention-item:hover,
    .mention-item.active {
        background: #fff7ed;
        color: #ea580c;
    }

    .mention-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f97316, #16a34a);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 950;
        flex-shrink: 0;
    }


    .empty-state {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, .07);
        text-align: center;
        padding: 58px 20px;
        color: #6b7280;
    }

    .empty-state-icon { font-size: 52px; margin-bottom: 12px; }
    .empty-state-text { font-size: 19px; color: #111827; font-weight: 950; margin-bottom: 6px; }
    .empty-state-subtext { font-size: 14px; color: #6b7280; font-weight: 650; }

    #noSearchResults { display: none; margin-bottom: 14px; }

    @media (max-width: 991.98px) {
        .content { margin-left: 0; width: 100%; padding: 20px 12px 38px; }
        .hero-row { flex-direction: column; align-items: flex-start; }
        .hero-actions { width: 100%; justify-content: flex-start; }
        .toolbar-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 575.98px) {
        .dashboard-hero { padding: 22px; border-radius: 18px; }
        .hero-title { font-size: 25px; }
        .post-header { flex-direction: column; }
        .post-badges-right { align-items: flex-start; flex-direction: row; flex-wrap: wrap; }
        .post-actions { flex-wrap: wrap; }
        .reaction-form, .btn-comment { flex: 1 1 calc(50% - 8px); }
        .post-manage-actions { width: 100%; justify-content: flex-end; margin-left: 0; padding-left: 0; border-left: none; border-top: 1px solid #f1f5f9; padding-top: 8px; }
        .comment-form, .comment-input-wrap { flex-direction: column; }
        .reply-input-row { align-items: stretch; }
        .reply-submit { width: auto; }
        .comment-submit { width: 100%; }
        .event-title { font-size: 19px; }
    }


    .reaction-form.reaction-open .reaction-picker,
    .reaction-form:focus-within .reaction-picker {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) scale(1);
    }

    .reaction-main-btn.is-loading {
        opacity: .65;
        cursor: wait;
    }

    @media (hover: none), (pointer: coarse) {
        .reaction-form:hover .reaction-picker {
            opacity: 0;
            pointer-events: none;
            transform: translateX(-50%) scale(.95);
        }
        .reaction-form.reaction-open .reaction-picker,
        .reaction-form:focus-within .reaction-picker {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(-50%) scale(1);
        }
    }

</style>

<div class="content">
    <div class="wall-wrapper">
        <section class="dashboard-hero">
            <div class="hero-row">
                <div>
                    <h1 class="hero-title">Event Posts</h1>
                    <div class="hero-subtitle">Review, manage, and monitor event engagement in a professional wall layout.</div>
                </div>

                <div class="hero-actions">
                    <a href="<?php 
echo \url('');
?>/admin/events_create.php" class="btn-primary-orange">+ Create Post</a>
                    <a href="<?php 
echo \url('');
?>/admin/dashboard.php" class="btn-light-outline">Dashboard</a>
                </div>
            </div>
        </section>

        <section class="toolbar-card">
            <div class="toolbar-grid">
                <input type="text" id="eventSearch" class="search-input" placeholder="Search title, content, or poster...">
                <select id="statusFilter" class="filter-select">
                    <option value="all">All Visible Posts</option>
                    <option value="active">Active</option>
                </select>
            </div>
        </section>

        <?php 
if (!empty(\gc_context()->query['deleted'])) {
    ?>
            <div class="alert-box alert-success-custom">Event archived successfully.</div>
        <?php 
}
?>

        <?php 
if ($msg) {
    ?>
            <div class="alert-box alert-success-custom"><?php 
    echo \gc_e($msg);
    ?></div>
        <?php 
}
?>

        <?php 
if ($error) {
    ?>
            <div class="alert-box alert-danger-custom"><?php 
    echo \gc_e($error);
    ?></div>
        <?php 
}
?>

        <div id="noSearchResults" class="empty-state">
            <div class="empty-state-icon">🔎</div>
            <div class="empty-state-text">No matching events found</div>
            <div class="empty-state-subtext">Try another keyword or status filter.</div>
        </div>

        <?php 
if (!$events) {
    ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <div class="empty-state-text">No visible event posts yet</div>
                <div class="empty-state-subtext">Scheduled posts will appear here automatically once their posting time is reached.</div>
            </div>
        <?php 
} else {
    ?>
            <?php 
    foreach ($events as $event) {
        ?>
                <?php 
        $eventId = (int) $event["id"];
        $postedBy = $event["poster"] ?? "Unknown";
        [$statusText, $statusClass] = \gc_admin_events_list_post_status_label($event['post_start_date'] ?? null, $event['post_end_date'] ?? null);
        $statusValue = strtolower($statusText);
        $searchText = strtolower(($event["title"] ?? '') . ' ' . ($event["content"] ?? '') . ' ' . $postedBy . ' ' . $statusText);
        $counts = $postData[$eventId]['counts'];
        $eventComments = $postData[$eventId]['comments'];
        $comments = \gc_admin_events_list_comment_total($eventComments);
        $userReaction = $postData[$eventId]['user_reaction'];
        $reactionLabel = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['label'] : 'Like';
        $reactionEmoji = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['emoji'] : '👍';
        ?>

                <article class="event-post" data-event-id="<?php 
        echo $eventId;
        ?>" data-search="<?php 
        echo \gc_e($searchText);
        ?>" data-status="<?php 
        echo \gc_e($statusValue);
        ?>">
                    <div class="post-header">
                        <div class="poster-info">
                            <?php 
        echo \gc_admin_events_list_render_avatar($postedBy, $event['poster_profile'] ?? '', 'avatar');
        ?>
                            <div>
                                <h4 class="poster-name"><?php 
        echo \gc_e($postedBy);
        ?></h4>
                                <div class="post-meta">
                                    Posted an alumni event • Event ID #<?php 
        echo $eventId;
        ?>
                                    <?php 
        if (!empty($event['created_at'])) {
            ?>
                                        • <?php 
            echo \gc_e(\gc_admin_events_list_format_schedule_date($event['created_at']));
            ?>
                                    <?php 
        }
        ?>
                                </div>
                            </div>
                        </div>

                        <div class="post-badges-right">
                            <div class="post-id-badge">Event</div>
                            <div class="status-pill <?php 
        echo \gc_e($statusClass);
        ?>"><?php 
        echo \gc_e($statusText);
        ?></div>
                        </div>
                    </div>

                    <div class="post-content">
                        <h2 class="event-title"><?php 
        echo \gc_e($event["title"]);
        ?></h2>
                        <div class="event-text"><?php 
        echo nl2br(\gc_e(\gc_admin_events_list_short_text($event["content"], 360)));
        ?></div>

                        <div class="schedule-line">
                            <span>🟢 Start: <?php 
        echo \gc_e(!empty($event['post_start_date']) ? \gc_admin_events_list_format_schedule_date($event['post_start_date']) : 'Immediately');
        ?></span>
                            <span>🔴 End: <?php 
        echo \gc_e(!empty($event['post_end_date']) ? \gc_admin_events_list_format_schedule_date($event['post_end_date']) : 'No end date');
        ?></span>
                        </div>
                    </div>

                    <div class="event-image-wrap">
                        <?php 
        if (!empty($event["image"])) {
            ?>
                            <img src="<?php 
            echo \url('');
            ?>/uploads/events/<?php 
            echo \gc_e($event["image"]);
            ?>" class="event-image" alt="Event Image">
                        <?php 
        } else {
            ?>
                            <div class="no-image-banner">
                                <span>🖼️</span>
                                No event image uploaded
                            </div>
                        <?php 
        }
        ?>
                    </div>

                    <div class="engagement-row">
                        <div class="reaction-summary">
                            <?php 
        if ((int) $counts['total'] > 0) {
            ?>
                                <span class="reaction-icons">
                                    <?php 
            foreach ($allowedReactions as $key => $info) {
                ?>
                                        <?php 
                if (($counts[$key] ?? 0) > 0) {
                    ?>
                                            <span title="<?php 
                    echo \gc_e($info['label']);
                    ?>"><?php 
                    echo \gc_e($info['emoji']);
                    ?></span>
                                        <?php 
                }
                ?>
                                    <?php 
            }
            ?>
                                </span>
                                <span><?php 
            echo number_format((int) $counts['total']);
            ?> Reaction<?php 
            echo (int) $counts['total'] === 1 ? '' : 's';
            ?></span>
                            <?php 
        } else {
            ?>
                                <span>Be the first to react</span>
                            <?php 
        }
        ?>
                        </div>

                        <button type="button" class="view-comments-btn" onclick="toggleCommentsBox('comments-box-<?php 
        echo $eventId;
        ?>')"><?php 
        echo number_format($comments);
        ?> Comment<?php 
        echo $comments === 1 ? '' : 's';
        ?></button>
                    </div>

                    <div class="post-actions">
                        <form method="POST" action="" class="reaction-form" data-reaction-form data-event-id="<?php 
        echo $eventId;
        ?>">
                            <input type="hidden" name="event_id" value="<?php 
        echo $eventId;
        ?>">
                            <input type="hidden" name="toggle_reaction" value="1">
                            <input type="hidden" name="ajax_reaction" value="1">

                            <div class="reaction-picker">
                                <?php 
        foreach ($allowedReactions as $reactionKey => $reactionInfo) {
            ?>
                                    <button type="submit" name="reaction_type" value="<?php 
            echo \gc_e($reactionKey);
            ?>" class="reaction-option" title="<?php 
            echo \gc_e($reactionInfo['label']);
            ?>" data-reaction="<?php 
            echo \gc_e($reactionKey);
            ?>">
                                        <?php 
            echo \gc_e($reactionInfo['emoji']);
            ?>
                                    </button>
                                <?php 
        }
        ?>
                            </div>

                            <button type="submit" name="reaction_type" value="<?php 
        echo \gc_e($userReaction ?: 'like');
        ?>" class="btn-action btn-like reaction-main-btn <?php 
        echo $userReaction ? 'active-' . \gc_e($userReaction) : '';
        ?>" data-current-reaction="<?php 
        echo \gc_e($userReaction ?: 'like');
        ?>">
                                <span class="main-reaction-emoji"><?php 
        echo \gc_e($reactionEmoji);
        ?></span> <span class="main-reaction-label"><?php 
        echo \gc_e($reactionLabel);
        ?></span>
                            </button>
                        </form>

                        <button type="button" class="btn-action btn-comment" onclick="focusComment(<?php 
        echo $eventId;
        ?>); showCommentsBox('comments-box-<?php 
        echo $eventId;
        ?>')">💬 Comment</button>

                        <div class="post-manage-actions" aria-label="Post management actions">
                            <a href="<?php 
        echo \url('');
        ?>/admin/events_edit.php?id=<?php 
        echo $eventId;
        ?>" class="btn-action btn-icon-action btn-edit" title="Edit event" aria-label="Edit event">✏️<span class="action-label">Edit</span></a>
                            <a href="<?php 
        echo \url('');
        ?>/admin/events_list.php?delete=<?php 
        echo $eventId;
        ?>" class="btn-action btn-icon-action btn-delete" title="Archive event" aria-label="Archive event" onclick="return confirm('Archive this event post? It will be removed from the active event list.');">🗄<span class="action-label">Archive</span></a>
                        </div>
                    </div>

                    <div class="comment-section">
                        <form method="POST" action="" class="comment-form">
                            <?php 
        echo \gc_admin_events_list_render_avatar($currentFullname, $currentUserPhoto, 'comment-avatar');
        ?>

                            <div class="comment-input-wrap">
                                <input type="hidden" name="event_id" value="<?php 
        echo $eventId;
        ?>">
                                <input type="text" id="comment-input-<?php 
        echo $eventId;
        ?>" name="comment" class="comment-input" placeholder="Write a comment..." autocomplete="off">
                                <button type="submit" name="add_comment" class="comment-submit">Post</button>
                            </div>
                        </form>

                        <?php 
        if (empty($eventComments['comments'])) {
            ?>
                            <div class="no-comments">No comments yet. Be the first to comment.</div>
                        <?php 
        } else {
            ?>
                            <?php 
            $commentsBoxId = 'comments-box-' . $eventId;
            $previewComment = $eventComments['comments'][0];
            $previewCommentName = $previewComment['fullname'] ?? 'Unknown User';
            $hiddenComments = max(0, $comments - 1);
            ?>

                            <div class="comments-toolbar">
                                <button type="button"
                                        class="view-comments-btn"
                                        onclick="toggleCommentsBox('<?php 
            echo \gc_e($commentsBoxId);
            ?>', this)"
                                        data-open-text="Hide comments"
                                        data-closed-text="View all <?php 
            echo number_format($comments);
            ?> comment<?php 
            echo $comments === 1 ? '' : 's';
            ?>">
                                    View all <?php 
            echo number_format($comments);
            ?> comment<?php 
            echo $comments === 1 ? '' : 's';
            ?>
                                </button>
                                <?php 
            if ($hiddenComments > 0) {
                ?>
                                    <span class="comment-hint"><?php 
                echo number_format($hiddenComments);
                ?> hidden</span>
                                <?php 
            }
            ?>
                            </div>

                            <div class="comments-collapse" id="<?php 
            echo \gc_e($commentsBoxId);
            ?>">
                                <div class="comments-list">
                                    <?php 
            foreach ($eventComments['comments'] as $comment) {
                ?>
                                        <?php 
                $commentName = $comment['fullname'] ?? 'Unknown User';
                $commentId = (int) $comment['id'];
                $replyBoxId = 'reply-box-' . $eventId . '-' . $commentId;
                ?>
                                        <div class="comment-thread">
                                            <div class="comment-item">
                                                <?php 
                echo \gc_admin_events_list_render_avatar($commentName, $comment['profile_image'] ?? '', 'comment-avatar');
                ?>

                                                <div class="comment-body-wrap">
                                                    <div class="comment-bubble">
                                                        <div class="comment-top">
                                                            <div class="comment-name"><?php 
                echo \gc_e($commentName);
                ?></div>
                                                            <div class="comment-date"><?php 
                echo \gc_e(date('M d, Y h:i A', strtotime($comment['created_at'] ?? 'now')));
                ?></div>
                                                        </div>
                                                        <div class="comment-text"><?php 
                echo \gc_admin_events_list_render_comment_text_with_mentions($comment['comment'] ?? '');
                ?></div>
                                                    </div>

                                                    <div class="comment-tools">
                                                        <button type="button" class="comment-reply-btn" onclick="toggleReplyBox('<?php 
                echo \gc_e($replyBoxId);
                ?>')">Reply</button>
                                                        <form method="POST" action="" class="comment-delete-form" onsubmit="return confirm('Delete this comment and its replies?');">
                                                            <input type="hidden" name="comment_id" value="<?php 
                echo $commentId;
                ?>">
                                                            <button type="submit" name="delete_comment" class="comment-delete-btn">Delete</button>
                                                        </form>
                                                    </div>

                                                    <form method="POST" action="" class="reply-form" id="<?php 
                echo \gc_e($replyBoxId);
                ?>">
                                                        <input type="hidden" name="event_id" value="<?php 
                echo $eventId;
                ?>">
                                                        <input type="hidden" name="parent_comment_id" value="<?php 
                echo $commentId;
                ?>">
                                                        <div class="reply-input-row">
                                                            <?php 
                echo \gc_admin_events_list_render_avatar($currentFullname, $currentUserPhoto, 'comment-avatar reply-avatar');
                ?>
                                                            <div class="comment-input-wrap">
                                                                <input type="text" name="comment" class="comment-input" placeholder="Write a reply..." autocomplete="off">
                                                                <button type="submit" name="add_comment" class="comment-submit reply-submit">Reply</button>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    <?php 
                if (!empty($eventComments['replies'][$commentId])) {
                    ?>
                                                        <div class="replies-list">
                                                            <?php 
                    foreach ($eventComments['replies'][$commentId] as $reply) {
                        ?>
                                                                <?php 
                        $replyName = $reply['fullname'] ?? 'Unknown User';
                        ?>
                                                                <div class="comment-item reply-item">
                                                                    <?php 
                        echo \gc_admin_events_list_render_avatar($replyName, $reply['profile_image'] ?? '', 'comment-avatar reply-avatar');
                        ?>

                                                                    <div class="comment-body-wrap">
                                                                        <div class="comment-bubble reply-bubble">
                                                                            <div class="comment-top">
                                                                                <div class="comment-name"><?php 
                        echo \gc_e($replyName);
                        ?></div>
                                                                                <div class="comment-date"><?php 
                        echo \gc_e(date('M d, Y h:i A', strtotime($reply['created_at'] ?? 'now')));
                        ?></div>
                                                                            </div>
                                                                            <div class="comment-text"><?php 
                        echo \gc_admin_events_list_render_comment_text_with_mentions($reply['comment'] ?? '');
                        ?></div>
                                                                        </div>

                                                                        <?php 
                        $replyReplyBoxId = 'reply-box-' . $eventId . '-' . $commentId . '-' . (int) $reply['id'];
                        ?>
                                                                        <div class="comment-tools">
                                                                            <button type="button" class="comment-reply-btn" onclick="toggleReplyBox('<?php 
                        echo \gc_e($replyReplyBoxId);
                        ?>', '@<?php 
                        echo \gc_e($replyName);
                        ?> ')">Reply</button>
                                                                            <form method="POST" action="" class="comment-delete-form" onsubmit="return confirm('Delete this reply?');">
                                                                                <input type="hidden" name="comment_id" value="<?php 
                        echo (int) $reply['id'];
                        ?>">
                                                                                <button type="submit" name="delete_comment" class="comment-delete-btn">Delete</button>
                                                                            </form>
                                                                        </div>

                                                                        <form method="POST" action="" class="reply-form" id="<?php 
                        echo \gc_e($replyReplyBoxId);
                        ?>">
                                                                            <input type="hidden" name="event_id" value="<?php 
                        echo $eventId;
                        ?>">
                                                                            <input type="hidden" name="parent_comment_id" value="<?php 
                        echo $commentId;
                        ?>">
                                                                            <div class="reply-input-row">
                                                                                <?php 
                        echo \gc_admin_events_list_render_avatar($currentFullname, $currentUserPhoto, 'comment-avatar reply-avatar');
                        ?>
                                                                                <div class="comment-input-wrap">
                                                                                    <input type="text" name="comment" class="comment-input" placeholder="Write a reply..." autocomplete="off">
                                                                                    <button type="submit" name="add_comment" class="comment-submit reply-submit">Reply</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            <?php 
                    }
                    ?>
                                                        </div>
                                                    <?php 
                }
                ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
            }
            ?>
                                </div>
                            </div>

                            <div class="comments-preview">
                                <div class="comment-item">
                                    <?php 
            echo \gc_admin_events_list_render_avatar($previewCommentName, $previewComment['profile_image'] ?? '', 'comment-avatar');
            ?>
                                    <div class="comment-body-wrap">
                                        <div class="comment-bubble">
                                            <div class="comment-top">
                                                <div class="comment-name"><?php 
            echo \gc_e($previewCommentName);
            ?></div>
                                                <div class="comment-date"><?php 
            echo \gc_e(date('M d, Y h:i A', strtotime($previewComment['created_at'] ?? 'now')));
            ?></div>
                                            </div>
                                            <div class="comment-text"><?php 
            echo \gc_admin_events_list_render_comment_text_with_mentions(\gc_admin_events_list_short_text($previewComment['comment'] ?? '', 120));
            ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php 
        }
        ?>                    </div>
                </article>
            <?php 
    }
    ?>
        <?php 
}
?>
    </div>
</div>

<div class="mention-box" id="mentionBox"></div>

<script>
const mentionUsers = <?php 
echo json_encode($mentionUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>;
const mentionBox = document.getElementById('mentionBox');
let activeMentionInput = null;
let activeMentionStart = -1;

function mentionInitials(name) {
    return String(name || 'U').trim().split(/\s+/).slice(0, 2).map(function(part) { return part.charAt(0).toUpperCase(); }).join('') || 'U';
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(char) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]);
    });
}

function getMentionInfo(input) {
    const pos = input.selectionStart || 0;
    const value = input.value || '';
    const before = value.substring(0, pos);
    const at = before.lastIndexOf('@');
    if (at < 0) return null;
    if (at > 0 && /\S/.test(before.charAt(at - 1))) return null;
    const query = before.substring(at + 1);
    if (/\n/.test(query) || query.length > 45) return null;
    return { start: at, query: query.toLowerCase() };
}

function positionMentionBox(input) {
    if (!mentionBox) return;
    const rect = input.getBoundingClientRect();
    mentionBox.style.left = (rect.left + window.scrollX) + 'px';
    mentionBox.style.top = (rect.bottom + window.scrollY + 6) + 'px';
    mentionBox.style.width = Math.max(rect.width, 240) + 'px';
}

function showMentionSuggestions(input) {
    if (!mentionBox) return;
    const info = getMentionInfo(input);
    if (!info) {
        mentionBox.style.display = 'none';
        return;
    }

    activeMentionInput = input;
    activeMentionStart = info.start;

    const matches = mentionUsers.filter(function(user) {
        return String(user.name || '').toLowerCase().includes(info.query);
    }).slice(0, 8);

    if (matches.length === 0) {
        mentionBox.style.display = 'none';
        return;
    }

    mentionBox.innerHTML = matches.map(function(user) {
        return '<button type="button" class="mention-item" data-name="' + escapeHtml(user.name) + '">' +
               '<span class="mention-avatar">' + mentionInitials(user.name) + '</span>' +
               '<span>' + escapeHtml(user.name) + '</span>' +
               '</button>';
    }).join('');

    positionMentionBox(input);
    mentionBox.style.display = 'block';
}

function insertMention(name) {
    if (!activeMentionInput || activeMentionStart < 0) return;
    const input = activeMentionInput;
    const pos = input.selectionStart || 0;
    const value = input.value || '';
    const before = value.substring(0, activeMentionStart);
    const after = value.substring(pos);
    const mentionText = '@' + name + ' ';
    input.value = before + mentionText + after;
    const newPos = (before + mentionText).length;
    input.focus();
    input.setSelectionRange(newPos, newPos);
    mentionBox.style.display = 'none';
}

document.addEventListener('input', function(ev) {
    const input = ev.target.closest('.comment-input');
    if (!input) return;
    showMentionSuggestions(input);
});

document.addEventListener('keyup', function(ev) {
    const input = ev.target.closest('.comment-input');
    if (!input) return;
    showMentionSuggestions(input);
});

document.addEventListener('click', function(ev) {
    const item = ev.target.closest('.mention-item');
    if (item) {
        ev.preventDefault();
        insertMention(item.dataset.name || '');
        return;
    }

    if (!ev.target.closest('#mentionBox') && !ev.target.closest('.comment-input')) {
        if (mentionBox) mentionBox.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('eventSearch');
    const statusFilter = document.getElementById('statusFilter');
    const posts = Array.from(document.querySelectorAll('.event-post'));
    const noSearchResults = document.getElementById('noSearchResults');

    function filterEvents() {
        const keyword = (searchInput?.value || '').trim().toLowerCase();
        const status = statusFilter?.value || 'all';
        let visibleCount = 0;

        posts.forEach(function (post) {
            const text = post.dataset.search || '';
            const postStatus = post.dataset.status || '';
            const keywordMatched = keyword === '' || text.includes(keyword);
            const statusMatched = status === 'all' || postStatus === status;
            const matched = keywordMatched && statusMatched;

            post.style.display = matched ? '' : 'none';

            if (matched) visibleCount++;
        });

        if (noSearchResults) {
            noSearchResults.style.display = posts.length > 0 && visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterEvents);
    if (statusFilter) statusFilter.addEventListener('change', filterEvents);
});

function updateCommentPreview(commentsBoxId, isOpen) {
    const box = document.getElementById(commentsBoxId);
    if (!box) return;
    const preview = box.nextElementSibling;
    if (preview && preview.classList.contains('comments-preview')) {
        preview.classList.toggle('hide-preview', isOpen);
    }
}

function showCommentsBox(commentsBoxId) {
    const box = document.getElementById(commentsBoxId);
    if (!box) return;
    box.classList.add('show');
    updateCommentPreview(commentsBoxId, true);
    const btn = document.querySelector('[onclick*="' + commentsBoxId + '"][data-open-text]');
    if (btn) btn.textContent = btn.dataset.openText || 'Hide comments';
}

function toggleCommentsBox(commentsBoxId, btn = null) {
    const box = document.getElementById(commentsBoxId);
    if (!box) return;
    box.classList.toggle('show');
    const isOpen = box.classList.contains('show');
    updateCommentPreview(commentsBoxId, isOpen);
    const targetBtn = btn || document.querySelector('[onclick*="' + commentsBoxId + '"][data-open-text]');
    if (targetBtn && targetBtn.dataset) {
        targetBtn.textContent = isOpen
            ? (targetBtn.dataset.openText || 'Hide comments')
            : (targetBtn.dataset.closedText || 'View comments');
    }
    if (isOpen) {
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function toggleReplyBox(replyBoxId, prefillText = '') {
    const box = document.getElementById(replyBoxId);
    if (!box) return;
    box.classList.toggle('show');
    if (box.classList.contains('show')) {
        const input = box.querySelector('input[name="comment"]');
        if (input) {
            if (prefillText && input.value.trim() === '') {
                input.value = prefillText;
            }
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showMentionSuggestions(input);
        }
    }
}

function focusComment(eventId) {
    const input = document.getElementById('comment-input-' + eventId);

    if (input) {
        input.focus();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}


const allowedReactionMap = <?php 
echo json_encode($allowedReactions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>;

function buildReactionSummaryHtml(counts) {
    counts = counts || {total: 0};
    const total = parseInt(counts.total || 0, 10);
    if (total <= 0) {
        return '<span>Be the first to react</span>';
    }

    let icons = '';
    Object.keys(allowedReactionMap).forEach(function(key) {
        if (parseInt(counts[key] || 0, 10) > 0) {
            icons += '<span title="' + escapeHtml(allowedReactionMap[key].label) + '">' + escapeHtml(allowedReactionMap[key].emoji) + '</span>';
        }
    });

    return '<span class="reaction-icons">' + icons + '</span>' +
           '<span>' + total.toLocaleString() + ' Reaction' + (total === 1 ? '' : 's') + '</span>';
}

function updateReactionUi(form, data) {
    const post = form.closest('.event-post');
    const mainBtn = form.querySelector('.reaction-main-btn');
    const emojiEl = mainBtn ? mainBtn.querySelector('.main-reaction-emoji') : null;
    const labelEl = mainBtn ? mainBtn.querySelector('.main-reaction-label') : null;
    const reaction = data.user_reaction || '';
    const info = reaction && allowedReactionMap[reaction] ? allowedReactionMap[reaction] : { emoji: '👍', label: 'Like' };

    if (mainBtn) {
        mainBtn.classList.remove('active-like', 'active-love', 'active-haha', 'active-angry', 'is-loading');
        if (reaction) mainBtn.classList.add('active-' + reaction);
        mainBtn.value = reaction || 'like';
        mainBtn.dataset.currentReaction = reaction || 'like';
    }
    if (emojiEl) emojiEl.textContent = info.emoji;
    if (labelEl) labelEl.textContent = info.label;

    if (post) {
        const summary = post.querySelector('.reaction-summary');
        if (summary) summary.innerHTML = buildReactionSummaryHtml(data.counts || {});
    }

    form.classList.remove('reaction-open');
}

document.addEventListener('submit', async function(ev) {
    const form = ev.target.closest('form[data-reaction-form]');
    if (!form) return;

    ev.preventDefault();

    const submitter = ev.submitter;
    const reactionType = submitter && submitter.name === 'reaction_type'
        ? submitter.value
        : (form.querySelector('.reaction-main-btn')?.value || 'like');

    const mainBtn = form.querySelector('.reaction-main-btn');
    if (mainBtn) mainBtn.classList.add('is-loading');

    const body = new FormData(form);
    body.set('reaction_type', reactionType);
    body.set('toggle_reaction', '1');
    body.set('ajax_reaction', '1');

    try {
        const response = await fetch(form.action || window.location.href, {
            method: 'POST',
            body: body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (!data.success) {
            if (mainBtn) mainBtn.classList.remove('is-loading');
            console.warn(data.message || 'Reaction failed.');
            return;
        }
        updateReactionUi(form, data);
    } catch (error) {
        if (mainBtn) mainBtn.classList.remove('is-loading');
        console.error('Reaction request failed:', error);
    }
});

document.addEventListener('click', function(ev) {
    const mainBtn = ev.target.closest('.reaction-main-btn');
    if (mainBtn) {
        const form = mainBtn.closest('form[data-reaction-form]');
        if (form && window.matchMedia('(hover: none), (pointer: coarse)').matches) {
            // First tap opens the reaction choices on mobile. Second tap submits the current/default reaction.
            if (!form.classList.contains('reaction-open')) {
                ev.preventDefault();
                form.classList.add('reaction-open');
                return;
            }
        }
    }

    if (!ev.target.closest('form[data-reaction-form]')) {
        document.querySelectorAll('form[data-reaction-form].reaction-open').forEach(function(form) {
            form.classList.remove('reaction-open');
        });
    }
});

</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());