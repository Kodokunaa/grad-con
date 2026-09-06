
<style>
    * { box-sizing: border-box; }

    body {
        background: #f4f6fb;
        min-height: 100vh;
        overflow-x: hidden;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        color: #111827;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        min-height: 100vh;
        padding: 28px 22px 48px;
    }

    .create-wrapper {
        max-width: 980px;
        margin: 0 auto;
    }

    .hero-card {
        background:
            radial-gradient(circle at top left, rgba(255,255,255,.35), transparent 28%),
            linear-gradient(135deg, #f97316 0%, #fb923c 48%, #16a34a 100%);
        border-radius: 22px;
        padding: 30px;
        color: #ffffff;
        box-shadow: 0 18px 45px rgba(249, 115, 22, 0.20);
        margin-bottom: 18px;
        position: relative;
        overflow: hidden;
    }

    .hero-card::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: linear-gradient(45deg, rgba(255,255,255,.10) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.10) 50%, rgba(255,255,255,.10) 75%, transparent 75%, transparent);
        background-size: 46px 46px;
        opacity: .25;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .hero-title {
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -0.03em;
        margin: 0 0 6px;
    }

    .hero-subtitle {
        margin: 0;
        font-size: 15px;
        opacity: .92;
        font-weight: 600;
    }

    .hero-icon {
        width: 78px;
        height: 78px;
        border-radius: 22px;
        background: rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 38px;
        border: 1px solid rgba(255,255,255,.30);
        backdrop-filter: blur(10px);
    }

    .alert-box {
        padding: 14px 16px;
        border-radius: 14px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
    }

    .alert-success-custom {
        background: #ecfdf5;
        color: #065f46;
        border-color: #bbf7d0;
    }

    .alert-danger-custom {
        background: #fef2f2;
        color: #7f1d1d;
        border-color: #fecaca;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .form-topbar {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        background: #ffffff;
    }

    .form-heading {
        font-size: 18px;
        font-weight: 900;
        margin: 0;
        color: #111827;
    }

    .form-note {
        font-size: 13px;
        color: #6b7280;
        font-weight: 700;
        margin-top: 3px;
    }

    .status-helper {
        padding: 8px 12px;
        border-radius: 999px;
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .form-body {
        padding: 26px 24px 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 900;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .form-control-custom,
    .form-textarea-custom,
    .form-file-custom {
        width: 100%;
        padding: 13px 15px;
        border: 1px solid #dbe3ef;
        border-radius: 14px;
        font-size: 14px;
        background: #f8fafc;
        color: #111827;
        outline: none;
        transition: .2s ease;
        font-family: inherit;
    }

    .form-control-custom:hover,
    .form-textarea-custom:hover,
    .form-file-custom:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .form-control-custom:focus,
    .form-textarea-custom:focus,
    .form-file-custom:focus {
        background: #ffffff;
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, .12);
    }

    .form-textarea-custom {
        min-height: 170px;
        resize: vertical;
        line-height: 1.7;
    }

    .helper-text {
        color: #64748b;
        font-size: 12px;
        margin-top: 7px;
        font-weight: 600;
        line-height: 1.5;
    }

    .schedule-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .schedule-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 900;
        color: #111827;
        margin-bottom: 5px;
    }

    .schedule-subtitle {
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
        padding-top: 22px;
        border-top: 1px solid #eef2f7;
    }

    .btn-orange,
    .btn-outline-custom {
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 900;
        transition: .2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 12px 20px;
        cursor: pointer;
    }

    .btn-orange {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #ffffff;
        border: none;
        box-shadow: 0 10px 22px rgba(249, 115, 22, .22);
    }

    .btn-orange:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(249, 115, 22, .30);
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-outline-custom:hover {
        background: #f3f4f6;
        color: #111827;
        border-color: #f97316;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 14px 36px;
        }

        .hero-title {
            font-size: 26px;
        }
    }

    @media (max-width: 767.98px) {
        .hero-card { padding: 22px; }
        .hero-icon { width: 62px; height: 62px; font-size: 30px; }
        .form-topbar, .form-body { padding: 18px; }
        .form-grid { grid-template-columns: 1fr; }
        .actions { flex-direction: column; }
        .btn-orange, .btn-outline-custom { width: 100%; }
    }
</style>

<div class="content">
    <div class="create-wrapper">
        <section class="hero-card">
            <div class="hero-content">
                <div>
                    <h1 class="hero-title">Create Community Post</h1>
                    <p class="hero-subtitle">Create polished community posts with scheduled visibility for alumni users.</p>
                </div>
                <div class="hero-icon">📅</div>
            </div>
        </section>

        <?php
if ($msg) {
    ?>
            <div class="alert-box alert-success-custom"><?php
    echo e($msg);
    ?></div>
        <?php
}
        ?>

        <?php
if ($error) {
    ?>
            <div class="alert-box alert-danger-custom"><?php
    echo e($error);
    ?></div>
        <?php
}
        ?>

        <section class="form-card">
            <div class="form-topbar">
                <div>
                    <h2 class="form-heading">Event Information</h2>
                    <div class="form-note">Fill in the details below to publish or schedule your post.</div>
                </div>
                <div class="status-helper">Advanced Posting Enabled</div>
            </div>

            <div class="form-body">
                <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
@csrf
                    <div class="form-group"><label class="form-label">Post Type</label><select name="category" class="form-control-custom" required><option value="announcement" @selected(old('category','announcement') === 'announcement')>Announcement</option><option value="news" @selected(old('category') === 'news')>News</option><option value="event" @selected(old('category') === 'event')>Event</option></select></div>
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control-custom"
                            value="<?php
        echo e(old('title', request()->input('title')) ?? '');
        ?>"
                            placeholder="Enter event title"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Content</label>
                        <textarea
                            name="content"
                            rows="6"
                            class="form-textarea-custom"
                            placeholder="Write the community post, details, requirements, or reminders..."
                            required
                        ><?php
        echo e(old('content', request()->input('content')) ?? '');
        ?></textarea>
                    </div>

                    <div class="schedule-box">
                        <div class="schedule-title">⏰ Advanced Posting Schedule</div>
                        <div class="schedule-subtitle">
                            Leave the start date empty to show the post immediately. Leave the end date empty if the post should not expire.
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Post Start Date</label>
                                <input
                                    type="datetime-local"
                                    name="post_start_date"
                                    class="form-control-custom"
                                    value="<?php
        echo e(old('post_start_date', request()->input('post_start_date')) ?? '');
        ?>"
                                >
                                <div class="helper-text">The post becomes visible to alumni on this date and time.</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Post End Date</label>
                                <input
                                    type="datetime-local"
                                    name="post_end_date"
                                    class="form-control-custom"
                                    value="<?php
        echo e(old('post_end_date', request()->input('post_end_date')) ?? '');
        ?>"
                                >
                                <div class="helper-text">The post will no longer appear in alumni feed after this date and time.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Image (optional)</label>
                        <input
                            type="file"
                            name="image"
                            class="form-file-custom"
                            accept="image/*"
                        >
                        <div class="helper-text">Allowed file types: jpg, jpeg, png, gif, webp. Max 3MB.</div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn-orange">📤 Publish Post</button>
                        <a class="btn-outline-custom" href="<?php
        echo \url('');
        ?>/admin/events_list">← View Posts</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<?php
        echo view('partials.footer', \get_defined_vars());
