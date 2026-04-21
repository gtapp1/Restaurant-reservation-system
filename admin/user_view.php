<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: users.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit;
}

// User's reservations
$resStmt = $mysqli->prepare("
    SELECT r.id, r.res_date, r.res_time, r.table_pref, r.guest_count, r.status, r.created_at,
           COALESCE(SUM(ri.price * ri.quantity), 0) AS total
    FROM reservations r
    LEFT JOIN reservation_items ri ON ri.reservation_id = r.id
    WHERE r.user_id = ?
    GROUP BY r.id
    ORDER BY r.res_date DESC
");
$resStmt->bind_param('i', $id);
$resStmt->execute();
$reservations = $resStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalSpent = array_sum(array_column($reservations, 'total'));

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title">
      <i class="bi bi-person me-2"></i>
      <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
    </h1>
    <a href="users.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
  <div class="admin-content">
    <div class="row g-4">
      <!-- User Info -->
      <div class="col-md-4">
        <div class="admin-card mb-4">
          <h6 class="text-warning mb-3">User Profile</h6>
          <table class="table table-dark table-borderless table-sm small mb-0">
            <tr><th class="text-muted">ID</th><td>#<?= $user['id'] ?></td></tr>
            <tr><th class="text-muted">First Name</th><td><?= htmlspecialchars($user['first_name']) ?></td></tr>
            <tr><th class="text-muted">Last Name</th><td><?= htmlspecialchars($user['last_name']) ?></td></tr>
            <tr><th class="text-muted">Email</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
            <tr><th class="text-muted">Joined</th><td><?= date('d M Y', strtotime($user['created_at'])) ?></td></tr>
            <tr><th class="text-muted">Reservations</th><td><?= count($reservations) ?></td></tr>
            <tr><th class="text-muted">Total Spent</th>
                <td class="text-warning fw-bold">₱<?= number_format($totalSpent, 2) ?></td></tr>
          </table>
        </div>
        <?php if ($_SESSION['admin_role'] == 1): ?>
        <div class="admin-card">
          <h6 class="text-danger mb-3">Danger Zone</h6>
          <p class="small text-muted mb-3">Deleting this user will remove all their reservations and data permanently.</p>
          <form method="post" action="user_delete.php"
                onsubmit="return confirm('Delete user #<?= $user['id'] ?>? This cannot be undone.')">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <button class="btn btn-danger btn-sm w-100">
              <i class="bi bi-trash me-1"></i>Delete User
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>

      <!-- Reservations -->
      <div class="col-md-8">
        <div class="admin-card">
          <h6 class="text-warning mb-3">Reservation History (<?= count($reservations) ?>)</h6>
          <?php if (!$reservations): ?>
            <p class="text-muted small">This user has no reservations.</p>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table admin-table mb-0">
              <thead>
                <tr><th>#</th><th>Date</th><th>Time</th><th>Table</th><th>Guests</th><th>Total</th><th>Status</th><th></th></tr>
              </thead>
              <tbody>
              <?php foreach ($reservations as $r): ?>
                <tr>
                  <td>#<?= $r['id'] ?></td>
                  <td><?= date('d M Y', strtotime($r['res_date'])) ?></td>
                  <td><?= substr($r['res_time'], 0, 5) ?></td>
                  <td><?= htmlspecialchars($r['table_pref']) ?></td>
                  <td><?= $r['guest_count'] ?></td>
                  <td>₱<?= number_format($r['total'], 2) ?></td>
                  <td><span class="badge badge-<?= $r['status'] ?> badge-status"><?= ucfirst($r['status']) ?></span></td>
                  <td>
                    <a href="reservation_view.php?id=<?= $r['id'] ?>"
                       class="btn btn-sm btn-outline-warning">
                      <i class="bi bi-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
