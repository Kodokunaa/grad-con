
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

.back-btn{
background:#f97316;
color:#fff;
padding:10px 16px;
border-radius:10px;
text-decoration:none;
font-weight:600;
font-size:14px;
transition:.3s;
}

.back-btn:hover{
background:#16a34a;
color:#fff;
}

.table-card{
background:#fff;
border-radius:16px;
padding:20px;
border:1px solid #e5e7eb;
box-shadow:0 4px 14px rgba(0,0,0,0.05);
overflow-x:auto;
}

.custom-table{
width:100%;
border-collapse:collapse;
min-width:700px;
}

.custom-table th{
background:#f9fafb;
font-weight:700;
font-size:14px;
color:#374151;
padding:14px;
border-bottom:1px solid #e5e7eb;
}

.custom-table td{
padding:14px;
border-bottom:1px solid #e5e7eb;
font-size:14px;
color:#111827;
}

.custom-table tbody tr:hover{
background:#fffaf5;
}

.name{
font-weight:600;
}

.email{
color:#6b7280;
}

.empty{
color:#6b7280;
padding:15px 0;
}

@media (max-width:991px){

.content{
margin-left:0;
width:100%;
padding:20px 15px;
}

.page-title{
font-size:24px;
}

}

</style>


<div class="content">

<div class="page-header">
<h3 class="page-title"><?php 
echo htmlspecialchars($title);
?></h3>

<a class="back-btn"
href="<?php 
echo \url('');
?>/admin/graduates_stats.php">
Back
</a>

</div>


<div class="table-card">

<?php 
if (count($list) === 0) {
    ?>

<div class="empty">No records found.</div>

<?php 
} else {
    ?>

<table class="custom-table">

<thead>
<tr>
<th>Full Name</th>
<th>Username</th>
<th>Email</th>
<th>Department</th>
<th>Batch</th>
</tr>
</thead>

<tbody>

<?php 
    foreach ($list as $u) {
        ?>

<tr>

<td class="name">
<?php 
        echo htmlspecialchars($u['fullname']);
        ?>
</td>

<td>
<?php 
        echo htmlspecialchars($u['username']);
        ?>
</td>

<td class="email">
<?php 
        echo htmlspecialchars($u['email'] ?? '');
        ?>
</td>

<td>
<?php 
        echo htmlspecialchars($u['course'] ?? '');
        ?>
</td>

<td>
<?php 
        echo htmlspecialchars($u['batch_year'] ?? '');
        ?>
</td>

</tr>

<?php 
    }
    ?>

</tbody>

</table>

<?php 
}
?>

</div>

</div>

<?php 
echo view('partials.footer', \get_defined_vars());