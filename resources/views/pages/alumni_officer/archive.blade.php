
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f0f2f5; overflow-x: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1f2937; }
    .content { margin-left: 290px; width: calc(100% - 290px); min-height: 100vh; padding: 26px 18px 45px; }
    .wall-wrapper { max-width: 820px; margin: 0 auto; }
    .cover-card { background: #ffffff; border-radius: 18px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08); margin-bottom: 18px; }
    .cover-bg { height: 160px; background: radial-gradient(circle at top left, rgba(255,255,255,0.35), transparent 26%), linear-gradient(135deg, #f97316 0%, #fb923c 45%, #16a34a 100%); position: relative; }
    .cover-bg::after { content: ""; position: absolute; inset: 0; background-image: linear-gradient(45deg, rgba(255,255,255,0.10) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.10) 50%, rgba(255,255,255,0.10) 75%, transparent 75%, transparent); background-size: 42px 42px; opacity: .28; }
    .profile-section { padding: 0 24px 22px; margin-top: -34px; position: relative; z-index: 2; }
    .page-title { font-size: 28px; font-weight: 900; color: #111827; line-height: 1.1; margin: 0; letter-spacing: -0.02em; }
    .page-subtitle { color: #6b7280; font-size: 14px; font-weight: 600; margin-top: 6px; }
    .feed-area { width: 100%; }
    .empty-state { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06); text-align: center; padding: 55px 20px; color: #6b7280; }
    .empty-state-icon { font-size: 52px; margin-bottom: 12px; }
    .empty-state-text { font-size: 18px; color: #111827; font-weight: 900; margin-bottom: 6px; }
    .empty-state-subtext { font-size: 14px; color: #6b7280; font-weight: 600; }
    .event-post { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06); overflow: visible; margin-bottom: 14px; }
    .post-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 16px 10px; }
    .poster-info { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .user-avatar { border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; color: #ffffff; font-weight: 900; background: linear-gradient(135deg, #f97316, #16a34a); box-shadow: 0 2px 8px rgba(15,23,42,.10); }
    .user-avatar .avatar-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .poster-avatar { width: 48px; height: 48px; font-size: 16px; background: #fff7ed; color: #ea580c; border: 2px solid #fed7aa; }
    .poster-name { font-size: 15px; font-weight: 900; color: #111827; margin: 0; }
    .post-meta { margin-top: 3px; font-size: 12px; color: #6b7280; font-weight: 600; }
    .post-badges-right { display:flex; flex-direction:column; align-items:flex-end; gap:7px; }
    .post-id-badge { background: #f3f4f6; color: #4b5563; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 900; white-space: nowrap; }
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
    .post-actions { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-top: 1px solid #eef2f7; border-bottom: 1px solid #eef2f7; background: #fff; }
    .btn-action { display: flex; justify-content: center; align-items: center; gap: 7px; border: none; border-radius: 10px; padding: 10px 12px; text-decoration: none; font-size: 14px; font-weight: 900; cursor: pointer; transition: .2s ease; width: 100%; }
    .btn-edit { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .btn-edit:hover { background: #f97316; color: #ffffff; border-color: #f97316; }
    .comment-section { padding: 12px 16px 16px; background: #ffffff; }
    .comments-list { display: flex; flex-direction: column; gap: 10px; }
    .comment-item { display: flex; align-items: flex-start; gap: 9px; }
    .comment-avatar { width: 36px; height: 36px; font-size: 12px; }
    .comment-bubble { background: #f0f2f5; border-radius: 16px; padding: 9px 12px; max-width: 100%; flex: 1; }
    .comment-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 3px; }
    .comment-name { font-size: 13px; color: #111827; font-weight: 900; }
    .comment-date { font-size: 11px; color: #6b7280; white-space: nowrap; }
    .comment-text { font-size: 13px; color: #374151; line-height: 1.45; white-space: pre-line; word-break: break-word; }
    @media (max-width: 991.98px) { .content { margin-left: 0; width: 100%; padding: 18px 12px 36px; } }
    @media (max-width: 575.98px) { .post-header { flex-direction: column; } .post-badges-right { align-items: flex-start; } .comment-item { flex-direction: column; } .comment-avatar { width: 32px; height: 32px; font-size: 11px; } }
</style>

<div class="content">
    <div class="wall-wrapper">
        <div class="cover-card">
            <div class="cover-bg"></div>
            <div class="profile-section">
                <div>
                    <h2 class="page-title">Archived Events</h2>
                    <p class="page-subtitle">These archived posts keep the same look as the live event feed and can be restored anytime.</p>
                </div>
            </div>
        </div>

        <div class="feed-area">
            <?php 
if (empty($archivedEvents)) {
    ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🗂️</div>
                    <div class="empty-state-text">No archived events yet.</div>
                    <div class="empty-state-subtext">Archived posts will appear here in the same layout as the event feed.</div>
                </div>
            <?php 
} else {
    ?>
                <?php 
    foreach ($archivedEvents as $event) {
        ?>
                    <?php 
        [$statusText, $statusClass] = \gc_alumni_officer_archive_post_status_label($event['post_start_date'] ?? null, $event['post_end_date'] ?? null);
        ?>
                    <div class="event-post">
                        <div class="post-header">
                            <div class="poster-info">
                                <?php 
        echo \gc_alumni_officer_archive_avatar_html($event['fullname'] ?? 'Unknown', 'user-avatar poster-avatar');
        ?>
                                <div>
                                    <h4 class="poster-name"><?php 
        echo \gc_e($event['fullname'] ?? 'Unknown');
        ?></h4>
                                    <div class="post-meta">Archived on <?php 
        echo \gc_e(!empty($event['archived_at']) ? $event['archived_at'] : 'Unknown');
        ?></div>
                                </div>
                            </div>
                            <div class="post-badges-right">
                                <div class="post-id-badge">Archived Event</div>
                                <div class="status-pill <?php 
        echo \gc_e($statusClass);
        ?>"><?php 
        echo \gc_e($statusText);
        ?></div>
                            </div>
                        </div>

                        <div class="post-content">
                            <h2 class="event-title"><?php 
        echo \gc_e($event['title'] ?? 'Untitled event');
        ?></h2>
                            <div class="event-text"><?php 
        echo nl2br(\gc_e($event['content'] ?? 'No description provided.'));
        ?></div>
                            <div class="schedule-line">
                                <span>🟢 Start: <?php 
        echo \gc_e(!empty($event['post_start_date']) ? \gc_alumni_officer_archive_format_schedule_date($event['post_start_date']) : 'Immediately');
        ?></span>
                                <span>🔴 End: <?php 
        echo \gc_e(!empty($event['post_end_date']) ? \gc_alumni_officer_archive_format_schedule_date($event['post_end_date']) : 'No end date');
        ?></span>
                            </div>
                        </div>

                        <?php 
        if (!empty($event['image'])) {
            ?>
                            <div class="event-image-wrap">
                                <img src="<?php 
            echo \url('');
            ?>/uploads/events/<?php 
            echo \gc_e($event['image']);
            ?>" class="event-image" alt="Archived event image" onclick="openImageLightbox(this.src)">
                            </div>
                        <?php 
        } else {
            ?>
                            <div class="event-image-wrap">
                                <div class="no-image-banner"><span>🖼️</span>No event image uploaded</div>
                            </div>
                        <?php 
        }
        ?>

                        <div class="engagement-row">
                            <div>📦 Archived post</div>
                            <div><?php 
        echo count($event['comments'] ?? []);
        ?> Comment<?php 
        echo count($event['comments'] ?? []) === 1 ? '' : 's';
        ?></div>
                        </div>

                        <div class="post-actions">
                            <a href="<?php 
        echo \url('');
        ?>/alumni_officer/archive.php?restore=<?php 
        echo (int) $event['id'];
        ?>" class="btn-action btn-edit">↺ Restore</a>
                        </div>

                        <?php 
        if (!empty($event['comments'])) {
            ?>
                            <div class="comment-section">
                                <div class="comments-list">
                                    <?php 
            foreach ($event['comments'] as $comment) {
                ?>
                                        <div class="comment-item">
                                            <?php 
                echo \gc_alumni_officer_archive_avatar_html($comment['fullname'] ?? 'User', 'user-avatar comment-avatar');
                ?>
                                            <div class="comment-bubble">
                                                <div class="comment-top">
                                                    <div class="comment-name"><?php 
                echo \gc_e($comment['fullname'] ?? 'Unknown');
                ?></div>
                                                    <div class="comment-date"><?php 
                echo \gc_e(!empty($comment['created_at']) ? date('M d, Y', strtotime($comment['created_at'])) : '');
                ?></div>
                                                </div>
                                                <div class="comment-text"><?php 
                echo nl2br(\gc_e($comment['comment'] ?? ''));
                ?></div>
                                            </div>
                                        </div>
                                    <?php 
            }
            ?>
                                </div>
                            </div>
                        <?php 
        }
        ?>
                    </div>
                <?php 
    }
    ?>
            <?php 
}
?>
        </div>
    </div>
</div>

<div class="image-lightbox" id="imageLightbox" onclick="closeImageLightbox(event)">
    <div class="image-lightbox-inner">
        <button type="button" class="image-lightbox-close" onclick="closeImageLightbox(event)" aria-label="Close image preview">×</button>
        <img id="imageLightboxImg" src="" alt="Archived event preview">
    </div>
</div>

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
</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());