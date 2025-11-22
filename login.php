<?php include 'header.php'; ?>
<div class="container py-5">
  <h2 class="text-warning mb-4">Login</h2>
  <?php if(!empty($_SESSION['msg'])){ echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['msg']).'</div>'; $_SESSION['msg']=null; } ?>
  <form method="post" action="process_login.php" class="row g-3">
    <div class="col-12">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="col-12">
      <button class="btn btn-gold">Login</button>
    </div>
  </form>
</div>
<?php include 'footer.php'; ?>
