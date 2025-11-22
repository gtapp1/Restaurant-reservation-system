<?php if (session_status()===PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>La Flamme</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="styles.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-warning">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning d-flex align-items-center" href="index.php">
      <img src="images/logo.png" alt="La Flamme" class="nav-logo">
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="reservation.php">Reserve</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="min-vh-100 d-flex flex-column">
