<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_alumni();

function e($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function format_post_date($date) {
    if (!$date) return '';
    $time = strtotime($date);
    if (!$time) return e($date);
    return date('F d, Y \\a\\t h:i A', $time);
}

function shorten_text($text, $limit = 120): string {
    $text = trim(strip_tags((string)($text ?? '')));
    if ($text === '') return 'No description provided.';
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit) . '...' : $text;
    }
    return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

function initials($name) {
    $name = trim((string)$name);
    if ($name === '') return 'U';
    $parts = preg_split('/\\s+/', $name);
    $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';
    return e($first . $last);
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

function table_exists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

function get_user_profile_column(PDO $pdo): ?string {
    $possibleColumns = ['profile_picture','profile_image','profile_photo','photo','avatar','image','picture'];
    foreach ($possibleColumns as $column) {
        try {
            if (column_exists($pdo, 'users', $column)) {
                return $column;
            }
        } catch (Throwable $e) {}
    }
    return null;
}

function profile_image_url($photo): string {
    $photo = trim((string)($photo ?? ''));
    if ($photo === '') return '';

    if (preg_match('/^https?:\/\//i', $photo)) {
        return $photo;
    }

    $cleanPhoto = ltrim($photo, '/');

    if (strpos($cleanPhoto, 'uploads/') !== false) {
        return BASE_URL . '/' . $cleanPhoto;
    }

    return BASE_URL . '/uploads/profiles/' . $cleanPhoto;
}

function avatar_html($name, $photo = null, string $class = 'avatar'): string {
    $url = profile_image_url($photo);
    $safeName = e($name ?: 'User');

    if ($url !== '') {
        return '<div class="' . e($class) . ' has-photo"><img src="' . e($url) . '" alt="' . $safeName . ' profile photo" onerror="this.style.display=\'none\'; this.parentElement.classList.remove(\'has-photo\'); this.parentElement.querySelector(\'.avatar-fallback\').style.display=\'flex\';"><span class="avatar-fallback" style="display:none;">' . initials($name) . '</span></div>';
    }

    return '<div class="' . e($class) . '"><span class="avatar-fallback">' . initials($name) . '</span></div>';
}


function render_comment_text_with_mentions($text): string {
    $safe = e($text ?? '');
    $safe = preg_replace('/@([A-Za-z0-9_ .\-]+)/u', '<span class="mention-text">@$1</span>', $safe);
    return nl2br($safe);
}

function get_mentioned_user_ids(PDO $pdo, string $comment, int $currentUserId): array {
    preg_match_all('/@([A-Za-z0-9_ .\-]+)/u', $comment, $matches);
    if (empty($matches[1])) return [];

    $names = [];
    foreach ($matches[1] as $name) {
        $clean = trim(preg_replace('/\s+/', ' ', $name));
        if ($clean !== '') $names[] = mb_strtolower($clean);
    }
    $names = array_unique($names);
    if (!$names) return [];

    $stmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> ''");
    $mentioned = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $user) {
        $full = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)$user['fullname'])));
        foreach ($names as $name) {
            if ($full === $name || strpos($full, $name) !== false || strpos($name, $full) !== false) {
                $uid = (int)$user['id'];
                if ($uid > 0 && $uid !== $currentUserId) $mentioned[$uid] = $uid;
            }
        }
    }
    return array_values($mentioned);
}

$user_id = (int)($_SESSION['user']['id'] ?? 0);
$profileColumn = get_user_profile_column($pdo);
$currentUserPhoto = '';
$userProfileSelect = $profileColumn ? ", `$profileColumn` AS profile_photo" : ", NULL AS profile_photo";

$userStmt = $pdo->prepare("SELECT id, fullname, course $userProfileSelect FROM users WHERE id=? AND role='alumni' LIMIT 1");
$userStmt->execute([$user_id]);
$currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("Unauthorized access.");
}

$alumni_course = trim($currentUser['course'] ?? '');
$currentFullname = $currentUser['fullname'] ?? 'Alumni';
$currentUserPhoto = $currentUser['profile_photo'] ?? '';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS post_reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_type VARCHAR(30) NOT NULL DEFAULT 'event',
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        reaction_type VARCHAR(20) NOT NULL DEFAULT 'like',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_post_user_reaction (post_type, post_id, user_id),
        INDEX idx_post_reactions_post (post_type, post_id),
        INDEX idx_post_reactions_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS post_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_type VARCHAR(30) NOT NULL DEFAULT 'event',
        post_id INT NOT NULL,
        parent_comment_id INT NULL DEFAULT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post_comments_post (post_type, post_id),
        INDEX idx_post_comments_parent (parent_comment_id),
        INDEX idx_post_comments_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS post_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient_user_id INT NOT NULL,
        sender_user_id INT NOT NULL,
        post_type VARCHAR(30) NOT NULL DEFAULT 'event',
        post_id INT NOT NULL,
        notification_type VARCHAR(50) NOT NULL DEFAULT 'comment',
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_post_notifications_recipient (recipient_user_id),
        INDEX idx_post_notifications_post (post_type, post_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    foreach ([
        'post_comments' => [
            'post_type' => "ALTER TABLE post_comments ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id",
            'post_id' => "ALTER TABLE post_comments ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type",
            'parent_comment_id' => "ALTER TABLE post_comments ADD COLUMN parent_comment_id INT NULL DEFAULT NULL AFTER post_id",
            'user_id' => "ALTER TABLE post_comments ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER parent_comment_id",
            'comment' => "ALTER TABLE post_comments ADD COLUMN comment TEXT NOT NULL AFTER user_id",
            'created_at' => "ALTER TABLE post_comments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER comment",
        ],
        'post_reactions' => [
            'post_type' => "ALTER TABLE post_reactions ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER id",
            'post_id' => "ALTER TABLE post_reactions ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type",
            'user_id' => "ALTER TABLE post_reactions ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER post_id",
            'reaction_type' => "ALTER TABLE post_reactions ADD COLUMN reaction_type VARCHAR(20) NOT NULL DEFAULT 'like' AFTER user_id",
            'created_at' => "ALTER TABLE post_reactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reaction_type",
        ],
        'post_notifications' => [
            'recipient_user_id' => "ALTER TABLE post_notifications ADD COLUMN recipient_user_id INT NOT NULL DEFAULT 0 AFTER id",
            'sender_user_id' => "ALTER TABLE post_notifications ADD COLUMN sender_user_id INT NOT NULL DEFAULT 0 AFTER recipient_user_id",
            'post_type' => "ALTER TABLE post_notifications ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'event' AFTER sender_user_id",
            'post_id' => "ALTER TABLE post_notifications ADD COLUMN post_id INT NOT NULL DEFAULT 0 AFTER post_type",
            'notification_type' => "ALTER TABLE post_notifications ADD COLUMN notification_type VARCHAR(50) NOT NULL DEFAULT 'comment' AFTER post_id",
            'message' => "ALTER TABLE post_notifications ADD COLUMN message TEXT NOT NULL AFTER notification_type",
            'is_read' => "ALTER TABLE post_notifications ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message",
            'created_at' => "ALTER TABLE post_notifications ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_read",
        ],
    ] as $table => $columns) {
        foreach ($columns as $column => $sql) {
            if (!column_exists($pdo, $table, $column)) {
                $pdo->exec($sql);
            }
        }
    }

    if (table_exists($pdo, 'event_reactions')) {
        $pdo->exec("INSERT IGNORE INTO post_reactions (post_type, post_id, user_id, reaction_type, created_at)
                    SELECT 'event', event_id, user_id, reaction_type, created_at FROM event_reactions");
    }

    if (table_exists($pdo, 'event_comments')) {
        $pdo->exec("INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment, created_at)
                    SELECT 'event', ec.event_id, NULL, ec.user_id, ec.comment, ec.created_at
                    FROM event_comments ec
                    WHERE NOT EXISTS (
                        SELECT 1 FROM post_comments pc
                        WHERE pc.post_type='event'
                          AND pc.post_id=ec.event_id
                          AND pc.user_id=ec.user_id
                          AND pc.comment=ec.comment
                          AND pc.created_at=ec.created_at
                    )");
    }
} catch (Throwable $ex) {
    die("Database setup error: " . e($ex->getMessage()));
}

$allowedReactions = [
    'like'  => ['emoji' => '👍', 'label' => 'Like'],
    'love'  => ['emoji' => '❤️', 'label' => 'Love'],
    'haha'  => ['emoji' => '😂', 'label' => 'Haha'],
    'angry' => ['emoji' => '😡', 'label' => 'Angry'],
];

function get_reaction_counts(PDO $pdo, string $postType, int $postId): array {
    $stmt = $pdo->prepare("SELECT reaction_type, COUNT(*) AS total FROM post_reactions WHERE post_type=? AND post_id=? GROUP BY reaction_type");
    $stmt->execute([$postType, $postId]);
    $counts = ['like' => 0, 'love' => 0, 'haha' => 0, 'angry' => 0, 'total' => 0];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = $row['reaction_type'];
        $total = (int)$row['total'];
        if (isset($counts[$type])) {
            $counts[$type] = $total;
            $counts['total'] += $total;
        }
    }
    return $counts;
}

function get_user_reaction(PDO $pdo, string $postType, int $postId, int $userId): string {
    $stmt = $pdo->prepare("SELECT reaction_type FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=? LIMIT 1");
    $stmt->execute([$postType, $postId, $userId]);
    return (string)($stmt->fetchColumn() ?: '');
}

function get_comments(PDO $pdo, string $postType, int $postId): array {
    $profileColumn = get_user_profile_column($pdo);
    $profileSelect = $profileColumn ? ", u.`$profileColumn` AS profile_photo" : ", NULL AS profile_photo";

    $stmt = $pdo->prepare("SELECT c.*, u.fullname $profileSelect FROM post_comments c LEFT JOIN users u ON u.id=c.user_id WHERE c.post_type=? AND c.post_id=? ORDER BY c.id ASC");
    $stmt->execute([$postType, $postId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function render_engagement_html(array $counts, int $commentCount, array $allowedReactions): string {
    ob_start();
    ?>
    <div class="reaction-summary">
        <?php if ((int)$counts['total'] > 0): ?>
            <span class="reaction-icons">
                <?php foreach ($allowedReactions as $key => $info): ?>
                    <?php if (($counts[$key] ?? 0) > 0): ?>
                        <span title="<?php echo e($info['label']); ?>"><?php echo e($info['emoji']); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </span>
            <span><?php echo number_format((int)$counts['total']); ?></span>
        <?php else: ?>
            <span>Be the first to react</span>
        <?php endif; ?>
    </div>
    <div><?php echo number_format($commentCount); ?> Comment<?php echo $commentCount === 1 ? '' : 's'; ?></div>
    <?php
    return ob_get_clean();
}

function render_comments_html(array $comments, int $currentUserId): string {
    $children = [];
    $commentMap = [];

    foreach ($comments as $comment) {
        $commentId = (int)($comment['id'] ?? 0);
        $parentId = (int)($comment['parent_comment_id'] ?? 0);
        $commentMap[$commentId] = $comment;
        $children[$parentId][] = $comment;
    }

    $renderComment = function(array $comment, int $level = 0) use (&$renderComment, &$children) {
        $commentId = (int)($comment['id'] ?? 0);
        $postType = (string)($comment['post_type'] ?? 'event');
        $postId = (int)($comment['post_id'] ?? 0);
        $isReply = $level > 0;
        $levelClass = $isReply ? ' reply-item level-' . min($level, 5) : '';
        ob_start();
        ?>
        <div class="comment-thread <?php echo $isReply ? 'reply-thread' : 'main-thread'; ?>">
            <div class="comment-item<?php echo e($levelClass); ?>">
                <?php echo avatar_html($comment['fullname'] ?? 'User', $comment['profile_photo'] ?? '', $isReply ? 'comment-avatar small-avatar reply-avatar' : 'comment-avatar small-avatar'); ?>
                <div class="comment-content-wrap">
                    <div class="comment-bubble <?php echo $isReply ? 'reply-bubble' : ''; ?>">
                        <div class="comment-name"><?php echo e($comment['fullname'] ?? 'Unknown User'); ?></div>
                        <div class="comment-text"><?php echo render_comment_text_with_mentions($comment['comment'] ?? ''); ?></div>
                    </div>
                    <div class="comment-tools">
                        <span class="comment-date"><?php echo e(date('M d, Y h:i A', strtotime($comment['created_at'] ?? 'now'))); ?></span>
                        <button type="button" class="reply-toggle-btn" data-reply-box="reply-box-<?php echo $commentId; ?>">Reply</button>
                    </div>

                    <form class="reply-form ajax-reply-form" id="reply-box-<?php echo $commentId; ?>" style="display:none;">
                        <input type="hidden" name="post_type" value="<?php echo e($postType); ?>">
                        <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                        <input type="hidden" name="parent_comment_id" value="<?php echo $commentId; ?>">
                        <div class="reply-input-row">
                            <input type="text" class="comment-input reply-input" name="comment" placeholder="Reply to <?php echo e($comment['fullname'] ?? 'this comment'); ?>..." autocomplete="off">
                            <button class="comment-submit reply-submit" type="submit">Reply</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($children[$commentId])): ?>
                <div class="replies-list <?php echo $level > 0 ? 'nested-replies-list' : ''; ?>">
                    <?php foreach ($children[$commentId] as $child): ?>
                        <?php echo $renderComment($child, $level + 1); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    };

    ob_start();
    if (empty($children[0])): ?>
        <div class="no-comments">No comments yet. Be the first to comment.</div>
    <?php else: ?>
        <div class="comments-list">
            <?php foreach ($children[0] as $comment): ?>
                <?php echo $renderComment($comment, 0); ?>
            <?php endforeach; ?>
        </div>
    <?php endif;
    return ob_get_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    $action = (string)($_POST['ajax_action'] ?? '');
    $postType = (string)($_POST['post_type'] ?? '');
    $postId = (int)($_POST['post_id'] ?? 0);

    if (!in_array($postType, ['event', 'training'], true) || $postId <= 0 || $user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        exit;
    }

    try {
        if ($action === 'react') {
            global $allowedReactions;
            $reactionType = (string)($_POST['reaction_type'] ?? 'like');

            if (!isset($allowedReactions[$reactionType])) {
                echo json_encode(['success' => false, 'message' => 'Invalid reaction.']);
                exit;
            }

            $existing = get_user_reaction($pdo, $postType, $postId, $user_id);

            if ($existing === $reactionType) {
                $del = $pdo->prepare("DELETE FROM post_reactions WHERE post_type=? AND post_id=? AND user_id=?");
                $del->execute([$postType, $postId, $user_id]);
                $userReaction = '';
            } elseif ($existing !== '') {
                $upd = $pdo->prepare("UPDATE post_reactions SET reaction_type=?, created_at=NOW() WHERE post_type=? AND post_id=? AND user_id=?");
                $upd->execute([$reactionType, $postType, $postId, $user_id]);
                $userReaction = $reactionType;
            } else {
                $ins = $pdo->prepare("INSERT INTO post_reactions (post_type, post_id, user_id, reaction_type) VALUES (?, ?, ?, ?)");
                $ins->execute([$postType, $postId, $user_id, $reactionType]);
                $userReaction = $reactionType;
            }

            $counts = get_reaction_counts($pdo, $postType, $postId);
            $comments = get_comments($pdo, $postType, $postId);

            echo json_encode([
                'success' => true,
                'user_reaction' => $userReaction,
                'reaction_label' => $userReaction ? $allowedReactions[$userReaction]['label'] : 'Like',
                'reaction_emoji' => $userReaction ? $allowedReactions[$userReaction]['emoji'] : '👍',
                'engagement_html' => render_engagement_html($counts, count($comments), $allowedReactions),
            ]);
            exit;
        }

        if ($action === 'comment') {
            $comment = trim((string)($_POST['comment'] ?? ''));
            $parentCommentId = (int)($_POST['parent_comment_id'] ?? 0);
            $parentCommentId = $parentCommentId > 0 ? $parentCommentId : null;

            if ($comment === '') {
                echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
                exit;
            }

            if ($parentCommentId !== null) {
                $parentCheck = $pdo->prepare("SELECT id FROM post_comments WHERE id=? AND post_type=? AND post_id=? LIMIT 1");
                $parentCheck->execute([$parentCommentId, $postType, $postId]);
                if (!$parentCheck->fetchColumn()) {
                    echo json_encode(['success' => false, 'message' => 'The comment you are replying to was not found.']);
                    exit;
                }
            }

            $ins = $pdo->prepare("INSERT INTO post_comments (post_type, post_id, parent_comment_id, user_id, comment) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$postType, $postId, $parentCommentId, $user_id, $comment]);

            if ($parentCommentId !== null) {
                $replyOwnerStmt = $pdo->prepare("SELECT user_id FROM post_comments WHERE id=? LIMIT 1");
                $replyOwnerStmt->execute([$parentCommentId]);
                $replyOwnerId = (int)($replyOwnerStmt->fetchColumn() ?: 0);

                if ($replyOwnerId > 0 && $replyOwnerId !== $user_id) {
                    $replyMsg = $currentFullname . " replied to your comment.";
                    $replyNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'reply', ?)");
                    $replyNotif->execute([$replyOwnerId, $user_id, $postType, $postId, $replyMsg]);
                }
            }

            $posterId = 0;
            $postTitle = '';

            if ($postType === 'event') {
                $ownerStmt = $pdo->prepare("SELECT posted_by, title FROM events WHERE id=? LIMIT 1");
            } else {
                $ownerStmt = $pdo->prepare("SELECT posted_by, title FROM trainings WHERE id=? LIMIT 1");
            }
            $ownerStmt->execute([$postId]);
            $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

            if ($owner) {
                $posterId = (int)($owner['posted_by'] ?? 0);
                $postTitle = (string)($owner['title'] ?? 'your post');
            }

            $notificationCreated = false;
            if ($posterId > 0 && $posterId !== $user_id) {
                $notifMsg = $currentFullname . ($parentCommentId ? " replied to a comment on your " : " commented on your ") . $postType . ": " . $postTitle;
                $notif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'comment', ?)");
                $notif->execute([$posterId, $user_id, $postType, $postId, $notifMsg]);
                $notificationCreated = true;
            }

            $mentionedUserIds = get_mentioned_user_ids($pdo, $comment, $user_id);
            if (!empty($mentionedUserIds)) {
                $mentionNotif = $pdo->prepare("INSERT INTO post_notifications (recipient_user_id, sender_user_id, post_type, post_id, notification_type, message) VALUES (?, ?, ?, ?, 'mention', ?)");
                foreach ($mentionedUserIds as $mentionedId) {
                    if ($mentionedId === $posterId) continue;
                    $mentionMsg = $currentFullname . " mentioned you in a comment on " . $postType . ": " . ($postTitle ?: 'a post');
                    $mentionNotif->execute([$mentionedId, $user_id, $postType, $postId, $mentionMsg]);
                    $notificationCreated = true;
                }
            }

            $counts = get_reaction_counts($pdo, $postType, $postId);
            $comments = get_comments($pdo, $postType, $postId);

            echo json_encode([
                'success' => true,
                'comments_html' => render_comments_html($comments, $user_id),
                'engagement_html' => render_engagement_html($counts, count($comments), $allowedReactions),
                'notification_created' => $notificationCreated,
                'comment_count' => count($comments),
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    } catch (Throwable $ex) {
        echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
        exit;
    }
}

$feedProfileSelect = $profileColumn ? ", u.`$profileColumn` AS poster_photo" : ", NULL AS poster_photo";

$jobStmt = $pdo->prepare("SELECT id, title, employer_company, location, description FROM jobs WHERE is_open = 1 ORDER BY id DESC LIMIT 5");
$jobStmt->execute();
$sidebarJobs = $jobStmt->fetchAll(PDO::FETCH_ASSOC);

$feedStmt = $pdo->prepare("
    SELECT
        e.id,
        e.title,
        e.content,
        e.image,
        e.created_at,
        e.posted_by,
        u.fullname AS poster
        $feedProfileSelect,
        'event' AS post_type,
        NULL AS training_date,
        NULL AS location,
        'Open for All' AS target_course,
        e.post_start_date,
        e.post_end_date
    FROM events e
    JOIN users u ON u.id = e.posted_by
    WHERE e.is_archived = 0
      AND (e.post_start_date IS NULL OR e.post_start_date <= NOW())
      AND (e.post_end_date IS NULL OR e.post_end_date >= NOW())

    UNION ALL

    SELECT
        t.id,
        t.title,
        t.content,
        t.image,
        t.created_at,
        t.posted_by,
        u.fullname AS poster
        $feedProfileSelect,
        'training' AS post_type,
        t.training_date,
        t.location,
        t.target_course,
        NULL AS post_start_date,
        NULL AS post_end_date
    FROM trainings t
    JOIN users u ON u.id = t.posted_by
    WHERE t.target_course = ? OR t.target_course = 'Open for All'

    ORDER BY created_at DESC, id DESC
");
$feedStmt->execute([$alumni_course]);
$feed = $feedStmt->fetchAll(PDO::FETCH_ASSOC);

$postData = [];
foreach ($feed as $item) {
    $key = $item['post_type'] . '_' . (int)$item['id'];
    $counts = get_reaction_counts($pdo, $item['post_type'], (int)$item['id']);
    $comments = get_comments($pdo, $item['post_type'], (int)$item['id']);
    $postData[$key] = [
        'counts' => $counts,
        'comments' => $comments,
        'user_reaction' => get_user_reaction($pdo, $item['post_type'], (int)$item['id'], $user_id),
    ];
}


$mentionUsersStmt = $pdo->query("SELECT id, fullname FROM users WHERE fullname IS NOT NULL AND fullname <> '' ORDER BY fullname ASC");
$mentionUsers = [];
foreach ($mentionUsersStmt->fetchAll(PDO::FETCH_ASSOC) as $mu) {
    $mentionUsers[] = [
        'id' => (int)$mu['id'],
        'name' => (string)$mu['fullname'],
    ];
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/alumni_sidebar.php";
?>

<style>
    body { background: #f0f2f5; overflow-x: hidden; }
    .content { margin-left: 290px; width: calc(100% - 290px); max-width: 100%; padding: 25px 20px 40px; }
    .feed-layout { display: grid; grid-template-columns: minmax(0, 1.7fr) 320px; gap: 20px; align-items: start; }
    .feed-main { min-width: 0; }
    .feed-wrapper { max-width: 760px; margin: 0 auto; }
    .feed-sidebar { min-width: 0; }
    .sidebar-card { background: #fff; border-radius: 18px; border: 1px solid #e5e7eb; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 16px; position: sticky; top: 20px; }
    .sidebar-title { font-size: 16px; font-weight: 900; color: #111827; margin-bottom: 12px; }
    .job-ad-card { padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .job-ad-card:last-child { border-bottom: 0; padding-bottom: 0; }
    .job-ad-title { font-size: 15px; font-weight: 800; color: #111827; margin-bottom: 4px; }
    .job-ad-meta { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
    .job-ad-desc { font-size: 13px; color: #374151; line-height: 1.5; margin-bottom: 10px; }
    .job-ad-btn { display: inline-block; text-decoration: none; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; padding: 8px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; }
    .job-ad-btn:hover { background: #f97316; color: #fff; }
    .job-toggle-row { display: flex; justify-content: flex-end; margin-top: 8px; }
    .job-toggle-btn { border: none; background: transparent; color: #f97316; font-size: 12px; font-weight: 800; cursor: pointer; padding: 0; }
    .job-toggle-btn:hover { text-decoration: underline; }
    .job-ad-list-collapsed .job-ad-card:nth-child(n+3) { display: none; }
    .social-card { margin-top: 14px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 14px; background: linear-gradient(135deg, #f8fafc, #fff7ed); }
    .social-card-title { font-size: 14px; font-weight: 900; color: #111827; margin-bottom: 10px; }
    .social-link { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; text-decoration: none; color: #111827; background: #fff; border: 1px solid #e5e7eb; margin-bottom: 8px; transition: .2s ease; }
    .social-link:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
    .social-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #fff; font-size: 16px; }
    .social-facebook { background: linear-gradient(135deg, #1877f2, #0b5ed7); }
    .social-youtube { background: linear-gradient(135deg, #ff0000, #cc0000); }
    .social-name { font-size: 13px; font-weight: 800; }
    .social-desc { font-size: 12px; color: #6b7280; }

    @media (max-width: 992px) {
        .content { margin-left: 0; width: 100%; padding: 20px 14px 30px; }
        .feed-layout { grid-template-columns: 1fr; }
        .feed-sidebar { order: -1; }
        .sidebar-card { position: static; }
    }
    .feed-topbar { background: #fff; border-radius: 18px; padding: 18px 22px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .feed-title { font-size: 28px; font-weight: 800; color: #111827; margin: 0; }
    .feed-subtitle { color: #6b7280; font-size: 14px; margin-top: 4px; }
    .post-card { background: #fff; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; overflow: visible; }
    .post-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 18px 20px 12px; }
    .post-user { display: flex; align-items: center; gap: 12px; }
    .avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.12); }
    .avatar img, .comment-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .avatar .avatar-fallback, .comment-avatar .avatar-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .user-meta { display: flex; flex-direction: column; }
    .poster-name { font-size: 15px; font-weight: 700; color: #111827; line-height: 1.2; }
    .poster-date { font-size: 12px; color: #6b7280; margin-top: 4px; }
    .post-badges { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .badge-pill { padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .badge-event { background: #fff7ed; color: #ea580c; border: 1px solid #fdba74; }
    .badge-training { background: #eff6ff; color: #2563eb; border: 1px solid #93c5fd; }
    .badge-course { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
    .post-body { padding: 0 20px 16px; }
    .post-title { font-size: 22px; font-weight: 800; color: #111827; margin: 6px 0 12px; line-height: 1.3; }
    .training-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
    .training-meta span { background: #f9fafb; border: 1px solid #e5e7eb; color: #374151; font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; }
    .post-image-wrap { width: 100%; background: #f3f4f6; margin: 10px 0 14px; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb; }
    .post-image { width: 100%; max-height: 500px; object-fit: cover; display: block; cursor: pointer; transition: 0.2s ease; }
    .post-image:hover { opacity: 0.85; }
    .post-content { color: #374151; font-size: 15px; line-height: 1.8; white-space: pre-line; }
    .engagement-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 20px; color: #6b7280; font-size: 13px; font-weight: 700; }
    .reaction-summary { display: flex; align-items: center; gap: 7px; }
    .reaction-icons { display: inline-flex; align-items: center; }
    .reaction-icons span { width: 23px; height: 23px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 2px solid #fff; font-size: 13px; margin-right: -5px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
    .post-footer { border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-around; align-items: center; padding: 8px 14px; background: #fff; position: relative; }
    .reaction-area { position: relative; flex: 1; }
    .post-action { border: none; background: transparent; color: #4b5563; font-weight: 700; font-size: 14px; padding: 10px 16px; border-radius: 10px; cursor: pointer; transition: 0.2s ease; width: 100%; }
    .post-action:hover { background: #f3f4f6; }
    .post-action.active-like { color: #1877f2; }
    .post-action.active-love { color: #e11d48; }
    .post-action.active-haha { color: #ca8a04; }
    .post-action.active-angry { color: #dc2626; }
    .reaction-picker { position: absolute; bottom: 43px; left: 50%; transform: translateX(-50%) scale(.95); background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 8px 30px rgba(0,0,0,.18); border-radius: 999px; padding: 7px 9px; display: flex; gap: 7px; opacity: 0; pointer-events: none; transition: .18s ease; z-index: 50; white-space: nowrap; }
    .reaction-area:hover .reaction-picker, .reaction-picker.show { opacity: 1; pointer-events: auto; transform: translateX(-50%) scale(1); }
    .reaction-option { border: none; background: transparent; font-size: 25px; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; transition: .15s ease; }
    .reaction-option:hover { transform: scale(1.25) translateY(-3px); background: #f3f4f6; }
    .comments-section { padding: 10px 20px 14px; background: #fff; border-radius: 0 0 20px 20px; }
    .comments-section.collapsed { padding-top: 0; }
    .comments-section.collapsed .comments-body { display: none; }
    .comments-preview { display: flex; justify-content: flex-end; align-items: center; padding: 8px 0 0; }
    .view-comments-btn { border: none; background: transparent; color: #65676b; font-size: 13px; font-weight: 800; cursor: pointer; padding: 6px 0; }
    .view-comments-btn:hover { color: #f97316; text-decoration: underline; }
    .comment-form { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px; }
    .comment-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.10); }
    .comment-input-wrap { flex: 1; display: flex; gap: 8px; }
    .comment-input { flex: 1; border: 1px solid #e5e7eb; background: #f0f2f5; border-radius: 999px; padding: 11px 15px; outline: none; font-size: 14px; }
    .comment-input:focus { background: #fff; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,.12); }
    .comment-submit { border: none; background: #f97316; color: #fff; border-radius: 999px; padding: 10px 15px; font-weight: 800; cursor: pointer; }
    .comment-submit:hover { background: #ea580c; }
    .comments-list { display: flex; flex-direction: column; gap: 10px; }
    .comment-item { display: flex; align-items: flex-start; gap: 9px; }
    .small-avatar { width: 34px; height: 34px; font-size: 12px; }
    .comment-content-wrap { flex: 1; }
    .comment-bubble { display: inline-block; max-width: 100%; background: #f0f2f5; border-radius: 16px; padding: 9px 12px; }
    .comment-name { color: #111827; font-size: 13px; font-weight: 800; margin-bottom: 3px; }
    .comment-text { color: #374151; font-size: 13px; line-height: 1.45; white-space: pre-line; word-break: break-word; }
    .comment-date { color: #6b7280; font-size: 11px; margin-top: 3px; padding-left: 4px; }
    .comment-thread { display: flex; flex-direction: column; gap: 8px; }
    .comment-tools { display: flex; align-items: center; gap: 10px; margin-top: 3px; }
    .reply-toggle-btn { border: none; background: transparent; color: #6b7280; font-size: 11px; font-weight: 800; cursor: pointer; padding: 0; }
    .reply-toggle-btn:hover { color: #f97316; text-decoration: underline; }
    .reply-form { margin-top: 8px; }
    .reply-input-row { display: flex; gap: 8px; align-items: center; max-width: 520px; }
    .reply-input { padding: 9px 13px; font-size: 13px; }
    .reply-submit { padding: 9px 13px; font-size: 12px; }
    .replies-list { margin-left: 43px; padding-left: 12px; border-left: 2px solid #e5e7eb; display: flex; flex-direction: column; gap: 8px; }
    .nested-replies-list { margin-left: 34px; padding-left: 10px; }
    .reply-thread { gap: 8px; }
    .reply-item { gap: 8px; }
    .reply-avatar { width: 30px; height: 30px; font-size: 11px; }
    .reply-bubble { background: #f7f7f8; }
    .no-comments { color: #6b7280; font-size: 13px; font-weight: 700; padding-left: 48px; }
    .mention-text { color: #f97316; font-weight: 800; }
    .mention-box { position: absolute; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.16); max-height: 220px; overflow-y: auto; z-index: 99998; min-width: 230px; display: none; padding: 6px; }
    .mention-item { width: 100%; border: none; background: #fff; padding: 9px 10px; border-radius: 9px; text-align: left; cursor: pointer; font-size: 13px; font-weight: 800; color: #111827; display: flex; align-items: center; gap: 8px; }
    .mention-item:hover, .mention-item.active { background: #fff7ed; color: #ea580c; }
    .mention-avatar { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900; flex-shrink: 0; }
    .toast-notice { position: fixed; right: 22px; bottom: 22px; background: #111827; color: #fff; padding: 12px 16px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,.22); font-size: 14px; font-weight: 700; display: none; z-index: 99999; }
    .empty-feed { background: #fff; border-radius: 18px; padding: 40px 20px; text-align: center; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,0.06); color: #6b7280; }
    .empty-feed h4 { color: #111827; font-size: 22px; margin-bottom: 8px; }
    @media (max-width: 991.98px) { .content { margin-left: 0; width: 100%; padding: 18px 12px 30px; } .feed-wrapper { max-width: 100%; } .feed-title { font-size: 24px; } .post-title { font-size: 20px; } .post-image { max-height: 350px; } }
    @media (max-width: 575.98px) { .post-header { flex-direction: column; align-items: stretch; } .post-badges { justify-content: flex-start; } .post-footer { flex-wrap: wrap; gap: 8px; } .reaction-area, .post-action { width: 100%; } .comment-form, .comment-input-wrap { flex-direction: column; } .comment-submit { width: 100%; } }
    .lightbox-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.95); z-index: 9999; overflow: auto; animation: fadeIn 0.3s ease; }
    .lightbox-modal.active { display: flex; align-items: center; justify-content: center; }
    .lightbox-content { position: relative; max-width: 600px; max-height: 500px; display: flex; align-items: center; justify-content: center; animation: zoomIn 0.3s ease; }
    .lightbox-image { max-width: 100%; max-height: 100%; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); }
    .lightbox-close { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 32px; font-weight: bold; cursor: pointer; background: rgba(0, 0, 0, 0.5); border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s ease; z-index: 10000; }
    .lightbox-close:hover { background: rgba(0, 0, 0, 0.8); }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>

<div class="content">
    <div class="feed-layout">
        <div class="feed-main">
            <div class="feed-wrapper">
                <div class="feed-topbar">
                    <h3 class="feed-title">Feed</h3>
                    <div class="feed-subtitle">Latest events and trainings from admin</div>
                </div>

                <?php if (count($feed) === 0): ?>
                    <div class="empty-feed">
                        <h4>No posts yet</h4>
                        <p>Please check again later for new events and trainings.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feed as $item): ?>
                <?php
                    $postId = (int)$item['id'];
                    $postType = $item['post_type'];
                    $postKey = $postType . '_' . $postId;
                    $posterInitial = strtoupper(substr(trim($item['poster']), 0, 1));
                    $imageFolder = ($postType === 'event') ? '/uploads/events/' : '/uploads/trainings/';
                    $counts = $postData[$postKey]['counts'];
                    $comments = $postData[$postKey]['comments'];
                    $userReaction = $postData[$postKey]['user_reaction'];
                    $reactionLabel = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['label'] : 'Like';
                    $reactionEmoji = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['emoji'] : '👍';
                ?>
                <div class="post-card" data-post-key="<?php echo e($postKey); ?>">
                    <div class="post-header">
                        <div class="post-user">
                            <?php echo avatar_html($item['poster'] ?? 'User', $item['poster_photo'] ?? '', 'avatar'); ?>
                            <div class="user-meta">
                                <div class="poster-name"><?php echo e($item['poster']); ?></div>
                                <div class="poster-date"><?php echo e(format_post_date($item['created_at'])); ?></div>
                            </div>
                        </div>

                        <div class="post-badges">
                            <?php if ($postType === 'event'): ?>
                                <span class="badge-pill badge-event">Event</span>
                            <?php else: ?>
                                <span class="badge-pill badge-training">Training</span>
                                <span class="badge-pill badge-course"><?php echo e($item['target_course']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="post-body">
                        <div class="post-title"><?php echo e($item['title']); ?></div>

                        <?php if ($postType === 'training'): ?>
                            <div class="training-meta">
                                <span>📅 <?php echo e($item['training_date'] ?: 'Not set'); ?></span>
                                <span>📍 <?php echo e($item['location'] ?: 'No location specified'); ?></span>
                            </div>
                        <?php elseif (!empty($item['post_start_date']) || !empty($item['post_end_date'])): ?>
                            <div class="training-meta">
                                <span>🟢 Start: <?php echo e($item['post_start_date'] ? format_post_date($item['post_start_date']) : 'Immediately'); ?></span>
                                <span>🔴 End: <?php echo e($item['post_end_date'] ? format_post_date($item['post_end_date']) : 'No end date'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['image'])): ?>
                            <div class="post-image-wrap">
                                <img class="post-image" src="<?php echo BASE_URL . $imageFolder . e($item['image']); ?>" alt="<?php echo e($postType); ?> image">
                            </div>
                        <?php endif; ?>

                        <div class="post-content"><?php echo nl2br(e($item['content'])); ?></div>
                    </div>

                    <div class="engagement-row" id="engagement-<?php echo e($postKey); ?>">
                        <?php echo render_engagement_html($counts, count($comments), $allowedReactions); ?>
                    </div>

                    <div class="post-footer">
                        <div class="reaction-area">
                            <div class="reaction-picker">
                                <?php foreach ($allowedReactions as $reactionKey => $reactionInfo): ?>
                                    <button type="button" class="reaction-option" data-post-type="<?php echo e($postType); ?>" data-post-id="<?php echo $postId; ?>" data-post-key="<?php echo e($postKey); ?>" data-reaction="<?php echo e($reactionKey); ?>" title="<?php echo e($reactionInfo['label']); ?>">
                                        <?php echo e($reactionInfo['emoji']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="post-action reaction-main-btn <?php echo $userReaction ? 'active-' . e($userReaction) : ''; ?>" id="reaction-btn-<?php echo e($postKey); ?>" data-post-type="<?php echo e($postType); ?>" data-post-id="<?php echo $postId; ?>" data-post-key="<?php echo e($postKey); ?>" data-reaction="<?php echo e($userReaction ?: 'like'); ?>">
                                <span class="reaction-btn-emoji"><?php echo e($reactionEmoji); ?></span>
                                <span class="reaction-btn-label"><?php echo e($reactionLabel); ?></span>
                            </button>
                        </div>

                        <button class="post-action comment-focus-btn" type="button" data-comments-section="comments-section-<?php echo e($postKey); ?>" data-comment-input="comment-input-<?php echo e($postKey); ?>">💬 Comment</button>
                    </div>

                    <div class="comments-section collapsed" id="comments-section-<?php echo e($postKey); ?>">
                        <div class="comments-preview">
                            <button type="button" class="view-comments-btn" data-comments-section="comments-section-<?php echo e($postKey); ?>" data-comment-input="comment-input-<?php echo e($postKey); ?>">
                                <?php echo count($comments) > 0 ? 'View all ' . number_format(count($comments)) . ' comment' . (count($comments) === 1 ? '' : 's') : 'Write a comment'; ?>
                            </button>
                        </div>

                        <div class="comments-body">
                            <form class="comment-form ajax-comment-form" data-post-key="<?php echo e($postKey); ?>">
                                <?php echo avatar_html($currentFullname, $currentUserPhoto, 'comment-avatar'); ?>
                                <div class="comment-input-wrap">
                                    <input type="hidden" name="post_type" value="<?php echo e($postType); ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                                    <input type="text" class="comment-input" id="comment-input-<?php echo e($postKey); ?>" name="comment" placeholder="Write a comment..." autocomplete="off">
                                    <button class="comment-submit" type="submit">Post</button>
                                </div>
                            </form>

                            <div id="comments-<?php echo e($postKey); ?>">
                                <?php echo render_comments_html($comments, $user_id); ?>
                            </div>
                        </div>
                    </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>

        <aside class="feed-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-title">Job Opportunities</div>
                <div class="job-ad-list-collapsed" id="jobAdList">
                    <?php if (!empty($sidebarJobs)): ?>
                        <?php foreach ($sidebarJobs as $index => $job): ?>
                            <div class="job-ad-card">
                                <div class="job-ad-title"><?php echo e($job['title'] ?? 'Job opening'); ?></div>
                                <div class="job-ad-meta"><?php echo e(($job['employer_company'] ?? 'Company') . (!empty($job['location']) ? ' • ' . $job['location'] : '')); ?></div>
                                <div class="job-ad-desc"><?php echo e(shorten_text($job['description'] ?? '', 110)); ?></div>
                                <a class="job-ad-btn" href="<?php echo BASE_URL; ?>/alumni/apply.php?id=<?php echo (int)$job['id']; ?>">Apply now</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="job-ad-desc">No job openings right now.</div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($sidebarJobs) && count($sidebarJobs) > 2): ?>
                    <div class="job-toggle-row">
                        <button class="job-toggle-btn" type="button" onclick="toggleJobList()" id="jobToggleBtn">See all</button>
                    </div>
                <?php endif; ?>

                <div class="social-card">
                    <div class="social-card-title">Follow Our School</div>
                    <a class="social-link" href="https://www.facebook.com" target="_blank" rel="noopener noreferrer">
                        <div class="social-icon social-facebook">f</div>
                        <div>
                            <div class="social-name">Facebook</div>
                            <div class="social-desc">Visit our school page</div>
                        </div>
                    </a>
                    <a class="social-link" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer">
                        <div class="social-icon social-youtube">▶</div>
                        <div>
                            <div class="social-name">YouTube</div>
                            <div class="social-desc">Watch updates and events</div>
                        </div>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="mention-box" id="mentionBox"></div>
<div class="toast-notice" id="toastNotice"></div>

<div class="lightbox-modal" id="lightboxModal">
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
        <img class="lightbox-image" id="lightboxImage" src="" alt="">
    </div>
</div>

<script>
const ajaxUrl = window.location.href;

const mentionUsers = <?php echo json_encode($mentionUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const mentionBox = document.getElementById('mentionBox');
let activeMentionInput = null;
let activeMentionStart = -1;

function getInitials(name) {
    return String(name || 'U').trim().split(/\s+/).slice(0, 2).map(p => p.charAt(0).toUpperCase()).join('') || 'U';
}

function toggleJobList() {
    const list = document.getElementById('jobAdList');
    const btn = document.getElementById('jobToggleBtn');
    if (!list || !btn) return;
    const collapsed = list.classList.toggle('job-ad-list-collapsed');
    btn.textContent = collapsed ? 'See all' : 'Hide';
}

function getMentionInfo(input) {
    const pos = input.selectionStart || 0;
    const value = input.value || '';
    const before = value.substring(0, pos);
    const at = before.lastIndexOf('@');
    if (at < 0) return null;
    if (at > 0 && /\S/.test(before.charAt(at - 1))) return null;
    const query = before.substring(at + 1);
    if (/\n/.test(query) || query.length > 40) return null;
    return { start: at, query: query.toLowerCase() };
}

function positionMentionBox(input) {
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

    const matches = mentionUsers.filter(u => String(u.name || '').toLowerCase().includes(info.query)).slice(0, 8);
    if (matches.length === 0) {
        mentionBox.style.display = 'none';
        return;
    }

    mentionBox.innerHTML = matches.map(u => `
        <button type="button" class="mention-item" data-name="${String(u.name).replace(/"/g, '&quot;')}">
            <span class="mention-avatar">${getInitials(u.name)}</span>
            <span>${String(u.name).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>
        </button>
    `).join('');
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


function showToast(message) {
    const toast = document.getElementById('toastNotice');
    if (!toast) return;
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2500);
}

function setReactionButton(postKey, reactionType, emoji, label) {
    const btn = document.getElementById('reaction-btn-' + postKey);
    if (!btn) return;
    btn.classList.remove('active-like', 'active-love', 'active-haha', 'active-angry');
    if (reactionType) {
        btn.classList.add('active-' + reactionType);
        btn.dataset.reaction = reactionType;
    } else {
        btn.dataset.reaction = 'like';
    }
    const emojiSpan = btn.querySelector('.reaction-btn-emoji');
    const labelSpan = btn.querySelector('.reaction-btn-label');
    if (emojiSpan) emojiSpan.textContent = emoji || '👍';
    if (labelSpan) labelSpan.textContent = label || 'Like';
}

async function sendReaction(button, reactionType) {
    const postType = button.dataset.postType;
    const postId = button.dataset.postId;
    const postKey = button.dataset.postKey;
    const formData = new FormData();
    formData.append('ajax_action', 'react');
    formData.append('post_type', postType);
    formData.append('post_id', postId);
    formData.append('reaction_type', reactionType);
    const response = await fetch(ajaxUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await response.json();
    if (!data.success) { showToast(data.message || 'Reaction failed.'); return; }
    const engagement = document.getElementById('engagement-' + postKey);
    if (engagement) engagement.innerHTML = data.engagement_html;
    setReactionButton(postKey, data.user_reaction, data.reaction_emoji, data.reaction_label);
}

document.querySelectorAll('.reaction-option').forEach(btn => {
    btn.addEventListener('click', function () { sendReaction(this, this.dataset.reaction); });
});

document.querySelectorAll('.reaction-main-btn').forEach(btn => {
    btn.addEventListener('click', function () { sendReaction(this, this.dataset.reaction || 'like'); });
});

function openComments(sectionId, inputId, focusInput = false) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    section.classList.remove('collapsed');
    const input = inputId ? document.getElementById(inputId) : null;
    section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    if (focusInput && input) {
        setTimeout(() => input.focus(), 150);
    }
}

function toggleComments(sectionId, inputId, focusInput = false) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    const willOpen = section.classList.contains('collapsed');
    section.classList.toggle('collapsed');
    if (willOpen) {
        const input = inputId ? document.getElementById(inputId) : null;
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        if (focusInput && input) setTimeout(() => input.focus(), 150);
    }
}

document.addEventListener('click', function (ev) {
    const commentBtn = ev.target.closest('.comment-focus-btn');
    if (commentBtn) {
        toggleComments(commentBtn.dataset.commentsSection, commentBtn.dataset.commentInput, true);
        return;
    }

    const viewBtn = ev.target.closest('.view-comments-btn');
    if (viewBtn) {
        toggleComments(viewBtn.dataset.commentsSection, viewBtn.dataset.commentInput, false);
    }
});

async function submitCommentForm(form) {
    const postKey = form.dataset.postKey || ((form.querySelector('[name="post_type"]')?.value || '') + '_' + (form.querySelector('[name="post_id"]')?.value || ''));
    const input = form.querySelector('.comment-input');
    const comment = input ? input.value.trim() : '';
    if (comment === '') { showToast('Comment cannot be empty.'); return; }

    const formData = new FormData(form);
    formData.append('ajax_action', 'comment');

    const response = await fetch(ajaxUrl, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await response.json();

    if (!data.success) { showToast(data.message || 'Comment failed.'); return; }

    const commentsBox = document.getElementById('comments-' + postKey);
    const engagement = document.getElementById('engagement-' + postKey);

    if (commentsBox) commentsBox.innerHTML = data.comments_html;
    if (engagement) engagement.innerHTML = data.engagement_html;
    const section = document.getElementById('comments-section-' + postKey);
    if (section) section.classList.remove('collapsed');
    const previewBtn = section ? section.querySelector('.view-comments-btn') : null;
    if (previewBtn && typeof data.comment_count !== 'undefined') {
        const n = Number(data.comment_count || 0);
        previewBtn.textContent = n > 0 ? ('View all ' + n.toLocaleString() + ' comment' + (n === 1 ? '' : 's')) : 'Write a comment';
    }
    if (input) input.value = '';
    if (mentionBox) mentionBox.style.display = 'none';

    showToast(data.notification_created ? 'Comment posted. Notification sent.' : 'Comment posted.');
}

document.addEventListener('submit', function (ev) {
    const form = ev.target.closest('.ajax-comment-form, .ajax-reply-form');
    if (!form) return;
    ev.preventDefault();
    submitCommentForm(form);
});

document.addEventListener('click', function (ev) {
    const btn = ev.target.closest('.reply-toggle-btn');
    if (!btn) return;
    const box = document.getElementById(btn.dataset.replyBox);
    if (!box) return;
    box.style.display = box.style.display === 'none' || box.style.display === '' ? 'block' : 'none';
    const input = box.querySelector('.reply-input');
    if (box.style.display === 'block' && input) input.focus();
});

const lightboxModal = document.getElementById('lightboxModal');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxClose = document.getElementById('lightboxClose');
document.querySelectorAll('.post-image').forEach(img => {
    img.addEventListener('click', function(ev) { ev.preventDefault(); lightboxImage.src = this.src; lightboxModal.classList.add('active'); });
});
if (lightboxClose) lightboxClose.addEventListener('click', function() { lightboxModal.classList.remove('active'); });
if (lightboxModal) lightboxModal.addEventListener('click', function(ev) { if (ev.target === lightboxModal) lightboxModal.classList.remove('active'); });
document.addEventListener('keydown', function(ev) { if (ev.key === 'Escape' && lightboxModal) lightboxModal.classList.remove('active'); });
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
