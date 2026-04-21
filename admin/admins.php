<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

// Super admin only
if ($_SESSION['admin_role'] != 1) {
    header('Location: dashboard.php');
    exit;
}

$errors  = [];
$success = '';

// Handle add new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId   = (int)($_POST['role_id'] ?? 2);

    if (!$username) $errors[] = 'Username required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!in_array($roleId, [1, 2])) $errors[] = 'Invalid role.';

    if (!$errors) {
        // Check uniqueness
        $ck = $mysqli->prepare("SELECT id FROM admins WHERE username=? OR email=?");
        $ck->bind_param('ss', $username, $email);
        $ck->execute();
        if ($ck->get_result()->fetch_assoc()) {
            $errors[] = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins  = $mysqli->prepare(
                "INSERT INTO admins (username, email, password_hash, role_id) VALUES (?,?,?,?)"
            );
            $ins->bind_param('sssi', $username, $email, $hash, $roleId);
            $ins->execute();
            $success = "Admin \"$username\" created successfully.";
        }
    }
}

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'toggle') {
    $toggleId = (int)($_POST['toggle_id'] ?? 0);
    if ($toggleId && $toggleId !== (int)$_SESSION['admin_id']) {
        $toggle = $mysqli->prepare("UPDATE admins SET is_active = 1 - is_active WHERE id = ?");
        $toggle->bind_param('i', $toggleId);
        $toggle->execute();
        $success = "Admin status updated.";
    }
}

// List all admins
$admins = $mysqli->query("
    SELECT a.id, a.username, a.email, a.role_id, a.is_active, a.created_at, a.last_login,
           ar.name AS role_name
    FROM admins a
    JOIN admin_roles ar ON a.role_id = ar.id
    ORDER BY a.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-shield-lock me-2"></i>Admin Accounts</h1>
  </div>
  <div class="admin-content">

    <?php if ($success): ?>
      <div class="alert alert-success py-2 small mb-3"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-danger py-2 small mb-3">
        <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Add admin form -->
      <div class="col-md-4">
        <div class="admin-card">
          <h6 class="text-warning mb-3"><i class="bi bi-person-plus me-1"></i>Add New Admin</h6>
          <form method="post">
            <input type="hidden" name="_action" value="add">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password (min 8 chars)</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
              <label class="form-label">Role</label>
              <select name="role_id" class="form-select">
                <option value="2">Staff</option>
                <option value="1">Super Admin</option>
              </select>
            </div>
            <button type="submit" class="btn btn-gold w-100">Create Admin</button>
          </form>
        </div>
      </div>

      <!-- Admin list -->
      <div class="col-md-8">
        <div class="admin-card">
          <h6 class="text-warning mb-3">All Admins (<?= count($admins) ?>)</h6>
          <div class="table-responsive">
            <table class="table admin-table mb-0">
              <thead>
                <tr><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Action</th></tr>
              </thead>
              <tbody>
              <?php foreach ($admins as $a): ?>
                <tr>
                  <td>
                    <?= htmlspecialchars($a['username']) ?>
                    <?php if ($a['id'] == $_SESSION['admin_id']): ?>
                      <span class="badge badge-confirmed badge-status ms-1">You</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($a['email']) ?></td>
                  <td>
                    <span class="badge badge-<?= $a['role_id']==1?'completed':'pending' ?> badge-status">
                      <?= htmlspecialchars($a['role_name']) ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($a['is_active']): ?>
                      <span class="badge badge-confirmed badge-status">Active</span>
                    <?php else: ?>
                      <span class="badge badge-cancelled badge-status">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted small">
                    <?= $a['last_login'] ? date('d M Y H:i', strtotime($a['last_login'])) : 'Never' ?>
                  </td>
                  <td>
                    <?php if ($a['id'] != $_SESSION['admin_id']): ?>
                      <form method="post" style="display:inline"
                            onsubmit="return confirm('Toggle active status for <?= htmlspecialchars(addslashes($a['username'])) ?>?')">
                        <input type="hidden" name="_action" value="toggle">
                        <input type="hidden" name="toggle_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn btn-sm <?= $a['is_active']?'btn-warning':'btn-success' ?>">
                          <?= $a['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
