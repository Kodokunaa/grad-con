
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

                <?php 
if (count($feed) === 0) {
    ?>
                    <div class="empty-feed">
                        <h4>No posts yet</h4>
                        <p>Please check again later for new events and trainings.</p>
                    </div>
                <?php 
} else {
    ?>
                    <?php 
    foreach ($feed as $item) {
        ?>
                <?php 
        $postId = (int) $item['id'];
        $postType = $item['post_type'];
        $postKey = $postType . '_' . $postId;
        $posterInitial = strtoupper(substr(trim($item['poster']), 0, 1));
        $imageFolder = $postType === 'event' ? '/uploads/events/' : '/uploads/trainings/';
        $counts = $postData[$postKey]['counts'];
        $comments = $postData[$postKey]['comments'];
        $userReaction = $postData[$postKey]['user_reaction'];
        $reactionLabel = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['label'] : 'Like';
        $reactionEmoji = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['emoji'] : '👍';
        ?>
                <div class="post-card" data-post-key="<?php 
        echo \gc_e($postKey);
        ?>">
                    <div class="post-header">
                        <div class="post-user">
                            <?php 
        echo \gc_alumni_feed_avatar_html($item['poster'] ?? 'User', $item['poster_photo'] ?? '', 'avatar');
        ?>
                            <div class="user-meta">
                                <div class="poster-name"><?php 
        echo \gc_e($item['poster']);
        ?></div>
                                <div class="poster-date"><?php 
        echo \gc_e(\gc_alumni_feed_format_post_date($item['created_at']));
        ?></div>
                            </div>
                        </div>

                        <div class="post-badges">
                            <?php 
        if ($postType === 'event') {
            ?>
                                <span class="badge-pill badge-event">Event</span>
                            <?php 
        } else {
            ?>
                                <span class="badge-pill badge-training">Training</span>
                                <span class="badge-pill badge-course"><?php 
            echo \gc_e($item['target_course']);
            ?></span>
                            <?php 
        }
        ?>
                        </div>
                    </div>

                    <div class="post-body">
                        <div class="post-title"><?php 
        echo \gc_e($item['title']);
        ?></div>

                        <?php 
        if ($postType === 'training') {
            ?>
                            <div class="training-meta">
                                <span>📅 <?php 
            echo \gc_e($item['training_date'] ?: 'Not set');
            ?></span>
                                <span>📍 <?php 
            echo \gc_e($item['location'] ?: 'No location specified');
            ?></span>
                            </div>
                        <?php 
        } elseif (!empty($item['post_start_date']) || !empty($item['post_end_date'])) {
            ?>
                            <div class="training-meta">
                                <span>🟢 Start: <?php 
            echo \gc_e($item['post_start_date'] ? \gc_alumni_feed_format_post_date($item['post_start_date']) : 'Immediately');
            ?></span>
                                <span>🔴 End: <?php 
            echo \gc_e($item['post_end_date'] ? \gc_alumni_feed_format_post_date($item['post_end_date']) : 'No end date');
            ?></span>
                            </div>
                        <?php 
        }
        ?>

                        <?php 
        if (!empty($item['image'])) {
            ?>
                            <div class="post-image-wrap">
                                <img class="post-image" src="<?php 
            echo \url('') . $imageFolder . \gc_e($item['image']);
            ?>" alt="<?php 
            echo \gc_e($postType);
            ?> image">
                            </div>
                        <?php 
        }
        ?>

                        <div class="post-content"><?php 
        echo nl2br(\gc_e($item['content']));
        ?></div>
                    </div>

                    <div class="engagement-row" id="engagement-<?php 
        echo \gc_e($postKey);
        ?>">
                        <?php 
        echo \gc_alumni_feed_render_engagement_html($counts, count($comments), $allowedReactions);
        ?>
                    </div>

                    <div class="post-footer">
                        <div class="reaction-area">
                            <div class="reaction-picker">
                                <?php 
        foreach ($allowedReactions as $reactionKey => $reactionInfo) {
            ?>
                                    <button type="button" class="reaction-option" data-post-type="<?php 
            echo \gc_e($postType);
            ?>" data-post-id="<?php 
            echo $postId;
            ?>" data-post-key="<?php 
            echo \gc_e($postKey);
            ?>" data-reaction="<?php 
            echo \gc_e($reactionKey);
            ?>" title="<?php 
            echo \gc_e($reactionInfo['label']);
            ?>">
                                        <?php 
            echo \gc_e($reactionInfo['emoji']);
            ?>
                                    </button>
                                <?php 
        }
        ?>
                            </div>

                            <button type="button" class="post-action reaction-main-btn <?php 
        echo $userReaction ? 'active-' . \gc_e($userReaction) : '';
        ?>" id="reaction-btn-<?php 
        echo \gc_e($postKey);
        ?>" data-post-type="<?php 
        echo \gc_e($postType);
        ?>" data-post-id="<?php 
        echo $postId;
        ?>" data-post-key="<?php 
        echo \gc_e($postKey);
        ?>" data-reaction="<?php 
        echo \gc_e($userReaction ?: 'like');
        ?>">
                                <span class="reaction-btn-emoji"><?php 
        echo \gc_e($reactionEmoji);
        ?></span>
                                <span class="reaction-btn-label"><?php 
        echo \gc_e($reactionLabel);
        ?></span>
                            </button>
                        </div>

                        <button class="post-action comment-focus-btn" type="button" data-comments-section="comments-section-<?php 
        echo \gc_e($postKey);
        ?>" data-comment-input="comment-input-<?php 
        echo \gc_e($postKey);
        ?>">💬 Comment</button>
                    </div>

                    <div class="comments-section collapsed" id="comments-section-<?php 
        echo \gc_e($postKey);
        ?>">
                        <div class="comments-preview">
                            <button type="button" class="view-comments-btn" data-comments-section="comments-section-<?php 
        echo \gc_e($postKey);
        ?>" data-comment-input="comment-input-<?php 
        echo \gc_e($postKey);
        ?>">
                                <?php 
        echo count($comments) > 0 ? 'View all ' . number_format(count($comments)) . ' comment' . (count($comments) === 1 ? '' : 's') : 'Write a comment';
        ?>
                            </button>
                        </div>

                        <div class="comments-body">
                            <form class="comment-form ajax-comment-form" data-post-key="<?php 
        echo \gc_e($postKey);
        ?>">
                                <?php 
        echo \gc_alumni_feed_avatar_html($currentFullname, $currentUserPhoto, 'comment-avatar');
        ?>
                                <div class="comment-input-wrap">
                                    <input type="hidden" name="post_type" value="<?php 
        echo \gc_e($postType);
        ?>">
                                    <input type="hidden" name="post_id" value="<?php 
        echo $postId;
        ?>">
                                    <input type="text" class="comment-input" id="comment-input-<?php 
        echo \gc_e($postKey);
        ?>" name="comment" placeholder="Write a comment..." autocomplete="off">
                                    <button class="comment-submit" type="submit">Post</button>
                                </div>
                            </form>

                            <div id="comments-<?php 
        echo \gc_e($postKey);
        ?>">
                                <?php 
        echo \gc_alumni_feed_render_comments_html($comments, $user_id);
        ?>
                            </div>
                        </div>
                    </div>
                    </div>
                <?php 
    }
    ?>
            <?php 
}
?>
            </div>
        </div>

        <aside class="feed-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-title">Job Opportunities</div>
                <div class="job-ad-list-collapsed" id="jobAdList">
                    <?php 
if (!empty($sidebarJobs)) {
    ?>
                        <?php 
    foreach ($sidebarJobs as $index => $job) {
        ?>
                            <div class="job-ad-card">
                                <div class="job-ad-title"><?php 
        echo \gc_e($job['title'] ?? 'Job opening');
        ?></div>
                                <div class="job-ad-meta"><?php 
        echo \gc_e(($job['employer_company'] ?? 'Company') . (!empty($job['location']) ? ' • ' . $job['location'] : ''));
        ?></div>
                                <div class="job-ad-desc"><?php 
        echo \gc_e(\gc_alumni_feed_shorten_text($job['description'] ?? '', 110));
        ?></div>
                                <a class="job-ad-btn" href="<?php 
        echo \url('');
        ?>/alumni/apply.php?id=<?php 
        echo (int) $job['id'];
        ?>">Apply now</a>
                            </div>
                        <?php 
    }
    ?>
                    <?php 
} else {
    ?>
                        <div class="job-ad-desc">No job openings right now.</div>
                    <?php 
}
?>
                </div>

                <?php 
if (!empty($sidebarJobs) && count($sidebarJobs) > 2) {
    ?>
                    <div class="job-toggle-row">
                        <button class="job-toggle-btn" type="button" onclick="toggleJobList()" id="jobToggleBtn">See all</button>
                    </div>
                <?php 
}
?>

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

const mentionUsers = <?php 
echo json_encode($mentionUsers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>;
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

<?php 
echo \gc_partial('footer', \get_defined_vars());