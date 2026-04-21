<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = '';
$params = [];
$types  = '';

if ($search !== '') {
    $like   = '%' . $search . '%';
    $where  = 'WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
    $params = [$like, $like, $like];
    $types  = 'sss';
}

// Count
$countStmt = $mysqli->prepare("SELECT COUNT(*) FROM users u $where");
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_row()[0];
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Data
$dataParams = array_merge($params, [$perPage, $offset]);
$dataTypes  = $types . 'ii';

$stmt = $mysqli->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.created_at,
           COUNT(r.id) AS res_count,
           SUM(CASE WHEN r.status='pending'   THEN 1 ELSE 0 END) AS pending_count,
           SUM(CASE WHEN r.status='confirmed' THEN 1 ELSE 0 END) AS confirmed_count
    FROM users u
    LEFT JOIN reservations r ON r.user_id = u.id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param($dataTypes, ...$dataParams);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-people me-2"></i>Users</h1>
    <span class="admin-user"><?= number_format($totalRows) ?> registered users</span>
  </div>
  <div class="admin-content">

    <?php if (!empty($_SESSION['admin_msg'])): ?>
      <div class="alert alert-success py-2 small mb-3">
        <?= htmlspecialchars($_SESSION['admin_msg']) ?>
        <?php unset($_SESSION['admin_msg']); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['admin_err'])): ?>
      <div class="alert alert-danger py-2 small mb-3">
        <?= htmlspecialchars($_SESSION['admin_err']) ?>
        <?php unset($_SESSION['admin_err']); ?>
      </div>
    <?php endif; ?>

    <!-- Search bar -->
    <div class="admin-card mb-4">
      <form method="get" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" style="max-width:320px"
               placeholder="Search by name or email"
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-gold">Search</button>
        <a href="users.php" class="btn btn-outline-secondary">Reset</a>
      </form>
    </div>

    <!-- Users table -->
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table admin-table mb-0">
          <thead>
            <tr>
              <th>#</th><th>Full Name</th><th>Email</th>
              <th>Total Res.</th><th>Pending</th><th>Confirmed</th>
              <th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$users): ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-5">
                <i class="bi bi-people fs-3 d-block mb-2"></i>No users found.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td class="text-muted">#<?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= $u['res_count'] ?></td>
              <td>
                <?php if ($u['pending_count'] > 0): ?>
                  <span class="badge badge-pending badge-status"><?= $u['pending_count'] ?></span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['confirmed_count'] > 0): ?>
                  <span class="badge badge-confirmed badge-status"><?= $u['confirmed_count'] ?></span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <a href="user_view.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <?php if ($_SESSION['admin_role'] == 1): ?>
                  <form method="post" action="user_delete.php" style="display:inline"
                        onsubmit="return confirm('Delete user #<?= $u['id'] ?> and ALL their data? This CANNOT be undone.')">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav class="mt-3 d-flex justify-content-between align-items-center pt-3 border-top border-secondary">
        <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
        <ul class="pagination pagination-sm mb-0">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p===$page?'active':'' ?>">
              <a class="page-link"
                 href="users.php?page=<?= $p ?>&search=<?= urlencode($search) ?>">
                <?= $p ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
