<footer class="mt-auto py-4 bg-black text-center border-warning">
  <div class="container">
    <?php if (session_status()===PHP_SESSION_NONE) session_start(); ?>
    <!-- Quick Links -->
    <ul class="list-inline mb-3 small">
      <li class="list-inline-item"><a href="menu.php" class="text-warning text-decoration-none">Menu</a></li>
      <li class="list-inline-item"><a href="about.php" class="text-warning text-decoration-none">About</a></li>
      <?php if(isset($_SESSION['user_id'])): ?>
        <li class="list-inline-item"><a href="reservation.php" class="text-warning text-decoration-none">Reserve</a></li>
        <li class="list-inline-item"><a href="history.php" class="text-warning text-decoration-none">History</a></li>
      <?php else: ?>
        <li class="list-inline-item"><a href="login.php" class="text-warning text-decoration-none">Login</a></li>
        <li class="list-inline-item"><a href="signup.php" class="text-warning text-decoration-none">Sign Up</a></li>
      <?php endif; ?>
    </ul>
    <p class="mb-0 text-light">© La Flamme Fine Dining</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts.js"></script>
</div>
</body>
</html>
