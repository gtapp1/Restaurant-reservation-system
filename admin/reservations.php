<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

// ---- Filters ----
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

// ---- Query builder ----
$where  = [];
$params = [];
$types  = '';

if ($statusFilter && in_array($statusFilter, ['pending','confirmed','cancelled','completed'])) {
    $where[]  = 'r.status = ?';
    $params[] = $statusFilter;
    $types   .= 's';
}
if ($search !== '') {
    $like     = '%' . $search . '%';
    $where[]  = '(r.full_name LIKE ? OR r.email LIKE ? OR r.phone LIKE ?)';
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Count
$countStmt = $mysqli->prepare("SELECT COUNT(*) FROM reservations r $whereSql");
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_row()[0];
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Main query
$dataParams = array_merge($params, [$perPage, $offset]);
$dataTypes  = $types . 'ii';

$stmt = $mysqli->prepare("
    SELECT r.id, r.full_name, r.email, r.phone, r.res_date, r.res_time,
           r.guest_count, r.table_pref, r.status, r.created_at
    FROM reservations r
    $whereSql
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param($dataTypes, ...$dataParams);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build pagination query string
function pageUrl(int $p, string $status, string $search): string {
    $qs = http_build_query(['page' => $p, 'status' => $status, 'search' => $search]);
    return 'reservations.php?' . $qs;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-calendar-check me-2"></i>Reservations</h1>
    <span class="admin-user"><?= number_format($totalRows) ?> records</span>
  </div>
  <div class="admin-content">

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['admin_msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show py-2 small mb-3">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($_SESSION['admin_msg']) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['admin_msg']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['admin_err'])): ?>
      <div class="alert alert-danger alert-dismissible fade show py-2 small mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($_SESSION['admin_err']) ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['admin_err']); ?>
    <?php endif; ?>

    <!-- Filter bar -->
    <div class="admin-card mb-4">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Name / Email / Phone"
                 value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <?php foreach (['pending','confirmed','cancelled','completed'] as $s): ?>
              <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>>
                <?= ucfirst($s) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-gold">Filter</button>
          <a href="reservations.php" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table admin-table mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>Guest Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Date</th>
              <th>Time</th>
              <th>Guests</th>
              <th>Table</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No reservations found.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="text-muted">#<?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['full_name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['phone']) ?></td>
              <td><?= date('d M Y', strtotime($r['res_date'])) ?></td>
              <td><?= substr($r['res_time'], 0, 5) ?></td>
              <td><?= $r['guest_count'] ?></td>
              <td><?= htmlspecialchars($r['table_pref']) ?></td>
              <td>
                <span class="badge badge-<?= $r['status'] ?> badge-status">
                  <?= ucfirst($r['status']) ?>
                </span>
              </td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <!-- View -->
                  <a href="reservation_view.php?id=<?= $r['id'] ?>"
                     class="btn btn-sm btn-outline-warning" title="View Details">
                    <i class="bi bi-eye"></i>
                  </a>
                  <!-- Confirm (pending only) -->
                  <?php if ($r['status'] === 'pending'): ?>
                    <form method="post" action="reservation_action.php">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="confirm">
                      <button type="submit" class="btn btn-sm btn-success" title="Confirm">
                        <i class="bi bi-check-lg"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                  <!-- Cancel (pending or confirmed) -->
                  <?php if (in_array($r['status'], ['pending','confirmed'])): ?>
                    <form method="post" action="reservation_action.php"
                          onsubmit="return confirm('Cancel reservation #<?= $r['id'] ?>?')">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="cancel">
                      <button type="submit" class="btn btn-sm btn-warning" title="Cancel">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                  <!-- Delete (super admin only) -->
                  <?php if ($_SESSION['admin_role'] == 1): ?>
                    <form method="post" action="reservation_action.php"
                          onsubmit="return confirm('Permanently delete reservation #<?= $r['id'] ?>? This cannot be undone.')">
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="delete">
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav class="mt-3 d-flex justify-content-between align-items-center pt-3 border-top border-secondary">
        <small class="text-muted">
          Page <?= $page ?> of <?= $totalPages ?> &mdash; <?= $totalRows ?> records
        </small>
        <ul class="pagination pagination-sm mb-0">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="<?= pageUrl($page-1, $statusFilter, $search) ?>">‹ Prev</a>
            </li>
          <?php endif; ?>
          <?php
          $start = max(1, $page - 2);
          $end   = min($totalPages, $page + 2);
          for ($p = $start; $p <= $end; $p++):
          ?>
            <li class="page-item <?= $p===$page?'active':'' ?>">
              <a class="page-link" href="<?= pageUrl($p, $statusFilter, $search) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="<?= pageUrl($page+1, $statusFilter, $search) ?>">Next ›</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
      <?php endif; ?>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
