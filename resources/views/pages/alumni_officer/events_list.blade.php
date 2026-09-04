
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f0f2f5; overflow-x: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1f2937; }
    .content { margin-left: 290px; width: calc(100% - 290px); min-height: 100vh; padding: 26px 18px 45px; }
    .wall-wrapper { max-width: 820px; margin: 0 auto; }
    .cover-card { background: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10); margin-bottom: 22px; }
    .cover-bg { height: 190px; background: radial-gradient(circle at top left, rgba(255,255,255,0.38), transparent 26%), linear-gradient(135deg, #f97316 0%, #fb923c 45%, #16a34a 100%); position: relative; }
    .cover-bg::after { content: ""; position: absolute; inset: 0; background-image: linear-gradient(45deg, rgba(255,255,255,0.10) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.10) 50%, rgba(255,255,255,0.10) 75%, transparent 75%, transparent); background-size: 42px 42px; opacity: .28; }
    .profile-section { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; padding: 0 24px 24px; margin-top: -54px; position: relative; z-index: 2; }
    .profile-left { display: flex; align-items: flex-end; gap: 18px; min-width: 0; flex: 1 1 auto; }
    .page-avatar { width: 112px; height: 112px; border-radius: 50%; background: #ffffff; border: 5px solid #ffffff; box-shadow: 0 10px 26px rgba(15, 23, 42, 0.20); display: flex; align-items: center; justify-content: center; font-size: 44px; flex: 0 0 auto; }
    .page-info { min-width: 0; padding: 0 0 8px; }
    .page-title { font-size: 31px; font-weight: 900; color: #111827; line-height: 1.08; margin: 0; letter-spacing: -0.02em; }
    .page-subtitle { max-width: 520px; color: #64748b; font-size: 14px; font-weight: 600; line-height: 1.5; margin: 8px 0 0; }
    .btn-post { display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #f97316; color: #ffffff; text-decoration: none; padding: 13px 18px; border-radius: 12px; font-size: 14px; font-weight: 800; box-shadow: 0 8px 18px rgba(249, 115, 22, 0.24); transition: .2s ease; white-space: nowrap; margin-bottom: 8px; flex: 0 0 auto; }
    .btn-post:hover { background: #ea580c; color: #ffffff; transform: translateY(-1px); }
    .feed-area { width: 100%; }
    .composer-card, .event-post, .alert-box, .search-card, .schedule-notice-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06); }
    .search-card { padding: 14px; margin-bottom: 14px; }
    .schedule-notice-card { padding: 12px 14px; margin-bottom: 14px; background: #fff7ed; border-color: #fed7aa; color: #9a3412; font-size: 13px; line-height: 1.5; }
    .schedule-notice-card strong { color: #7c2d12; font-weight: 900; }
    .search-label { display: block; font-size: 12px; font-weight: 900; color: #4b5563; margin-bottom: 7px; }
    .search-input { width: 100%; border: 1px solid #d1d5db; border-radius: 999px; padding: 12px 16px; outline: none; font-size: 14px; transition: .2s ease; background: #f0f2f5; }
    .search-input:focus { background: #ffffff; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.14); }
    .composer-card { padding: 14px; margin-bottom: 14px; }
    .composer-row { display: flex; align-items: center; gap: 12px; }
    .user-avatar { border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; color: #ffffff; font-weight: 900; background: linear-gradient(135deg, #f97316, #16a34a); box-shadow: 0 2px 8px rgba(15,23,42,.10); }
    .user-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .user-avatar .avatar-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .composer-avatar { width: 46px; height: 46px; font-size: 17px; }
    .composer-button { flex: 1; background: #f0f2f5; color: #6b7280; border-radius: 999px; padding: 12px 16px; text-decoration: none; font-size: 14px; font-weight: 700; transition: .2s ease; }
    .composer-button:hover { background: #e5e7eb; color: #374151; }
    .alert-box { padding: 14px 16px; margin-bottom: 14px; font-size: 14px; font-weight: 700; border-left: 5px solid; }
    .alert-success-custom { background: #ecfdf5; color: #065f46; border-left-color: #10b981; }
    .alert-danger-custom { background: #fef2f2; color: #7f1d1d; border-left-color: #ef4444; }
    .event-post { overflow: visible; margin-bottom: 14px; }
    .post-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 16px 10px; }
    .poster-info { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .poster-avatar { width: 48px; height: 48px; font-size: 16px; background: #fff7ed; color: #ea580c; border: 2px solid #fed7aa; }
    .poster-name { font-size: 15px; font-weight: 900; color: #111827; margin: 0; }
    .post-meta { margin-top: 3px; font-size: 12px; color: #6b7280; font-weight: 600; }
    .post-id-badge { background: #f3f4f6; color: #4b5563; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 900; white-space: nowrap; }
    .post-badges-right { display:flex; flex-direction:column; align-items:flex-end; gap:7px; }
    .status-pill { border-radius:999px; padding:6px 10px; font-size:12px; font-weight:900; white-space:nowrap; }
    .status-active { background:#ecfdf5; color:#047857; }
    .status-scheduled { background:#eff6ff; color:#1d4ed8; }
    .status-expired { background:#fef2f2; color:#b91c1c; }
    .schedule-line { display:flex; flex-wrap:wrap; gap:8px; margin:10px 0 4px; }
    .schedule-line span { background:#f9fafb; border:1px solid #e5e7eb; color:#374151; font-size:12px; font-weight:800; padding:7px 10px; border-radius:999px; }
    .post-content { padding: 0 16px 13px; }
    .event-title { font-size: 20px; font-weight: 900; color: #111827; margin: 4px 0 8px; line-height: 1.25; }
    .event-text { color: #374151; font-size: 14px; line-height: 1.55; white-space: pre-line; }
    .event-image-wrap { border-top: 1px solid #eef2f7; border-bottom: 1px solid #eef2f7; background: #f9fafb; }
    .event-image { width: 100%; max-height: 460px; object-fit: cover; display: block; cursor: zoom-in; }
    .image-lightbox { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.78); display: none; align-items: center; justify-content: center; padding: 24px; z-index: 99999; }
    .image-lightbox.open { display: flex; }
    .image-lightbox-inner { position: relative; max-width: min(92vw, 860px); max-height: 90vh; width: 100%; display: flex; align-items: center; justify-content: center; }
    .image-lightbox img { max-width: 100%; max-height: 90vh; width: auto; height: auto; object-fit: contain; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.35); background: #ffffff; }
    .image-lightbox-close { position: absolute; top: 10px; right: 10px; width: 40px; height: 40px; border: none; border-radius: 50%; background: rgba(255,255,255,0.95); color: #111827; font-size: 20px; cursor: pointer; box-shadow: 0 6px 16px rgba(0,0,0,0.18); }
    .no-image-banner { height: 190px; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 8px; color: #9ca3af; background: linear-gradient(135deg, #f9fafb, #eef2f7); font-weight: 800; }
    .no-image-banner span { font-size: 38px; }
    .engagement-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 16px; color: #6b7280; font-size: 13px; font-weight: 700; }
    .reaction-summary { display: flex; align-items: center; gap: 6px; }
    .reaction-icons { display: inline-flex; align-items: center; }
    .reaction-icons span { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 2px solid #fff; font-size: 12px; margin-right: -5px; box-shadow: 0 1px 4px rgba(0,0,0,.15); }
    .post-actions { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-top: 1px solid #eef2f7; border-bottom: 1px solid #eef2f7; background: #fff; }
    .post-manage-actions { display: inline-flex; align-items: center; gap: 6px; margin-left: auto; padding-left: 8px; border-left: 1px solid #eef2f7; }
    .btn-icon-action { width: 38px; height: 38px; min-width: 38px; padding: 0; border-radius: 50%; font-size: 14px; line-height: 1; box-shadow: none; }
    .btn-icon-action .action-label { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .reaction-form { position: relative; margin: 0; flex: 1 1 0; min-width: 0; }
    .reaction-picker { position: absolute; bottom: 47px; left: 50%; transform: translateX(-50%) scale(.95); background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 8px 30px rgba(0,0,0,.18); border-radius: 999px; padding: 7px 9px; display: flex; gap: 7px; opacity: 0; pointer-events: none; transition: .18s ease; z-index: 50; white-space: nowrap; }
    .reaction-form:hover .reaction-picker { opacity: 1; pointer-events: auto; transform: translateX(-50%) scale(1); }
    .reaction-option { border: none; background: transparent; font-size: 23px; width: 34px; height: 34px; border-radius: 50%; cursor: pointer; transition: .15s ease; }
    .reaction-option:hover { transform: scale(1.25) translateY(-3px); background: #f3f4f6; }
    .btn-action { display: flex; justify-content: center; align-items: center; gap: 7px; border: none; border-radius: 10px; padding: 10px 12px; text-decoration: none; font-size: 14px; font-weight: 900; cursor: pointer; transition: .2s ease; width: 100%; }
    .btn-like { background: #ffffff; color: #4b5563; }
    .btn-like:hover, .btn-like.active-like { background: #eff6ff; color: #1877f2; }
    .btn-like.active-love { background: #fff1f2; color: #e11d48; }
    .btn-like.active-haha { background: #fefce8; color: #ca8a04; }
    .btn-like.active-angry { background: #fef2f2; color: #dc2626; }
    .btn-comment { background: #ffffff; color: #4b5563; flex: 1 1 0; min-width: 0; }
    .btn-comment:hover { background: #f3f4f6; color: #111827; }
    .btn-edit { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .btn-edit:hover { background: #f97316; color: #ffffff; border-color: #f97316; }
    .btn-delete { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .btn-delete:hover { background: #dc2626; color: #ffffff; border-color: #dc2626; }
    .comment-section { padding: 12px 16px 16px; background: #ffffff; border-radius: 0 0 16px 16px; }
    .comment-form { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 12px; }
    .comment-avatar { width: 36px; height: 36px; font-size: 12px; }
    .comment-input-wrap { flex: 1; display: flex; gap: 8px; }
    .comment-input { width: 100%; border: 1px solid #e5e7eb; background: #f0f2f5; border-radius: 999px; padding: 10px 14px; outline: none; font-size: 14px; }
    .comment-input:focus { background: #ffffff; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12); }
    .comment-submit { border: none; background: #f97316; color: #ffffff; border-radius: 999px; padding: 10px 14px; font-size: 13px; font-weight: 900; cursor: pointer; white-space: nowrap; }
    .comment-submit:hover { background: #ea580c; }
    .comments-list { display: flex; flex-direction: column; gap: 10px; }
    .comment-item { display: flex; align-items: flex-start; gap: 9px; }
    .comment-bubble { background: #f0f2f5; border-radius: 16px; padding: 9px 12px; max-width: 100%; flex: 1; }
    .comment-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 3px; }
    .comment-name { font-size: 13px; color: #111827; font-weight: 900; }
    .comment-date { font-size: 11px; color: #6b7280; white-space: nowrap; }
    .comment-text { font-size: 13px; color: #374151; line-height: 1.45; white-space: pre-line; word-break: break-word; }
    .comment-delete-form { margin-top: 4px; }
    .comment-delete-btn { background: transparent; border: none; color: #dc2626; font-size: 11px; font-weight: 800; cursor: pointer; padding: 0; }
    .comment-delete-btn:hover { text-decoration: underline; }
    .comment-thread { width: 100%; }
    .comment-tools { display: flex; align-items: center; gap: 12px; margin-top: 5px; padding-left: 12px; }
    .comment-reply-btn { background: transparent; border: none; color: #2563eb; font-size: 11px; font-weight: 900; cursor: pointer; padding: 0; }
    .comment-reply-btn:hover { text-decoration: underline; }
    .reply-form { display: flex; gap: 8px; align-items: flex-start; margin: 8px 0 10px 0; }
    .reply-avatar { width: 30px; height: 30px; font-size: 10px; }
    .reply-input-wrap { flex: 1; display: flex; gap: 7px; }
    .reply-input { width: 100%; border: 1px solid #e5e7eb; background: #f0f2f5; border-radius: 999px; padding: 9px 12px; outline: none; font-size: 13px; }
    .reply-input:focus { background: #ffffff; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12); }
    .reply-submit { border: none; background: #16a34a; color: #ffffff; border-radius: 999px; padding: 9px 12px; font-size: 12px; font-weight: 900; cursor: pointer; white-space: nowrap; }
    .reply-submit:hover { background: #15803d; }
    .replies-list { margin-top: 9px; margin-left: 16px; padding-left: 16px; border-left: 3px solid #e5e7eb; display: flex; flex-direction: column; gap: 9px; }
    .reply-item .comment-avatar { width: 32px; height: 32px; font-size: 11px; }
    .reply-bubble { background: #f9fafb; border: 1px solid #eef2f7; }
    .comment-count-btn { border: none; background: transparent; color: #6b7280; font-size: 13px; font-weight: 800; cursor: pointer; padding: 0; }
    .comment-count-btn:hover { color: #f97316; text-decoration: underline; }
    .comment-toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 2px 0 12px; }
    .view-comments-btn { border: none; background: transparent; color: #4b5563; font-size: 13px; font-weight: 900; cursor: pointer; padding: 0; }
    .view-comments-btn:hover { color: #f97316; text-decoration: underline; }
    .comments-collapse { display: none; }
    .comments-collapse.show { display: block; }
    .comment-preview { margin: 0 0 12px; }
    .comment-preview.hide-preview { display: none; }
    .comment-preview .comment-item { margin-bottom: 0; }
    .comment-preview-label { color: #6b7280; font-size: 12px; font-weight: 800; margin: 0 0 7px 46px; }
    .hidden-comments-note { color: #6b7280; font-size: 11px; font-weight: 800; white-space: nowrap; }
    .no-comments { color: #6b7280; font-size: 13px; font-weight: 700; padding-left: 46px; }
    .mention-text { color: #f97316; font-weight: 900; }
    .mention-box { position: absolute; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 12px 35px rgba(15, 23, 42, .18); max-height: 230px; overflow-y: auto; z-index: 99999; min-width: 240px; display: none; padding: 7px; }
    .mention-item { width: 100%; border: none; background: #fff; padding: 9px 10px; border-radius: 10px; text-align: left; cursor: pointer; font-size: 13px; font-weight: 850; color: #111827; display: flex; align-items: center; gap: 8px; }
    .mention-item:hover, .mention-item.active { background: #fff7ed; color: #ea580c; }
    .mention-avatar { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #16a34a); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 950; flex-shrink: 0; }
    .empty-state { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06); text-align: center; padding: 55px 20px; color: #6b7280; }
    .empty-state-icon { font-size: 52px; margin-bottom: 12px; }
    .empty-state-text { font-size: 18px; color: #111827; font-weight: 900; margin-bottom: 6px; }
    .empty-state-subtext { font-size: 14px; color: #6b7280; font-weight: 600; }
    #noSearchResults { display: none; margin-bottom: 14px; }
    @media (max-width: 991.98px) { .content { margin-left: 0; width: 100%; padding: 18px 12px 36px; } .profile-section { flex-direction: column; align-items: stretch; } .profile-left { align-items: center; } .btn-post { width: 100%; margin-bottom: 0; } .page-title { font-size: 26px; } }
    @media (max-width: 575.98px) { .cover-bg { height: 145px; } .profile-section { padding: 0 16px 18px; margin-top: -42px; } .profile-left { flex-direction: column; align-items: flex-start; gap: 8px; } .page-avatar { width: 88px; height: 88px; font-size: 35px; } .post-actions { flex-wrap: wrap; } .reaction-form, .btn-comment { flex: 1 1 calc(50% - 8px); } .post-manage-actions { width: 100%; justify-content: flex-end; margin-left: 0; padding-left: 0; border-left: none; border-top: 1px solid #f1f5f9; padding-top: 8px; } .event-title { font-size: 18px; } .comment-form, .comment-input-wrap { flex-direction: column; } .comment-submit { width: 100%; } }


    /* FIXED RESPONSIVE REACTION PICKER */
    .reaction-form { position: relative; overflow: visible; }
    .reaction-picker {
        min-width: max-content;
        visibility: hidden;
    }
    .reaction-form:hover .reaction-picker,
    .reaction-form.reaction-open .reaction-picker,
    .reaction-form:focus-within .reaction-picker {
        opacity: 1;
        pointer-events: auto;
        visibility: visible;
        transform: translateX(-50%) scale(1);
    }
    .reaction-main-btn { user-select: none; touch-action: manipulation; }
    .reaction-option {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2px;
        min-width: 42px;
        min-height: 42px;
        line-height: 1;
        touch-action: manipulation;
    }
    .reaction-option-label {
        display: none;
        font-size: 9px;
        font-weight: 900;
        color: #4b5563;
    }
    .reaction-option-emoji { line-height: 1; }

    @media (hover: none), (pointer: coarse) {
        .reaction-form:hover .reaction-picker {
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
            transform: translateX(-50%) scale(.95);
        }
        .reaction-form.reaction-open .reaction-picker,
        .reaction-form:focus-within .reaction-picker {
            opacity: 1;
            pointer-events: auto;
            visibility: visible;
            transform: translateX(-50%) scale(1);
        }
    }

    @media (max-width: 575.98px) {
        .reaction-picker {
            position: fixed;
            left: 50% !important;
            bottom: 18px;
            top: auto !important;
            transform: translateX(-50%) translateY(12px) scale(.98);
            width: calc(100vw - 28px);
            max-width: 360px;
            justify-content: space-around;
            border-radius: 18px;
            padding: 10px;
            z-index: 999999;
        }
        .reaction-form.reaction-open .reaction-picker,
        .reaction-form:focus-within .reaction-picker {
            transform: translateX(-50%) translateY(0) scale(1);
        }
        .reaction-option {
            width: auto;
            height: auto;
            min-width: 54px;
            min-height: 52px;
            font-size: 24px;
            border-radius: 14px;
        }
        .reaction-option-label { display: block; }
        .btn-action { min-height: 42px; }
    }

    .reaction-form.reaction-loading { opacity: .75; pointer-events: none; }
    .reaction-main-btn:disabled, .reaction-option:disabled { cursor: wait; opacity: .75; }
</style>

<div class="content">
    <div class="wall-wrapper">
        <div class="cover-card">
            <div class="cover-bg"></div>
            <div class="profile-section">
                <div class="profile-left">
                    <div class="page-avatar">📅</div>
                    <div class="page-info">
                        <h1 class="page-title">Events Wall</h1>
                        <p class="page-subtitle">Only posts whose scheduled date and time have been reached will appear here.</p>
                    </div>
                </div>
                <a href="<?php 
echo \url('');
?>/alumni_officer/events_create.php" class="btn-post">+ Post New Event</a>
            </div>
        </div>

        <main class="feed-area">
            <div class="composer-card">
                <div class="composer-row">
                    <?php 
echo \gc_alumni_officer_events_list_avatar_html($currentFullname, $currentUserPhoto, 'user-avatar composer-avatar');
?>
                    <a href="<?php 
echo \url('');
?>/alumni_officer/events_create.php" class="composer-button">Create or schedule an event post</a>
                </div>
            </div>

            <div class="search-card">
                <label class="search-label" for="eventSearch">Search Events</label>
                <input type="text" id="eventSearch" class="search-input" placeholder="Search visible posts by title, content, or poster...">
            </div>

            <div class="schedule-notice-card">
                <strong>Scheduled posting enabled:</strong> events with a future posting date/time are hidden from this feed until the scheduled time is reached.
            </div>

            <?php 
if ($msg) {
    ?><div class="alert-box alert-success-custom"><?php 
    echo \gc_e($msg);
    ?></div><?php 
}
?>
            <?php 
if ($error) {
    ?><div class="alert-box alert-danger-custom"><?php 
    echo \gc_e($error);
    ?></div><?php 
}
?>

            <div id="noSearchResults" class="empty-state">
                <div class="empty-state-icon">🔎</div>
                <div class="empty-state-text">No matching events found</div>
                <div class="empty-state-subtext">Try searching another event title or keyword.</div>
            </div>

            <?php 
if (!$events) {
    ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-text">No visible events found</div>
                    <div class="empty-state-subtext">Scheduled posts will appear automatically once their posting time is reached.</div>
                </div>
            <?php 
} else {
    ?>
                <?php 
    foreach ($events as $event) {
        ?>
                    <?php 
        $eventId = (int) $event["id"];
        $postedBy = $event["fullname"] ?? "Unknown";
        $searchText = strtolower(($event["title"] ?? '') . ' ' . ($event["content"] ?? '') . ' ' . $postedBy);
        $counts = $postData[$eventId]['counts'];
        $eventComments = $postData[$eventId]['comments'];
        $comments = (int) ($eventComments['total'] ?? 0);
        $userReaction = $postData[$eventId]['user_reaction'];
        $reactionLabel = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['label'] : 'Like';
        $reactionEmoji = $userReaction && isset($allowedReactions[$userReaction]) ? $allowedReactions[$userReaction]['emoji'] : '👍';
        [$statusText, $statusClass] = \gc_alumni_officer_events_list_post_status_label($event['post_start_date'] ?? null, $event['post_end_date'] ?? null);
        ?>

                    <article class="event-post" id="event-post-<?php 
        echo $eventId;
        ?>" data-search="<?php 
        echo \gc_e($searchText);
        ?>">
                        <div class="post-header">
                            <div class="poster-info">
                                <?php 
        echo \gc_alumni_officer_events_list_avatar_html($postedBy, $event['profile_photo'] ?? '', 'user-avatar poster-avatar');
        ?>
                                <div>
                                    <h4 class="poster-name"><?php 
        echo \gc_e($postedBy);
        ?></h4>
                                    <div class="post-meta">Posted an alumni event • Event ID #<?php 
        echo $eventId;
        ?></div>
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
        echo nl2br(\gc_e(\gc_alumni_officer_events_list_short_text($event["content"], 320)));
        ?></div>
                            <div class="schedule-line">
                                <span>🟢 Start: <?php 
        echo \gc_e(!empty($event['post_start_date']) ? \gc_alumni_officer_events_list_format_schedule_date($event['post_start_date']) : 'Immediately');
        ?></span>
                                <span>🔴 End: <?php 
        echo \gc_e(!empty($event['post_end_date']) ? \gc_alumni_officer_events_list_format_schedule_date($event['post_end_date']) : 'No end date');
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
            ?>" class="event-image" alt="Event Image" onclick="openImageLightbox(this.src)">
                            <?php 
        } else {
            ?>
                                <div class="no-image-banner"><span>🖼️</span>No event image uploaded</div>
                            <?php 
        }
        ?>
                        </div>

                        <div class="engagement-row">
                            <div class="reaction-summary" data-reaction-summary="<?php 
        echo $eventId;
        ?>">
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
                            <button type="button" class="comment-count-btn" onclick="toggleCommentsBox('comments-box-<?php 
        echo $eventId;
        ?>')"><?php 
        echo number_format($comments);
        ?> Comment<?php 
        echo $comments === 1 ? '' : 's';
        ?></button>
                        </div>

                        <div class="post-actions">
                            <form method="POST" action="" class="reaction-form" data-reaction-form>
@csrf
                                <input type="hidden" name="event_id" value="<?php 
        echo $eventId;
        ?>">
                                <input type="hidden" name="toggle_reaction" value="1">

                                <div class="reaction-picker" role="menu" aria-label="Choose reaction">
                                    <?php 
        foreach ($allowedReactions as $reactionKey => $reactionInfo) {
            ?>
                                        <button type="submit" name="reaction_type" value="<?php 
            echo \gc_e($reactionKey);
            ?>" class="reaction-option" title="<?php 
            echo \gc_e($reactionInfo['label']);
            ?>" aria-label="<?php 
            echo \gc_e($reactionInfo['label']);
            ?>">
                                            <span class="reaction-option-emoji"><?php 
            echo \gc_e($reactionInfo['emoji']);
            ?></span>
                                            <span class="reaction-option-label"><?php 
            echo \gc_e($reactionInfo['label']);
            ?></span>
                                        </button>
                                    <?php 
        }
        ?>
                                </div>

                                <button type="button" class="btn-action btn-like reaction-main-btn <?php 
        echo $userReaction ? 'active-' . \gc_e($userReaction) : '';
        ?>" data-current-reaction="<?php 
        echo \gc_e($userReaction ?: 'like');
        ?>" aria-haspopup="true" aria-expanded="false">
                                    <span class="main-reaction-emoji"><?php 
        echo \gc_e($reactionEmoji);
        ?></span>
                                    <span class="main-reaction-label"><?php 
        echo \gc_e($reactionLabel);
        ?></span>
                                </button>
                            </form>

                            <button type="button" class="btn-action btn-comment" onclick="showCommentsBox('comments-box-<?php 
        echo $eventId;
        ?>'); focusComment(<?php 
        echo $eventId;
        ?>)">💬 Comment</button>

                            <div class="post-manage-actions" aria-label="Post management actions">
                                <a href="<?php 
        echo \url('');
        ?>/alumni_officer/events_edit.php?id=<?php 
        echo $eventId;
        ?>" class="btn-action btn-icon-action btn-edit" title="Edit event" aria-label="Edit event">✏️<span class="action-label">Edit</span></a>
                                <a href="<?php 
        echo \url('');
        ?>/alumni_officer/events_list.php?delete=<?php 
        echo $eventId;
        ?>" class="btn-action btn-icon-action btn-delete" title="Archive event" aria-label="Archive event">🗑<span class="action-label">Archive</span></a>
                            </div>
                        </div>

                        <div class="comment-section">
                            <form method="POST" action="" class="comment-form">
@csrf
                                <?php 
        echo \gc_alumni_officer_events_list_avatar_html($currentFullname, $currentUserPhoto, 'user-avatar comment-avatar');
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
        if (empty($eventComments['main'])) {
            ?>
                                <div class="no-comments">No comments yet. Be the first to comment.</div>
                            <?php 
        } else {
            ?>
                                <?php 
            $commentsBoxId = 'comments-box-' . $eventId;
            $previewComment = $eventComments['main'][0];
            $previewCommentName = $previewComment['fullname'] ?? 'Unknown User';
            $previewHiddenCount = max(0, $comments - 1);
            ?>

                                <div class="comment-toggle-row">
                                    <button type="button" class="view-comments-btn" onclick="toggleCommentsBox('<?php 
            echo \gc_e($commentsBoxId);
            ?>', this)" data-open-text="Hide comments" data-closed-text="View all <?php 
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
            if ($previewHiddenCount > 0) {
                ?>
                                        <span class="hidden-comments-note"><?php 
                echo number_format($previewHiddenCount);
                ?> more hidden</span>
                                    <?php 
            }
            ?>
                                </div>

                                <div class="comment-preview">
                                    <div class="comment-preview-label">Latest comment preview</div>
                                    <div class="comment-item">
                                        <?php 
            echo \gc_alumni_officer_events_list_avatar_html($previewCommentName, $previewComment['profile_photo'] ?? '', 'user-avatar comment-avatar');
            ?>
                                        <div style="flex:1;">
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
            echo \gc_alumni_officer_events_list_render_comment_text_with_mentions(\gc_alumni_officer_events_list_short_text($previewComment['comment'] ?? '', 120));
            ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="comments-collapse" id="<?php 
            echo \gc_e($commentsBoxId);
            ?>">
                                    <div class="comments-list">
                                        <?php 
            foreach ($eventComments['main'] as $comment) {
                ?>
                                            <?php 
                $commentId = (int) $comment['id'];
                ?>
                                            <div class="comment-thread">
                                                <div class="comment-item">
                                                    <?php 
                echo \gc_alumni_officer_events_list_avatar_html($comment['fullname'] ?? 'User', $comment['profile_photo'] ?? '', 'user-avatar comment-avatar');
                ?>
                                                    <div style="flex:1;">
                                                        <div class="comment-bubble">
                                                            <div class="comment-top">
                                                                <div class="comment-name"><?php 
                echo \gc_e($comment['fullname'] ?? 'Unknown User');
                ?></div>
                                                                <div class="comment-date"><?php 
                echo \gc_e(date('M d, Y h:i A', strtotime($comment['created_at'] ?? 'now')));
                ?></div>
                                                            </div>
                                                            <div class="comment-text"><?php 
                echo \gc_alumni_officer_events_list_render_comment_text_with_mentions($comment['comment'] ?? '');
                ?></div>
                                                        </div>
                                                        <div class="comment-tools">
                                                            <button type="button" class="comment-reply-btn" onclick="toggleReplyBox(<?php 
                echo $commentId;
                ?>)">Reply</button>
                                                            <form method="POST" action="" class="comment-delete-form" onsubmit="return confirm('Delete this comment and its replies?');">
@csrf
                                                                <input type="hidden" name="comment_id" value="<?php 
                echo $commentId;
                ?>">
                                                                <button type="submit" name="delete_comment" class="comment-delete-btn">Delete</button>
                                                            </form>
                                                        </div>

                                                        <form method="POST" action="" class="reply-form" id="reply-box-<?php 
                echo $commentId;
                ?>
@csrf" style="display:none;">
                                                            <?php 
                echo \gc_alumni_officer_events_list_avatar_html($currentFullname, $currentUserPhoto, 'user-avatar reply-avatar');
                ?>
                                                            <div class="reply-input-wrap">
                                                                <input type="hidden" name="event_id" value="<?php 
                echo $eventId;
                ?>">
                                                                <input type="hidden" name="parent_comment_id" value="<?php 
                echo $commentId;
                ?>">
                                                                <input type="text" name="comment" class="reply-input" placeholder="Write a reply..." autocomplete="off">
                                                                <button type="submit" name="add_comment" class="reply-submit">Reply</button>
                                                            </div>
                                                        </form>

                                                        <?php 
                if (!empty($eventComments['replies'][$commentId])) {
                    ?>
                                                            <div class="replies-list">
                                                                <?php 
                    foreach ($eventComments['replies'][$commentId] as $reply) {
                        ?>
                                                                    <div class="comment-item reply-item">
                                                                        <?php 
                        echo \gc_alumni_officer_events_list_avatar_html($reply['fullname'] ?? 'User', $reply['profile_photo'] ?? '', 'user-avatar comment-avatar');
                        ?>
                                                                        <div style="flex:1;">
                                                                            <div class="comment-bubble reply-bubble">
                                                                                <div class="comment-top">
                                                                                    <div class="comment-name"><?php 
                        echo \gc_e($reply['fullname'] ?? 'Unknown User');
                        ?></div>
                                                                                    <div class="comment-date"><?php 
                        echo \gc_e(date('M d, Y h:i A', strtotime($reply['created_at'] ?? 'now')));
                        ?></div>
                                                                                </div>
                                                                                <div class="comment-text"><?php 
                        echo \gc_alumni_officer_events_list_render_comment_text_with_mentions($reply['comment'] ?? '');
                        ?></div>
                                                                            </div>
                                                                            <?php 
                        $replyId = (int) $reply['id'];
                        ?>
                                                                            <?php 
                        $replyReplyBoxId = 'reply-box-' . $eventId . '-' . $commentId . '-' . $replyId;
                        ?>
                                                                            <div class="comment-tools">
                                                                                <button type="button" class="comment-reply-btn" onclick="toggleReplyBoxById('<?php 
                        echo \gc_e($replyReplyBoxId);
                        ?>', '@<?php 
                        echo \gc_e($reply['fullname'] ?? 'User');
                        ?> ')">Reply</button>
                                                                                <form method="POST" action="" class="comment-delete-form" onsubmit="return confirm('Delete this reply?');">
@csrf
                                                                                    <input type="hidden" name="comment_id" value="<?php 
                        echo $replyId;
                        ?>">
                                                                                    <button type="submit" name="delete_comment" class="comment-delete-btn">Delete</button>
                                                                                </form>
                                                                            </div>

                                                                            <form method="POST" action="" class="reply-form" id="<?php 
                        echo \gc_e($replyReplyBoxId);
                        ?>
@csrf" style="display:none;">
                                                                                <?php 
                        echo \gc_alumni_officer_events_list_avatar_html($currentFullname, $currentUserPhoto, 'user-avatar reply-avatar');
                        ?>
                                                                                <div class="reply-input-wrap">
                                                                                    <input type="hidden" name="event_id" value="<?php 
                        echo $eventId;
                        ?>">
                                                                                    <input type="hidden" name="parent_comment_id" value="<?php 
                        echo $commentId;
                        ?>">
                                                                                    <input type="text" name="comment" class="reply-input" placeholder="Write a reply..." autocomplete="off">
                                                                                    <button type="submit" name="add_comment" class="reply-submit">Reply</button>
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
                            <?php 
        }
        ?>                        </div>
                    </article>
                <?php 
    }
    ?>
            <?php 
}
?>
        </main>
    </div>
</div>

<div class="image-lightbox" id="imageLightbox" onclick="closeImageLightbox(event)">
    <div class="image-lightbox-inner">
        <button type="button" class="image-lightbox-close" onclick="closeImageLightbox(event)" aria-label="Close image preview">×</button>
        <img id="imageLightboxImg" src="" alt="Event preview">
    </div>
</div>

<div class="mention-box" id="mentionBox"></div>

<script>
function openImageLightbox(src) {
    const lightbox = document.getElementById('imageLightbox');
    const img = document.getElementById('imageLightboxImg');
    if (!lightbox || !img) return;
    img.src = src;
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox(event) {
    const lightbox = document.getElementById('imageLightbox');
    if (!lightbox) return;
    const target = event && event.target ? event.target : null;
    if (target && (target.classList.contains('image-lightbox') || target.classList.contains('image-lightbox-close'))) {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox && lightbox.classList.contains('open')) {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
        }
    }
});

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
    const input = ev.target.closest('.comment-input, .reply-input');
    if (!input) return;
    showMentionSuggestions(input);
});

document.addEventListener('keyup', function(ev) {
    const input = ev.target.closest('.comment-input, .reply-input');
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

    if (!ev.target.closest('#mentionBox') && !ev.target.closest('.comment-input') && !ev.target.closest('.reply-input')) {
        if (mentionBox) mentionBox.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('eventSearch');
    const posts = Array.from(document.querySelectorAll('.event-post'));
    const noSearchResults = document.getElementById('noSearchResults');
    if (!searchInput) return;
    function filterEvents() {
        const keyword = (searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;
        posts.forEach(function (post) {
            const text = post.dataset.search || '';
            const matched = keyword === '' || text.includes(keyword);
            post.style.display = matched ? '' : 'none';
            if (matched) visibleCount++;
        });
        if (noSearchResults) noSearchResults.style.display = posts.length > 0 && visibleCount === 0 ? 'block' : 'none';
    }
    searchInput.addEventListener('input', filterEvents);
});

function updateCommentPreview(commentsBoxId, isOpen) {
    const box = document.getElementById(commentsBoxId);
    if (!box) return;
    const preview = box.previousElementSibling;
    if (preview && preview.classList.contains('comment-preview')) {
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

function focusComment(eventId) {
    const input = document.getElementById('comment-input-' + eventId);
    if (input) {
        input.focus();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function toggleReplyBox(commentId, prefillText = '') {
    toggleReplyBoxById('reply-box-' + commentId, prefillText);
}

function toggleReplyBoxById(replyBoxId, prefillText = '') {
    const box = document.getElementById(replyBoxId);
    if (!box) return;

    const isHidden = box.style.display === 'none' || box.style.display === '';
    box.style.display = isHidden ? 'flex' : 'none';

    if (isHidden) {
        const input = box.querySelector('input[name="comment"]');
        if (input) {
            if (prefillText && input.value.trim() === '') {
                input.value = prefillText;
            }
            input.focus();
            const end = input.value.length;
            input.setSelectionRange(end, end);
            showMentionSuggestions(input);
        }
    }
}


// Responsive AJAX reaction picker: no page refresh, no scroll to top, no "Reaction updated" alert
(function () {
    const reactionMeta = {
        like:  { emoji: '👍', label: 'Like' },
        love:  { emoji: '❤️', label: 'Love' },
        haha:  { emoji: '😂', label: 'Haha' },
        angry: { emoji: '😡', label: 'Angry' }
    };

    function closeAllReactionPickers(exceptForm) {
        document.querySelectorAll('[data-reaction-form].reaction-open').forEach(function (form) {
            if (form !== exceptForm) {
                form.classList.remove('reaction-open');
                const btn = form.querySelector('.reaction-main-btn');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function setButtonLoading(form, isLoading) {
        const mainBtn = form.querySelector('.reaction-main-btn');
        const options = form.querySelectorAll('.reaction-option');
        if (mainBtn) mainBtn.disabled = isLoading;
        options.forEach(function (btn) { btn.disabled = isLoading; });
        form.classList.toggle('reaction-loading', isLoading);
    }

    function updateReactionSummary(article, counts) {
        const summary = article.querySelector('.reaction-summary');
        if (!summary || !counts) return;

        const total = parseInt(counts.total || 0, 10);
        const reactions = ['like', 'love', 'haha', 'angry'];

        if (total <= 0) {
            summary.innerHTML = '<span>Be the first to react</span>';
            return;
        }

        let iconHtml = '<span class="reaction-icons">';
        reactions.forEach(function (key) {
            if (parseInt(counts[key] || 0, 10) > 0 && reactionMeta[key]) {
                iconHtml += '<span title="' + reactionMeta[key].label + '">' + reactionMeta[key].emoji + '</span>';
            }
        });
        iconHtml += '</span>';

        summary.innerHTML = iconHtml + '<span>' + total.toLocaleString() + ' Reaction' + (total === 1 ? '' : 's') + '</span>';
    }

    function updateMainReactionButton(form, reaction, emoji, label) {
        const mainBtn = form.querySelector('.reaction-main-btn');
        if (!mainBtn) return;

        mainBtn.classList.remove('active-like', 'active-love', 'active-haha', 'active-angry');

        if (reaction) {
            mainBtn.classList.add('active-' + reaction);
            mainBtn.dataset.currentReaction = reaction;
        } else {
            mainBtn.dataset.currentReaction = 'like';
        }

        const emojiSpan = mainBtn.querySelector('.main-reaction-emoji');
        const labelSpan = mainBtn.querySelector('.main-reaction-label');
        if (emojiSpan) emojiSpan.textContent = emoji || '👍';
        if (labelSpan) labelSpan.textContent = label || 'Like';
    }

    function submitReaction(form, reactionType) {
        if (!form || !reactionType) return;

        const article = form.closest('.event-post');
        const formData = new FormData(form);
        formData.set('toggle_reaction', '1');
        formData.set('ajax_reaction', '1');
        formData.set('reaction_type', reactionType);

        closeAllReactionPickers(null);
        setButtonLoading(form, true);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data || !data.success) {
                console.error(data && data.message ? data.message : 'Reaction failed.');
                return;
            }

            updateMainReactionButton(form, data.reaction || '', data.emoji || '👍', data.label || 'Like');
            if (article) updateReactionSummary(article, data.counts || {});
        })
        .catch(function (error) {
            console.error('Reaction request failed:', error);
        })
        .finally(function () {
            setButtonLoading(form, false);
        });
    }

    document.addEventListener('click', function (ev) {
        const mainBtn = ev.target.closest('.reaction-main-btn');
        const reactionOption = ev.target.closest('.reaction-option');

        if (mainBtn) {
            ev.preventDefault();
            const form = mainBtn.closest('[data-reaction-form]');
            if (!form || form.classList.contains('reaction-loading')) return;

            // First tap/click opens picker. Second tap/click reacts with current/default Like.
            if (!form.classList.contains('reaction-open')) {
                closeAllReactionPickers(form);
                form.classList.add('reaction-open');
                mainBtn.setAttribute('aria-expanded', 'true');
                return;
            }

            submitReaction(form, mainBtn.dataset.currentReaction || 'like');
            return;
        }

        if (reactionOption) {
            ev.preventDefault();
            const form = reactionOption.closest('[data-reaction-form]');
            if (!form || form.classList.contains('reaction-loading')) return;
            submitReaction(form, reactionOption.value || 'like');
            return;
        }

        if (!ev.target.closest('[data-reaction-form]')) {
            closeAllReactionPickers(null);
        }
    });

    document.addEventListener('submit', function (ev) {
        const form = ev.target.closest('[data-reaction-form]');
        if (!form) return;
        ev.preventDefault();
        const mainBtn = form.querySelector('.reaction-main-btn');
        submitReaction(form, (mainBtn && mainBtn.dataset.currentReaction) || 'like');
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') {
            closeAllReactionPickers(null);
        }
    });
})();

</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());