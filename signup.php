<?php include 'header.php'; ?>
<div class="container py-5">
  <h2 class="text-warning mb-4">Sign Up</h2>
  <form method="post" action="process_signup.php" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">First Name</label>
      <input name="first_name" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Last Name</label>
      <input name="last_name" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" minlength="6" required>
    </div>
    <div class="col-12">
      <button class="btn btn-gold">Create Account</button>
    </div>
  </form>
</div>
<?php include 'footer.php'; ?>
