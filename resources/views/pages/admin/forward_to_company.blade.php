
<div class="content">
<h3>Forward Resume to Company</h3>

<?php 
if ($msg) {
    ?><div class="alert alert-success"><?php 
    echo $msg;
    ?></div><?php 
}
if ($error) {
    ?><div class="alert alert-danger"><?php 
    echo $error;
    ?></div><?php 
}
?>

<form method="POST">
@csrf
  <div class="mb-3">
    <label>Company Email</label>
    <input class="form-control" type="email" name="company_email" required>
  </div>
  <button class="btn btn-dark">Send Resume</button>
</form>
</div>