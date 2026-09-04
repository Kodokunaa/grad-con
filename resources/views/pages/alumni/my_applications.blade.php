
<style>
    body {
        background: #f8fafc;
        overflow-x: hidden;
    }

    .content {
        margin-left: 290px;
        width: calc(100% - 290px);
        max-width: 100%;
        padding: 30px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .browse-btn {
        background: #ffffff;
        color: #374151;
        text-decoration: none;
        border: 1px solid #d1d5db;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
    }

    .browse-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .application-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        height: 100%;
        transition: 0.3s ease;
    }

    .application-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.07);
    }

    .job-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .job-subtitle {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .job-meta {
        color: #6b7280;
        font-size: 13px;
        margin-top: 4px;
    }

    .status-badge {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        text-transform: capitalize;
    }

    .status-pending {
        background: #e5e7eb;
        color: #374151;
    }

    .status-review {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-interview {
        background: #ede9fe;
        color: #6d28d9;
    }

    .status-accepted {
        background: #dcfce7;
        color: #166534;
    }

    .status-hired {
        background: #bbf7d0;
        color: #14532d;
    }

    .status-rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-cancelled {
        background: #fef3c7;
        color: #92400e;
    }

    .divider {
        border: 0;
        border-top: 1px solid #e5e7eb;
        margin: 16px 0;
    }

    .info-label {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }

    .info-text {
        color: #6b7280;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .status-note {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
    }

    .cancel-reason-box {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 14px;
        line-height: 1.6;
    }

    .cancel-btn {
        background: #dc2626;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
        cursor: pointer;
    }

    .cancel-btn:hover {
        background: #b91c1c;
        color: #ffffff;
    }

    .remove-btn {
        background: #ef4444;
        color: #ffffff;
        text-decoration: none;
        border: none;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        transition: 0.3s ease;
        display: inline-block;
        cursor: pointer;
        margin-top: 14px;
    }

    .remove-btn:hover {
        background: #c81f1f;
        color: #ffffff;
    }

    .empty-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 24px;
        color: #6b7280;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    }

    .alert-box {
        padding: 12px 14px;
        border-radius: 10px;
        margin-bottom: 18px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-success-custom {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger-custom {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .timeline {
        margin-top: 18px;
    }

    .timeline-title {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 12px;
    }

    .timeline-steps {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 70px;
        text-align: center;
        flex: 1;
        position: relative;
    }

    .timeline-step::after {
        content: "";
        position: absolute;
        top: 16px;
        left: calc(50% + 18px);
        width: calc(100% - 36px);
        height: 3px;
        background: #e5e7eb;
        z-index: 1;
    }

    .timeline-step:last-child::after {
        display: none;
    }

    .timeline-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        z-index: 2;
        position: relative;
    }

    .timeline-label {
        margin-top: 8px;
        font-size: 12px;
        color: #6b7280;
        line-height: 1.3;
    }

    .timeline-step.active .timeline-circle {
        background: #2563eb;
        color: #ffffff;
    }

    .timeline-step.done .timeline-circle {
        background: #16a34a;
        color: #ffffff;
    }

    .timeline-step.done::after {
        background: #16a34a;
    }

    .final-status-box {
        margin-top: 18px;
        padding: 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }

    .final-accepted {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .final-hired {
        background: #bbf7d0;
        color: #14532d;
        border: 1px solid #86efac;
    }

    .final-rejected {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .final-cancelled {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .cancel-modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 15px;
    }

    .cancel-modal-bg.show {
        display: flex;
    }

    .cancel-modal {
        background: #ffffff;
        width: 100%;
        max-width: 480px;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
    }

    .cancel-modal h4 {
        margin: 0 0 8px 0;
        color: #111827;
        font-size: 20px;
        font-weight: 700;
    }

    .cancel-modal p {
        margin: 0 0 14px 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
    }

    .cancel-modal textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 12px;
        resize: vertical;
        outline: none;
        font-size: 14px;
        color: #111827;
    }

    .cancel-modal textarea:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .modal-actions {
        margin-top: 16px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .modal-close-btn {
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #374151;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .modal-close-btn:hover {
        background: #f3f4f6;
    }

    @media (max-width: 991.98px) {
        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
        }

        .page-title {
            font-size: 24px;
        }

        .timeline-steps {
            gap: 14px;
        }

        .timeline-step {
            min-width: 90px;
            flex: unset;
        }

        .timeline-step::after {
            display: none;
        }
    }
</style>

<div class="content">
    <div class="page-header">
        <h3 class="page-title">My Applications</h3>
        <a class="browse-btn" href="<?php 
echo \url('');
?>/alumni/jobs.php">Browse Jobs</a>
    </div>

    <?php 
if ($msg) {
    ?>
        <div class="alert-box alert-success-custom">
            <?php 
    echo htmlspecialchars($msg);
    ?>
        </div>
    <?php 
}
?>

    <?php 
if ($error) {
    ?>
        <div class="alert-box alert-danger-custom">
            <?php 
    echo htmlspecialchars($error);
    ?>
        </div>
    <?php 
}
?>

    <div class="row g-3">
        <?php 
if (count($apps) === 0) {
    ?>
            <div class="col-12">
                <div class="empty-card">
                    You haven’t applied to any jobs yet.
                </div>
            </div>
        <?php 
} else {
    ?>

            <?php 
    foreach ($apps as $a) {
        ?>
                <?php 
        $status = \gc_alumni_my_applications_normalize_status($a['status'] ?? 'pending');
        $statusLabel = \gc_alumni_my_applications_get_status_label($status);
        $statusClass = \gc_alumni_my_applications_get_status_class($status);
        $statusNote = \gc_alumni_my_applications_get_status_note($status);
        $progressStep = \gc_alumni_my_applications_get_progress_step($status);
        $isFinalRejected = $status === 'rejected';
        $isFinalCancelled = $status === 'cancelled';
        $isFinalAccepted = $status === 'accepted';
        $isFinalHired = $status === 'hired';
        ?>

                <div class="col-lg-6">
                    <div class="application-card">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="job-title"><?php 
        echo htmlspecialchars($a['title']);
        ?></div>
                                <div class="job-subtitle">
                                    <?php 
        echo htmlspecialchars($a['company']);
        ?>
                                    <?php 
        if (!empty($a['location'])) {
            ?>
                                        • <?php 
            echo htmlspecialchars($a['location']);
            ?>
                                    <?php 
        }
        ?>
                                </div>
                                <div class="job-meta">
                                    Type: <?php 
        echo htmlspecialchars($a['job_type']);
        ?>
                                </div>
                            </div>

                            <div>
                                <span class="status-badge <?php 
        echo $statusClass;
        ?>">
                                    <?php 
        echo htmlspecialchars($statusLabel);
        ?>
                                </span>
                            </div>
                        </div>

                        <hr class="divider">

                        <div class="info-text">
                            Applied on:
                            <?php 
        echo !empty($a['created_at']) ? htmlspecialchars(date("F d, Y h:i A", strtotime($a['created_at']))) : 'N/A';
        ?>
                        </div>

                        <?php 
        if (!empty($a['message'])) {
            ?>
                            <div class="mt-3">
                                <div class="info-label">Your Message</div>
                                <div class="info-text"><?php 
            echo nl2br(htmlspecialchars($a['message']));
            ?></div>
                            </div>
                        <?php 
        }
        ?>

                        <div class="status-note">
                            <strong>Application Status:</strong> <?php 
        echo htmlspecialchars($statusLabel);
        ?><br>
                            <?php 
        echo htmlspecialchars($statusNote);
        ?>
                        </div>

                        <?php 
        if (!$isFinalRejected && !$isFinalCancelled) {
            ?>
                            <div class="timeline">
                                <div class="timeline-title">Application Progress</div>
                                <div class="timeline-steps">
                                    <div class="timeline-step <?php 
            echo $progressStep > 1 ? 'done' : ($progressStep == 1 ? 'active' : '');
            ?>">
                                        <div class="timeline-circle">1</div>
                                        <div class="timeline-label">Applied</div>
                                    </div>

                                    <div class="timeline-step <?php 
            echo $progressStep > 2 ? 'done' : ($progressStep == 2 ? 'active' : '');
            ?>">
                                        <div class="timeline-circle">2</div>
                                        <div class="timeline-label">Under Review</div>
                                    </div>

                                    <div class="timeline-step <?php 
            echo $progressStep > 3 ? 'done' : ($progressStep == 3 ? 'active' : '');
            ?>">
                                        <div class="timeline-circle">3</div>
                                        <div class="timeline-label">For Interview</div>
                                    </div>

                                    <div class="timeline-step <?php 
            echo $progressStep > 4 ? 'done' : ($progressStep == 4 ? 'active' : '');
            ?>">
                                        <div class="timeline-circle">4</div>
                                        <div class="timeline-label">Accepted</div>
                                    </div>

                                    <div class="timeline-step <?php 
            echo $progressStep == 5 ? 'active done' : '';
            ?>">
                                        <div class="timeline-circle">5</div>
                                        <div class="timeline-label">Hired</div>
                                    </div>
                                </div>
                            </div>
                        <?php 
        }
        ?>

                        <?php 
        if ($isFinalRejected) {
            ?>
                            <div class="final-status-box final-rejected">
                                Final Result: This application has been rejected.
                            </div>
                        <?php 
        }
        ?>

                        <?php 
        if ($isFinalCancelled) {
            ?>
                            <div class="final-status-box final-cancelled">
                                Final Result: You cancelled this application.
                            </div>

                            <?php 
            if (!empty($a['cancel_reason'])) {
                ?>
                                <div class="cancel-reason-box">
                                    <strong>Cancellation Reason:</strong><br>
                                    <?php 
                echo nl2br(htmlspecialchars($a['cancel_reason']));
                ?>

                                    <?php 
                if (!empty($a['cancelled_at'])) {
                    ?>
                                        <br><br>
                                        <small>
                                            Cancelled on:
                                            <?php 
                    echo htmlspecialchars(date("F d, Y h:i A", strtotime($a['cancelled_at'])));
                    ?>
                                        </small>
                                    <?php 
                }
                ?>
                                </div>
                            <?php 
            }
            ?>

                            <form method="POST" action="{{ url('/alumni/applications/'.$a['id']) }}" onsubmit="return confirm('Are you sure you want to remove this cancelled application?');">
@csrf
@method('DELETE')
                                <button type="submit" class="remove-btn">Remove Application</button>
                            </form>
                        <?php 
        }
        ?>

                        <?php 
        if ($isFinalAccepted) {
            ?>
                            <div class="final-status-box final-accepted">
                                Final Result: Your application has been accepted.
                            </div>
                        <?php 
        }
        ?>

                        <?php 
        if ($isFinalHired) {
            ?>
                            <div class="final-status-box final-hired">
                                Final Result: You have been marked as hired.
                            </div>
                        <?php 
        }
        ?>

                        <div class="actions">
                            <?php 
        if (in_array($status, ['pending', 'under_review', 'for_interview'])) {
            ?>
                                <button type="button"
                                        class="cancel-btn"
                                        onclick="openCancelModal(<?php 
            echo (int) $a['id'];
            ?>, '<?php 
            echo htmlspecialchars(addslashes($a['title']));
            ?>')">
                                    Cancel Application
                                </button>
                            <?php 
        } else {
            ?>
                                <span class="info-text">No available action for this application.</span>
                            <?php 
        }
        ?>
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

<div class="cancel-modal-bg" id="cancelModal">
    <div class="cancel-modal">
        <h4>Cancel Application</h4>
        <p id="cancelModalText">
            Please provide your reason for cancelling this application. This reason will be visible to the employer/admin.
        </p>

        <form method="POST" id="cancelApplicationForm" onsubmit="return validateCancelReason();">
@csrf
@method('PATCH')
            <input type="hidden" name="application_id" id="cancel_application_id">

            <textarea name="cancel_reason"
                      id="cancel_reason"
                      placeholder="Example: I would like to cancel because I already accepted another opportunity..."
                      required></textarea>

            <div class="modal-actions">
                <button type="button" class="modal-close-btn" onclick="closeCancelModal()">Close</button>
                <button type="submit" name="cancel_application" class="cancel-btn">
                    Submit Cancellation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCancelModal(applicationId, jobTitle) {
        document.getElementById('cancel_application_id').value = applicationId;
        document.getElementById('cancelApplicationForm').action = '{{ url('/alumni/applications') }}/' + applicationId + '/cancel';
        document.getElementById('cancel_reason').value = '';

        document.getElementById('cancelModalText').innerHTML =
            'Please provide your reason for cancelling your application for <strong>' +
            jobTitle +
            '</strong>. This reason will be visible to the employer/admin.';

        document.getElementById('cancelModal').classList.add('show');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.remove('show');
    }

    function validateCancelReason() {
        let reason = document.getElementById('cancel_reason').value.trim();

        if (reason.length < 10) {
            alert('Please enter a clearer reason before cancelling. Minimum of 10 characters is required.');
            return false;
        }

        return confirm('Are you sure you want to cancel this application?');
    }

    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCancelModal();
        }
    });
</script>

<?php 
echo \gc_partial('footer', \get_defined_vars());
