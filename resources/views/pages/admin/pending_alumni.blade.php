
<style>
* {
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    min-height: 100vh;
    overflow-x: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.content {
    margin-left: 290px;
    width: calc(100% - 290px);
    max-width: 100%;
    padding: 30px 24px;
}

.container {
    max-width: 1250px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.card {
    background: #ffffff;
    border: 1px solid #e0e7ff;
    border-left: 4px solid #f97316;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    overflow-x: auto;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.alert {
    padding: 14px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 14px;
    border-left: 4px solid;
    animation: slideDown 0.3s ease;
}

.success {
    background: #dcfce7;
    color: #166534;
    border-left-color: #22c55e;
}

.error {
    background: #fee2e2;
    color: #b91c1c;
    border-left-color: #ef4444;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.custom-table thead tr {
    background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
    border-top: 1px solid #e0e7ff;
    border-bottom: 2px solid #e0e7ff;
}

.custom-table th,
.custom-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e0e7ff;
    text-align: left;
    font-size: 14px;
    vertical-align: middle;
}

.custom-table th {
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.custom-table td {
    color: #1f2937;
    font-size: 15px;
}

.custom-table tbody tr {
    transition: all 0.2s ease;
}

.custom-table tbody tr:hover {
    background: linear-gradient(90deg, #fff7ed 0%, #fef3c7 100%);
}

.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 10px;
    color: white;
    cursor: pointer;
    font-weight: 700;
    margin-right: 6px;
    transition: all 0.3s ease;
    font-size: 13px;
}

.btn-approve {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2);
}

.btn-approve:hover {
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    transform: translateY(-1px);
}

.btn-reject {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.2);
}

.btn-reject:hover {
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    transform: translateY(-1px);
}

.no-data {
    padding: 30px 20px;
    text-align: center;
    color: #64748b;
    font-size: 16px;
}

.back-link {
    text-decoration: none;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #fff;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
}

.back-link:hover {
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3);
    transform: translateY(-2px);
}

.inline-form {
    display: inline;
}

.badge-status {
    display: inline-block;
    background: #f59e0b;
    color: #fff;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    text-transform: capitalize;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

    .card {
        padding: 20px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .back-link {
        width: 100%;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .custom-table th,
    .custom-table td {
        padding: 10px;
        font-size: 13px;
    }

    .btn {
        padding: 6px 10px;
        font-size: 12px;
        margin-right: 4px;
    }
}
</style>

<div class="content">
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Pending Alumni Registrations</h2>
            <a href="<?php 
echo \url('');
?>/admin/dashboard" class="back-link">Back to Dashboard</a>
        </div>

        <div class="card">
            <?php 
if ($success) {
    ?>
                <div class="alert success"><?php 
    echo htmlspecialchars($success);
    ?></div>
            <?php 
}
?>

            <?php 
if ($error) {
    ?>
                <div class="alert error"><?php 
    echo htmlspecialchars($error);
    ?></div>
            <?php 
}
?>

            <?php 
if (!empty($pendingUsers)) {
    ?>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Student ID</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Batch Year</th>
                            <th>Status</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php 
    $counter = 1;
    foreach ($pendingUsers as $user) {
        ?>
                        <tr>
                            <td><?php 
        echo $counter++;
        ?></td>
                            <td><?php 
        echo htmlspecialchars($user['fullname']);
        ?></td>
                            <td><?php 
        echo htmlspecialchars($user['username']);
        ?></td>
                            <td><?php 
        echo htmlspecialchars($user['email']);
        ?></td>
                            <td><?php 
        echo htmlspecialchars($user['course'] ?? '');
        ?></td>
                            <td><?php 
        echo htmlspecialchars($user['batch_year'] ?? '');
        ?></td>
                            <td>
                                <span class="badge-status">
                                    <?php 
        echo htmlspecialchars($user['status']);
        ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ url('/admin/alumni/'.(int) $user['id'].'/approval') }}" class="inline-form">
@csrf
@method('PATCH')
                                    <button type="submit" class="btn btn-approve">Approve</button>
                                </form>

                                <form method="POST" action="{{ url('/admin/alumni/'.(int) $user['id'].'/rejection') }}" class="inline-form">
@csrf
@method('PATCH')
                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Reject this alumni account?')">Reject</button>
                                </form>
                            </td>
                        </tr>

                    <?php 
    }
    ?>

                    </tbody>
                </table>

            <?php 
} else {
    ?>
                <div class="no-data">No pending alumni accounts found.</div>
            <?php 
}
?>

        </div>
    </div>
</div>

<?php 
echo view('partials.footer', \get_defined_vars());
