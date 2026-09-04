
<style>
	* { box-sizing: border-box; }
	body { background: #f4f6fb; color: #1f2937; overflow-x: hidden; }
	.content { margin-left: 290px; width: calc(100% - 290px); min-height: 100vh; padding: 28px 22px 48px; }
	.archive-wrapper { max-width: 980px; margin: 0 auto; }
	.archive-hero { position: relative; overflow: hidden; margin-bottom: 18px; padding: 30px; border-radius: 24px; color: #fff; background: radial-gradient(circle at 10% 20%, rgba(255,255,255,.34), transparent 27%), linear-gradient(135deg, #111827 0%, #334155 45%, #f97316 100%); box-shadow: 0 18px 42px rgba(15,23,42,.18); }
	.archive-hero::after { content: ""; position: absolute; width: 190px; height: 190px; right: -60px; top: -60px; border-radius: 50%; background: rgba(255,255,255,.14); }
	.archive-hero h1 { position: relative; z-index: 1; margin: 0 0 7px; font-size: 32px; font-weight: 900; }
	.archive-hero p { position: relative; z-index: 1; max-width: 620px; margin: 0; color: #e2e8f0; font-size: 14px; line-height: 1.6; }
	.alert-box { margin-bottom: 14px; padding: 14px 16px; border-radius: 14px; font-weight: 800; }
	.alert-success { color: #166534; background: #ecfdf5; border: 1px solid #bbf7d0; }
	.alert-error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
	.empty-state, .event-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 3px 12px rgba(15,23,42,.06); }
	.empty-state { padding: 58px 20px; color: #6b7280; text-align: center; }
	.empty-icon { margin-bottom: 12px; font-size: 48px; }
	.empty-title { margin-bottom: 6px; color: #111827; font-size: 19px; font-weight: 900; }
	.empty-text { font-size: 14px; }
	.event-card { overflow: hidden; margin-bottom: 16px; }
	.event-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 17px 18px 12px; }
	.poster { display: flex; align-items: center; gap: 11px; min-width: 0; }
	.poster-avatar { display: flex; align-items: center; justify-content: center; width: 46px; height: 46px; flex: 0 0 auto; border-radius: 50%; color: #ea580c; background: #fff7ed; border: 2px solid #fed7aa; font-weight: 900; }
	.poster-name { margin: 0; color: #111827; font-size: 15px; font-weight: 900; }
	.posted-date { margin-top: 3px; color: #6b7280; font-size: 12px; }
	.archive-badge { padding: 7px 10px; border-radius: 999px; color: #92400e; background: #fef3c7; border: 1px solid #fde68a; font-size: 12px; font-weight: 900; white-space: nowrap; }
	.event-body { padding: 0 18px 16px; }
	.event-title { margin: 3px 0 8px; color: #111827; font-size: 21px; line-height: 1.3; font-weight: 900; }
	.event-description { color: #374151; font-size: 14px; line-height: 1.6; white-space: pre-line; }
	.schedule { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 13px; }
	.schedule span { padding: 7px 10px; border-radius: 999px; color: #374151; background: #f8fafc; border: 1px solid #e5e7eb; font-size: 12px; font-weight: 800; }
	.event-image { display: block; width: 100%; max-height: 430px; object-fit: cover; border-top: 1px solid #eef2f7; border-bottom: 1px solid #eef2f7; }
	.event-footer { display: flex; justify-content: flex-end; padding: 11px 18px; background: #fff; }
	.restore-btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 14px; border-radius: 10px; color: #fff; background: #16a34a; text-decoration: none; font-size: 13px; font-weight: 900; }
	.restore-btn:hover { color: #fff; background: #15803d; }
		.comments-section { padding: 14px 18px 16px; border-top: 1px solid #eef2f7; background: #fcfcfd; }
		.comments-heading { margin-bottom: 10px; color: #475569; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; }
		.comment-item { display: flex; align-items: flex-start; gap: 9px; margin-bottom: 10px; }
		.comment-avatar { display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; flex: 0 0 auto; border-radius: 50%; color: #fff; background: linear-gradient(135deg, #f97316, #16a34a); font-size: 11px; font-weight: 900; }
		.comment-bubble { flex: 1; padding: 9px 11px; border-radius: 12px; background: #f1f5f9; }
		.comment-meta { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 3px; }
		.comment-name { color: #111827; font-size: 13px; font-weight: 900; }
		.comment-date { color: #64748b; font-size: 11px; }
		.comment-text { color: #374151; font-size: 13px; line-height: 1.45; white-space: pre-line; word-break: break-word; }
		.comment-delete { margin-top: 5px; padding: 0; border: 0; color: #dc2626; background: transparent; font-size: 11px; font-weight: 800; cursor: pointer; }
		.comment-delete:hover { text-decoration: underline; }
	@media (max-width: 991.98px) { .content { margin-left: 0; width: 100%; padding: 20px 14px 36px; } }
	@media (max-width: 575.98px) { .archive-hero { padding: 24px 20px; } .archive-hero h1 { font-size: 27px; } .event-header { flex-direction: column; } .archive-badge { align-self: flex-start; } }
</style>

<div class="content">
	<div class="archive-wrapper">
		<section class="archive-hero">
			<h1>Archived Events</h1>
			<p>Events archived from the active event list are stored here and can be restored whenever they need to be published again.</p>
		</section>

		<?php
if ($message) {
    ?><div class="alert-box alert-success"><?php
    echo e($message);
    ?></div><?php
}
		?>
		<?php
if ($error) {
    ?><div class="alert-box alert-error"><?php
    echo e($error);
    ?></div><?php
}
		?>

		<?php
if (empty($archivedEvents)) {
    ?>
			<div class="empty-state">
				<div class="empty-icon">🗂️</div>
				<div class="empty-title">No archived events yet</div>
				<div class="empty-text">Events archived from the Event List will appear here.</div>
			</div>
		<?php
} else {
    ?>
			<?php
    foreach ($archivedEvents as $event) {
        ?>
				<article class="event-card">
					<div class="event-header">
						<div class="poster">
							<div class="poster-avatar">📅</div>
							<div>
								<h2 class="poster-name"><?php
        echo e($event['poster'] ?? 'Unknown');
        ?></h2>
								<div class="posted-date">Archived on <?php
        echo e(\App\Support\ViewFormatter::admin_admin_archive_format_date($event['archived_at'] ?? ''));
        ?></div>
							</div>
						</div>
						<span class="archive-badge">Archived Event</span>
					</div>
					<div class="event-body">
						<h3 class="event-title"><?php
        echo e($event['title'] ?? 'Untitled event');
        ?></h3>
						<div class="event-description"><?php
        echo e($event['content'] ?? 'No description provided.');
        ?></div>
						<div class="schedule">
							<span>Start: <?php
        echo e(\App\Support\ViewFormatter::admin_admin_archive_format_date($event['post_start_date'] ?? ''));
        ?></span>
							<span>End: <?php
        echo e(\App\Support\ViewFormatter::admin_admin_archive_format_date($event['post_end_date'] ?? ''));
        ?></span>
						</div>
					</div>
					<?php
        if (! empty($event['image'])) {
            ?>
						<img class="event-image" src="<?php
            echo \url('');
            ?>/uploads/events/<?php
            echo e($event['image']);
            ?>" alt="Archived event image">
					<?php
        }
        ?>
					<div class="event-footer">
						<form method="POST" action="{{ route('events.restore', $event['id']) }}" onsubmit="return confirm('Restore this event to the active event list?');">
                            @csrf
                            @method('PATCH')
                            <button class="restore-btn" type="submit">↺ Restore Event</button>
                        </form>
					</div>
					<?php
        if (! empty($event['comments'])) {
            ?>
						<div class="comments-section">
							<div class="comments-heading">Comments (<?php
            echo count($event['comments']);
            ?>)</div>
							<?php
            foreach ($event['comments'] as $comment) {
                ?>
								<div class="comment-item">
									<div class="comment-avatar"><?php
                echo e(strtoupper(substr(trim($comment['fullname'] ?? 'U'), 0, 1)));
                ?></div>
									<div class="comment-bubble">
										<div class="comment-meta">
											<span class="comment-name"><?php
                echo e($comment['fullname'] ?? 'Unknown user');
                ?></span>
											<span class="comment-date"><?php
                echo e(\App\Support\ViewFormatter::admin_admin_archive_format_date($comment['created_at'] ?? ''));
                ?></span>
										</div>
										<div class="comment-text"><?php
                echo e($comment['comment'] ?? '');
                ?></div>
										<form method="POST" action="{{ url('/admin/archive/events/'.$event['id'].'/comments/'.$comment['id']) }}" onsubmit="return confirm('Delete this comment?');">
@csrf
@method('DELETE')
											<button type="submit" class="comment-delete">Delete comment</button>
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
				</article>
			<?php
    }
    ?>
		<?php
}
		?>
	</div>
</div>

<?php
		echo view('partials.footer', \get_defined_vars());
