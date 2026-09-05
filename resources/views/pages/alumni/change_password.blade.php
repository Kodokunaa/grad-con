
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

.page-title{
font-size:28px;
font-weight:700;
color:#1f2937;
margin-bottom:20px;
}

.card-box{
background:#ffffff;
border-radius:18px;
padding:28px;
border:1px solid #e5e7eb;
box-shadow:0 4px 14px rgba(0,0,0,0.05);
max-width:600px;
}

.form-label{
font-weight:600;
font-size:14px;
color:#374151;
margin-bottom:6px;
}

.form-control{
border-radius:10px;
padding:12px;
border:1px solid #d1d5db;
background:#f9fafb;
}

.form-control:focus{
border-color:#f97316;
box-shadow:0 0 0 3px rgba(249,115,22,.2);
background:#ffffff;
}

.btn-orange{
background:#f97316;
color:#fff;
padding:10px 16px;
border-radius:10px;
border:none;
font-weight:600;
transition:.3s;
}

.btn-orange:hover{
background:#16a34a;
color:#fff;
}

.btn-outline-custom{
border:1px solid #d1d5db;
padding:10px 16px;
border-radius:10px;
text-decoration:none;
color:#374151;
font-weight:600;
}

.btn-outline-custom:hover{
background:#f3f4f6;
}

.alert-success-custom{
background:#dcfce7;
border:1px solid #bbf7d0;
color:#166534;
padding:10px;
border-radius:10px;
margin-bottom:15px;
}

.alert-danger-custom{
background:#fee2e2;
border:1px solid #fecaca;
color:#b91c1c;
padding:10px;
border-radius:10px;
margin-bottom:15px;
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

<h3 class="page-title">Change Password</h3>

<?php 
if ($msg) {
    ?>
<div class="alert-success-custom">
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
<div class="alert-danger-custom">
<?php 
    echo htmlspecialchars($error);
    ?>
</div>
<?php 
}
?>

<div class="card-box">

<form method="POST" action="{{ route('profile.password.update') }}">
@csrf
@method('PUT')
<input type="hidden" name="change_password_page" value="1">

<div class="mb-3">
<label class="form-label">Old Password</label>
<input class="form-control" type="password" name="old_password" required>
</div>

<div class="mb-3">
<label class="form-label">New Password</label>
<input class="form-control" type="password" name="new_password" required>
</div>

<div class="mb-3">
<label class="form-label">Confirm New Password</label>
<input class="form-control" type="password" name="confirm_password" required>
</div>

<div class="d-flex gap-2 mt-3">

<button class="btn-orange">
Update Password
</button>

<a class="btn-outline-custom"
href="<?php 
echo \url('');
?>/alumni/feed">
Back
</a>

</div>

</form>

</div>

</div>

<?php 
echo view('partials.footer', \get_defined_vars());
