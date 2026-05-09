<?php
require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/db.php";
require_admin();

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)($_GET['delete']);

    $getImg = $pdo->prepare("SELECT image FROM trainings WHERE id=? LIMIT 1");
    $getImg->execute([$id]);
    $old = $getImg->fetch(PDO::FETCH_ASSOC);

    if ($old && !empty($old['image'])) {
        $imgPath = __DIR__ . "/../uploads/trainings/" . $old['image'];
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }

    $del = $pdo->prepare("DELETE FROM trainings WHERE id=?");
    $del->execute([$id]);

    header("Location: trainings_list.php");
    exit;
}

// FETCH
$trainings = $pdo->query("
    SELECT t.*, u.fullname
    FROM trainings t
    LEFT JOIN users u ON u.id = t.posted_by
    ORDER BY t.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/admin_sidebar.php";
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
body{
    background:#f8fafc;
    overflow-x:hidden;
}

.content{
    margin-left:290px;
    width:calc(100% - 290px);
    max-width:100%;
    padding:30px 24px;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}

.page-title{
    font-size:28px;
    font-weight:700;
    color:#1f2937;
    margin:0;
}

.create-btn{
    background:#f97316;
    color:#fff;
    padding:10px 16px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:.3s;
    display:inline-block;
}

.create-btn:hover{
    background:#16a34a;
    color:#fff;
}

.table-card{
    background:#fff;
    padding:20px;
    border-radius:16px;
    border:1px solid #e5e7eb;
    box-shadow:0 4px 14px rgba(0,0,0,0.05);
    overflow:hidden;
}

.badge-date{
    background:#f97316;
    color:#fff;
    padding:4px 10px;
    border-radius:8px;
    font-size:12px;
    font-weight:600;
    display:inline-block;
}

.btn-view{
    background:#3b82f6;
    color:#fff;
    padding:6px 10px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    border:none;
    display:inline-block;
}

.btn-edit{
    background:#f97316;
    color:#fff;
    padding:6px 10px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    border:none;
    display:inline-block;
}

.btn-delete{
    background:#ef4444;
    color:#fff;
    padding:6px 10px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    border:none;
    display:inline-block;
}

.btn-view:hover{ background:#2563eb; color:#fff; }
.btn-edit:hover{ background:#16a34a; color:#fff; }
.btn-delete:hover{ background:#dc2626; color:#fff; }

.img-thumb{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid #e5e7eb;
}

/* DataTables */
table.dataTable{
    width:100% !important;
}

table.dataTable thead th{
    background:#f9fafb !important;
    font-weight:700;
    font-size:14px;
    color:#374151;
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
}

table.dataTable tbody td{
    padding:14px !important;
    border-bottom:1px solid #e5e7eb !important;
    font-size:14px;
    color:#111827;
    vertical-align:middle;
}

table.dataTable tbody tr:hover{
    background:#fffaf5;
}

/* Top controls row */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 18px;
    font-size: 14px;
    color: #374151;
}

.dataTables_wrapper .dataTables_length {
    float: left;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}

.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #374151;
    margin: 0;
}

/* Show entries dropdown */
.dataTables_wrapper .dataTables_length select {
    min-width: 100px;
    height: 50px;
    padding: 10px 40px 10px 14px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 16px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-size: 16px !important;
    font-weight: 600;
    outline: none !important;
    box-shadow: none !important;
    appearance: auto;
    -webkit-appearance: menulist;
    -moz-appearance: menulist;
}

.dataTables_wrapper .dataTables_length select:focus {
    border-color: #f97316 !important;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.10) !important;
}

/* Search input */
.dataTables_wrapper .dataTables_filter input {
    width: 275px !important;
    max-width: 100%;
    height: 50px;
    padding: 10px 14px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    color: #111827 !important;
    font-size: 15px !important;
    margin-left: 8px !important;
    outline: none !important;
    box-shadow: none !important;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #f97316 !important;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.10) !important;
}

.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    color: #4b5563;
    font-size: 14px;
}

/* Clear float */
.dataTables_wrapper::after {
    content: "";
    display: block;
    clear: both;
}

/* pagination */
.dataTables_wrapper .dataTables_paginate{
    margin-top:14px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button{
    border-radius:8px !important;
    margin:0 2px;
    border:1px solid #e5e7eb !important;
    background:#fff !important;
    color:#374151 !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current{
    background:#f97316 !important;
    border:1px solid #f97316 !important;
    color:#fff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
    background:#16a34a !important;
    border:1px solid #16a34a !important;
    color:#fff !important;
}

/* modal */
.training-modal-image{
    width:100%;
    max-height:300px;
    object-fit:cover;
    border-radius:14px;
    border:1px solid #e5e7eb;
    margin-bottom:16px;
}

.training-meta{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
    margin-bottom:16px;
}

.training-box{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:12px 14px;
}

.training-label{
    font-size:12px;
    font-weight:700;
    color:#6b7280;
    text-transform:uppercase;
    margin-bottom:4px;
}

.training-value{
    font-size:14px;
    font-weight:600;
    color:#111827;
    word-break:break-word;
}

.training-content-box{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px;
}

.training-content-text{
    font-size:14px;
    color:#111827;
    white-space:pre-wrap;
}

@media (max-width:991px){
    .content{
        margin-left:0;
        width:100%;
        padding:20px 15px;
    }

    .training-meta{
        grid-template-columns:1fr;
    }
}

@media (max-width:768px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        float: none;
        text-align: left;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-top: 10px;
    }

    .dataTables_wrapper .dataTables_filter input {
        width: 100% !important;
        margin-left: 0 !important;
    }
}
</style>

<div class="content">

    <div class="page-header">
        <h3 class="page-title">Trainings List</h3>

        <a class="create-btn" href="<?php echo BASE_URL; ?>/admin/trainings_create.php">
            + Add Training
        </a>
    </div>

    <div class="table-card">
        <table id="trainingTable" class="table table-striped nowrap w-100">
            <thead>
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Image</th>
                    <th>Posted By</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($trainings as $t): ?>
                    <?php
                        $imgUrl = !empty($t['image']) ? BASE_URL.'/uploads/trainings/'.$t['image'] : '';
                    ?>
                    <tr>
                        <td></td>

                        <td><?php echo htmlspecialchars($t['title']); ?></td>

                        <td>
                            <span class="badge-date">
                                <?php echo htmlspecialchars($t['training_date']); ?>
                            </span>
                        </td>

                        <td><?php echo htmlspecialchars($t['location'] ?? '-'); ?></td>

                        <td>
                            <?php if($t['image']): ?>
                                <img src="<?php echo $imgUrl; ?>" class="img-thumb" alt="Training">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td><?php echo htmlspecialchars($t['fullname'] ?? 'Admin'); ?></td>

                        <td>
                            <button
                                type="button"
                                class="btn-view view-training-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#trainingViewModal"
                                data-title="<?php echo htmlspecialchars($t['title'] ?? '', ENT_QUOTES); ?>"
                                data-content="<?php echo htmlspecialchars($t['content'] ?? '', ENT_QUOTES); ?>"
                                data-training_date="<?php echo htmlspecialchars($t['training_date'] ?? '', ENT_QUOTES); ?>"
                                data-location="<?php echo htmlspecialchars($t['location'] ?? '', ENT_QUOTES); ?>"
                                data-posted_by="<?php echo htmlspecialchars($t['fullname'] ?? 'Admin', ENT_QUOTES); ?>"
                                data-image="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES); ?>"
                            >
                                View
                            </button>

                            <a href="<?php echo BASE_URL; ?>/admin/trainings_edit.php?id=<?php echo (int)$t['id']; ?>" class="btn-edit">Edit</a>

                            <a href="?delete=<?php echo (int)$t['id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Delete this training?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Training View Modal -->
<div class="modal fade" id="trainingViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header">
                <h5 class="modal-title">Training Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <img src="" alt="Training Image" id="modalTrainingImage" class="training-modal-image" style="display:none;">

                <div class="training-meta">
                    <div class="training-box">
                        <div class="training-label">Title</div>
                        <div class="training-value" id="modalTrainingTitle">—</div>
                    </div>

                    <div class="training-box">
                        <div class="training-label">Date</div>
                        <div class="training-value" id="modalTrainingDate">—</div>
                    </div>

                    <div class="training-box">
                        <div class="training-label">Location</div>
                        <div class="training-value" id="modalTrainingLocation">—</div>
                    </div>

                    <div class="training-box">
                        <div class="training-label">Posted By</div>
                        <div class="training-value" id="modalTrainingPostedBy">—</div>
                    </div>
                </div>

                <div class="training-content-box">
                    <div class="training-label">Description</div>
                    <div class="training-content-text" id="modalTrainingContent">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    const table = $('#trainingTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable:false, searchable:false, targets:[0,6] }
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ trainings",
            infoEmpty: "Showing 0 to 0 of 0 trainings",
            zeroRecords: "No matching trainings found",
            paginate: {
                previous: "Prev",
                next: "Next"
            }
        }
    });

    table.on('order.dt search.dt draw.dt', function () {
        let i = 1;
        table.cells(null, 0, {search:'applied', order:'applied', page:'current'}).every(function () {
            this.data(i++);
        });
    }).draw();

    $(document).on('click', '.view-training-btn', function () {
        const title = $(this).data('title') || '—';
        const content = $(this).data('content') || '—';
        const training_date = $(this).data('training_date') || '—';
        const location = $(this).data('location') || '—';
        const posted_by = $(this).data('posted_by') || '—';
        const image = $(this).data('image') || '';

        $('#modalTrainingTitle').text(title);
        $('#modalTrainingDate').text(training_date);
        $('#modalTrainingLocation').text(location);
        $('#modalTrainingPostedBy').text(posted_by);
        $('#modalTrainingContent').text(content);

        if (image !== '') {
            $('#modalTrainingImage').attr('src', image).show();
        } else {
            $('#modalTrainingImage').hide().attr('src', '');
        }
    });
});
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>