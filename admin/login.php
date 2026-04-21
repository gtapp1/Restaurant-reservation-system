<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $mysqli->prepare(
            "SELECT id, username, password_hash, role_id FROM admins WHERE username = ? AND is_active = 1"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role']     = $row['role_id'];

            // Record last login
            $upd = $mysqli->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $upd->bind_param('i', $row['id']);
            $upd->execute();

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login — La Flamme</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*, body { font-family: 'Poppins', sans-serif; }
body { background: #0a0a0a; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.login-outer { width: 100%; max-width: 420px; padding: 1rem; }
.login-card {
  background: #111; border: 1px solid #222; border-radius: 12px;
  padding: 2.75rem 2.25rem;
  box-shadow: 0 0 40px rgba(0,0,0,.7), 0 0 0 1px rgba(212,175,55,.06);
}
.login-brand { text-align: center; margin-bottom: 2.1rem; }
.login-brand .fire-icon { font-size: 2.5rem; color: #d4af37; display: block; margin-bottom: .35rem; }
.login-brand h1 { color: #d4af37; font-size: 1.5rem; font-weight: 700; letter-spacing: .1em; margin: 0; }
.login-brand p  { color: #444; font-size: .76rem; margin: .5rem 0 0; letter-spacing: .04em; text-transform: uppercase; }
.form-control {
  background: #181818; border: 1px solid #2a2a2a; color: #e0e0e0;
  padding: .7rem 1rem; font-size: .88rem; border-radius: 6px;
}
.form-control:focus {
  background: #181818; color: #e0e0e0;
  border-color: #d4af37; box-shadow: 0 0 0 .2rem rgba(212,175,55,.2);
}
.form-control::placeholder { color: #3a3a3a; }
.form-label { font-size: .76rem; color: #d4af37; font-weight: 600; letter-spacing: .04em; }
.btn-login {
  background: #d4af37; color: #000; font-weight: 700; width: 100%;
  padding: .75rem; font-size: .92rem; letter-spacing: .06em; border: none;
  border-radius: 6px; transition: background .2s;
}
.btn-login:hover { background: #c19d2f; }
.input-icon-wrap { position: relative; }
.input-icon-wrap i {
  position: absolute; left: .85rem; top: 50%; transform: translateY(-50%);
  color: #444; pointer-events: none;
}
.input-icon-wrap .form-control { padding-left: 2.4rem; }
.alert-danger {
  background: rgba(220,53,69,.1); border: 1px solid rgba(220,53,69,.3);
  color: #e05b68; font-size: .82rem; padding: .6rem 1rem; border-radius: 6px;
}
.back-link { text-align: center; margin-top: 1.25rem; }
.back-link a { color: #555; font-size: .75rem; text-decoration: none; }
.back-link a:hover { color: #d4af37; }
</style>
</head>
<body>
<div class="login-outer">
  <div class="login-card">
    <div class="login-brand">
      <i class="bi bi-fire fire-icon"></i>
      <h1>La Flamme</h1>
      <p>Admin Control Panel</p>
    </div>

    <?php if ($error): ?>
      <div class="alert-danger mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="mb-3">
        <label class="form-label" for="adminUsername">Username</label>
        <div class="input-icon-wrap">
          <i class="bi bi-person"></i>
          <input id="adminUsername" type="text" name="username" class="form-control"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 placeholder="superadmin" required autocomplete="username">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="adminPassword">Password</label>
        <div class="input-icon-wrap">
          <i class="bi bi-lock"></i>
          <input id="adminPassword" type="password" name="password" class="form-control"
                 placeholder="••••••••" required autocomplete="current-password">
        </div>
      </div>
      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Admin Panel
      </button>
    </form>

    <div class="back-link">
      <a href="../index.php"><i class="bi bi-arrow-left me-1"></i>Back to Restaurant Site</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
