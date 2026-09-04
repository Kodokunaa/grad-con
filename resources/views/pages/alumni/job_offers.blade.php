
<style>
body {
    background: #f8fafc;
    margin: 0;
    padding-top: 64px;
    overflow-x: hidden;
}
.content {
    margin-left: 270px;
    width: calc(100% - 270px);
    padding: 84px 24px 30px;
    min-height: calc(100vh - 100px);
    max-width: none;
}
.page-title {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 8px;
}
@media (max-width: 992px) {
    .content {
        margin-left: 0;
        width: 100%;
        max-width: none;
        padding: 80px 16px 24px;
    }

    .offer-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .offer-status-badge {
        width: auto;
    }
}
.page-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 24px;
}
.offers-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}
.offer-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.offer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}
.offer-employer {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}
.offer-date {
    font-size: 13px;
    color: #9ca3af;
}
.offer-status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-sent {
    background: #dbeafe;
    color: #1e40af;
}
.status-accepted {
    background: #dcfce7;
    color: #166534;
}
.status-declined {
    background: #fee2e2;
    color: #991b1b;
}
.offer-message {
    background: #f9fafb;
    padding: 15px;
    border-left: 4px solid #f97316;
    border-radius: 4px;
    margin: 16px 0;
    font-size: 14px;
    line-height: 1.6;
    color: #374151;
}
.offer-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}
.btn-accept,
.btn-decline {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-accept {
    background: #10b981;
    color: white;
}
.btn-accept:hover {
    background: #059669;
}
.btn-decline {
    background: #ef4444;
    color: white;
}
.btn-decline:hover {
    background: #dc2626;
}
.btn-disabled {
    background: #d1d5db;
    color: #6b7280;
    cursor: not-allowed;
    opacity: 0.6;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
.empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
}
.alert-box {
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}
.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>

<div class="content">
    <div class="page-title">Job Offers</div>
    <div class="page-subtitle">Review and respond to job offers from employers</div>

    <?php 
if ($msg) {
    ?>
        <div class="alert-box alert-success"><?php 
    echo htmlspecialchars($msg);
    ?></div>
    <?php 
}
?>

    <?php 
if ($error) {
    ?>
        <div class="alert-box alert-error"><?php 
    echo htmlspecialchars($error);
    ?></div>
    <?php 
}
?>

    <div class="offers-container">
        <?php 
if (empty($offers)) {
    ?>
            <div class="empty-state">
                <div class="empty-state-icon">📨</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No job offers yet</p>
                <p>Employers will send you job offers here. Check back regularly!</p>
            </div>
        <?php 
} else {
    ?>
            <?php 
    foreach ($offers as $offer) {
        ?>
                <div class="offer-card">
                    <div class="offer-header">
                        <div>
                            <div class="offer-employer"><?php 
        echo htmlspecialchars($offer['employer_name'] ?? 'Employer');
        ?></div>
                            <div class="offer-date"><?php 
        echo date('F d, Y', strtotime($offer['created_at']));
        ?></div>
                        </div>
                        <span class="offer-status-badge status-<?php 
        echo htmlspecialchars($offer['status']);
        ?>">
                            <?php 
        echo ucfirst(htmlspecialchars($offer['status']));
        ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 15px; font-weight: 600; color: #1f2937; margin-bottom: 8px;">
                            <?php 
        echo htmlspecialchars($offer['subject']);
        ?>
                        </div>
                    </div>

                    <div class="offer-message">
                        <?php 
        echo nl2br(htmlspecialchars($offer['message']));
        ?>
                    </div>

                    <?php 
        if ($offer['status'] === 'sent') {
            ?>
                        <form method="POST" style="margin-top: 16px;">
@csrf
                            <input type="hidden" name="offer_id" value="<?php 
            echo (int) $offer['id'];
            ?>">
                            <div class="offer-actions">
                                <button type="submit" name="offer_action" value="accept" class="btn-accept">✓ Accept Invitation</button>
                                <button type="submit" name="offer_action" value="decline" class="btn-decline">✗ Decline Invitation</button>
                            </div>
                        </form>
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

<?php 
echo view('partials.footer', \get_defined_vars());