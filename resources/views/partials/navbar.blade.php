<?php

null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php 
echo \url('');
?>/index.php">CCC Job Portal</a>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php 
if (isset(\gc_context()->session['user'])) {
    ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php 
    echo \url('');
    ?>/profile.php">My Profile</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-logout-trigger>Logout</a>
          </li>
        <?php 
}
?>
      </ul>
    </div>
  </div>
</nav>
