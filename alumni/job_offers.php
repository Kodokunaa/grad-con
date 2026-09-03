<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";

require_login();

if ($_SESSION['user']['role'] !== 'alumni') {
    header("Location: " . BASE_URL . "/");
    exit;
}

$id = (int)$_SESSION['user']['id'];
$error = "";
$msg = "";

// Handle accept/decline from email links
$emailToken = trim((string)($_GET['accept'] ?? $_GET['decline'] ?? ''));
if ($emailToken !== '') {
    $action = isset($_GET['accept']) ? 'accept' : 'decline';

    try {
        // Get the offer by token
        $offerStmt = $pdo->prepare("SELECT * FROM job_offers WHERE offer_token = ? LIMIT 1");
        $offerStmt->execute([$emailToken]);
        $offer = $offerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            $error = "Offer not found. It may have expired.";
        } elseif ($offer['alumni_id'] !== $id) {
            $error = "This offer was not sent to you.";
        } elseif ($offer['status'] !== 'sent') {
            $error = "This offer has already been " . ($offer['status'] === 'accepted' ? 'accepted' : 'declined') . ".";
        } else {
            $newStatus = $action === 'accept' ? 'accepted' : 'declined';
            $timestampCol = $action === 'accept' ? 'accepted_at' : 'declined_at';

            $updateStmt = $pdo->prepare("UPDATE job_offers SET status = ?, {$timestampCol} = NOW() WHERE id = ?");
            $updateStmt->execute([$newStatus, $offer['id']]);

            if ($action === 'accept') {
                $msg = "✓ Offer accepted successfully! Please wait for the employer to set an interview schedule.";
            } else {
                $msg = "Offer declined successfully!";
            }

            // Send notification email to employer if accepted
            if ($action === 'accept') {
                send_offer_acceptance_notification($pdo, $offer, $_SESSION['user']);
            }
        }
    } catch (Throwable $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}

// Handle accept/decline action from form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_action'])) {
    $offerId = (int)($_POST['offer_id'] ?? 0);
    $action = trim((string)($_POST['offer_action'] ?? ''));

    if (!in_array($action, ['accept', 'decline'], true)) {
        $error = "Invalid action.";
    } elseif ($offerId <= 0) {
        $error = "Invalid offer ID.";
    } else {
        try {
            // Get the offer
            $offerStmt = $pdo->prepare("SELECT * FROM job_offers WHERE id = ? AND alumni_id = ? LIMIT 1");
            $offerStmt->execute([$offerId, $id]);
            $offer = $offerStmt->fetch(PDO::FETCH_ASSOC);

            if (!$offer) {
                $error = "Offer not found.";
            } elseif ($offer['status'] !== 'sent') {
                $error = "This offer has already been " . ($offer['status'] === 'accepted' ? 'accepted' : 'declined') . ".";
            } else {
                $newStatus = $action === 'accept' ? 'accepted' : 'declined';
                $timestampCol = $action === 'accept' ? 'accepted_at' : 'declined_at';

                $updateStmt = $pdo->prepare("UPDATE job_offers SET status = ?, {$timestampCol} = NOW() WHERE id = ?");
                $updateStmt->execute([$newStatus, $offerId]);

                if ($action === 'accept') {
                    $msg = "✓ Offer accepted successfully! Please wait for the employer to set an interview schedule.";
                } else {
                    $msg = "Offer declined successfully!";
                }

                // Send notification email to employer
                if ($action === 'accept') {
                    send_offer_acceptance_notification($pdo, $offer, $_SESSION['user']);
                }
            }
        } catch (Throwable $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

// Fetch all offers for this alumni
$offersStmt = $pdo->prepare("
    SELECT jo.*, u.fullname as employer_name, u.email as employer_email
    FROM job_offers jo
    JOIN users u ON jo.employer_id = u.id
    WHERE jo.alumni_id = ?
    ORDER BY jo.created_at DESC
");
$offersStmt->execute([$id]);
$offers = $offersStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to send acceptance notification
function send_offer_acceptance_notification($pdo, $offer, $alumniUser) {
    try {
        require_once __DIR__ . "/../PHPMailer/src/Exception.php";
        require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
        require_once __DIR__ . "/../PHPMailer/src/SMTP.php";

        $employerStmt = $pdo->prepare("SELECT email, fullname FROM users WHERE id = ? LIMIT 1");
        $employerStmt->execute([$offer['employer_id']]);
        $employer = $employerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$employer || empty($employer['email'])) {
            return;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cccgradconn@gmail.com';
        $mail->Password   = 'anhfwyyhoqannyll';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('cccgradconn@gmail.com', 'Job Portal Admin');
        $mail->addReplyTo('cccgradconn@gmail.com', 'Job Portal Admin');
        $mail->addAddress($employer['email'], $employer['fullname'] ?? 'Employer');
        $mail->isHTML(true);

        $alumniName = htmlspecialchars($alumniUser['fullname'] ?? 'Alumni');
        $offerLink = BASE_URL . "/employer/job_offers.php";

        $mail->Subject = "Job Offer Acceptance - " . $alumniName;
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #4CAF50; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
                    .details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                    .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>✓ Job Offer Accepted!</h2>
                    </div>
                    <div class='content'>
                        <p>Great news! <strong>$alumniName</strong> has accepted your job offer.</p>
                        
                        <div class='details'>
                            <p><strong>Alumni Name:</strong> $alumniName</p>
                            <p><strong>Email:</strong> " . htmlspecialchars($alumniUser['email'] ?? '') . "</p>
                            <p><strong>Accepted On:</strong> " . date('F d, Y H:i A') . "</p>
                        </div>

                        <p>You can now proceed to schedule an interview with this applicant. Log in to your employer dashboard to manage interviews and next steps.</p>

                        <a href='$offerLink' class='button'>View Job Offers</a>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
    } catch (Throwable $e) {
        // Silently fail - acceptance is already recorded
    }
}

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/alumni_sidebar.php";
?>

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

    <?php if ($msg): ?>
        <div class="alert-box alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-box alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="offers-container">
        <?php if (empty($offers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📨</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No job offers yet</p>
                <p>Employers will send you job offers here. Check back regularly!</p>
            </div>
        <?php else: ?>
            <?php foreach ($offers as $offer): ?>
                <div class="offer-card">
                    <div class="offer-header">
                        <div>
                            <div class="offer-employer"><?php echo htmlspecialchars($offer['employer_name'] ?? 'Employer'); ?></div>
                            <div class="offer-date"><?php echo date('F d, Y', strtotime($offer['created_at'])); ?></div>
                        </div>
                        <span class="offer-status-badge status-<?php echo htmlspecialchars($offer['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($offer['status'])); ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 15px; font-weight: 600; color: #1f2937; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($offer['subject']); ?>
                        </div>
                    </div>

                    <div class="offer-message">
                        <?php echo nl2br(htmlspecialchars($offer['message'])); ?>
                    </div>

                    <?php if ($offer['status'] === 'sent'): ?>
                        <form method="POST" style="margin-top: 16px;">
                            <input type="hidden" name="offer_id" value="<?php echo (int)$offer['id']; ?>">
                            <div class="offer-actions">
                                <button type="submit" name="offer_action" value="accept" class="btn-accept">✓ Accept Invitation</button>
                                <button type="submit" name="offer_action" value="decline" class="btn-decline">✗ Decline Invitation</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
