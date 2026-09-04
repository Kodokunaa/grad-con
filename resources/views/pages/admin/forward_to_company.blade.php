
<div class="content">
<h3>Forward Resume to Company</h3>

<?php
if (session('status')) {
    ?><div class="alert alert-success"><?php
    echo e(session('status'));
    ?></div><?php
}
if ($errors->any()) {
    ?><div class="alert alert-danger"><?php
    echo e($errors->first());
    ?></div><?php
}
?>

<form method="POST" action="{{ route('admin.applications.resume.send', $application) }}">
@csrf
  <div class="mb-3">
    <label>Company Email</label>
    <input class="form-control" type="email" name="company_email" required>
  </div>
  <button class="btn btn-dark">Send Resume</button>
</form>
</div>
