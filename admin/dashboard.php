<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

// ---- Aggregate stats ----
$totalRes     = $mysqli->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0] ?? 0;
$pendingRes   = $mysqli->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetch_row()[0] ?? 0;
$confirmedRes = $mysqli->query("SELECT COUNT(*) FROM reservations WHERE status='confirmed'")->fetch_row()[0] ?? 0;
$cancelledRes = $mysqli->query("SELECT COUNT(*) FROM reservations WHERE status='cancelled'")->fetch_row()[0] ?? 0;
$todayRes     = $mysqli->query("SELECT COUNT(*) FROM reservations WHERE res_date=CURDATE()")->fetch_row()[0] ?? 0;
$totalUsers   = $mysqli->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;
$totalMenus   = $mysqli->query("SELECT COUNT(*) FROM menu_items WHERE is_available=1")->fetch_row()[0] ?? 0;

$revRow = $mysqli->query("
    SELECT COALESCE(SUM(ri.price * ri.quantity), 0) AS rev
    FROM reservation_items ri
    JOIN reservations r ON ri.reservation_id = r.id
    WHERE r.status IN ('confirmed','completed')
")->fetch_assoc();
$totalRevenue = $revRow['rev'] ?? 0;

// ---- Recent reservations ----
$recent = $mysqli->query("
    SELECT r.id, r.full_name, r.res_date, r.res_time, r.guest_count,
           r.status, r.table_pref, r.created_at
    FROM reservations r
    ORDER BY r.created_at DESC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// ---- Status distribution for mini-summary ----
$statusLabels = ['pending' => $pendingRes, 'confirmed' => $confirmedRes, 'cancelled' => $cancelledRes];

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <span class="admin-user">
      <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
    </span>
  </div>
  <div class="admin-content">

    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
      <?php
      $cards = [
        ['Total Reservations', number_format($totalRes),   'calendar2-check'],
        ['Pending Approval',   number_format($pendingRes),  'hourglass-split'],
        ['Reserved Today',     number_format($todayRes),    'calendar-day'],
        ['Confirmed Revenue',  '₱'.number_format($totalRevenue, 2), 'cash-coin'],
        ['Registered Users',   number_format($totalUsers),  'people-fill'],
        ['Active Menu Items',  number_format($totalMenus),  'egg-fried'],
      ];
      foreach ($cards as [$label, $value, $icon]): ?>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="stat-card d-flex justify-content-between align-items-start">
          <div>
            <div class="stat-number"><?= $value ?></div>
            <div class="stat-label"><?= $label ?></div>
          </div>
          <div class="stat-icon ms-2"><i class="bi bi-<?= $icon ?>"></i></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Status Summary Strip -->
    <div class="admin-card mb-4 d-flex flex-wrap gap-3 align-items-center">
      <span class="small text-muted me-2">Reservation breakdown:</span>
      <?php foreach ($statusLabels as $st => $cnt): ?>
        <span class="badge badge-<?= $st ?> badge-status px-3 py-2">
          <?= ucfirst($st) ?>: <?= $cnt ?>
        </span>
      <?php endforeach; ?>
      <span class="badge badge-completed badge-status px-3 py-2">
        Completed: <?= $mysqli->query("SELECT COUNT(*) FROM reservations WHERE status='completed'")->fetch_row()[0] ?? 0 ?>
      </span>
      <a href="reservations.php" class="btn btn-sm btn-outline-warning ms-auto">Manage All →</a>
    </div>

    <!-- Recent Reservations Table -->
    <div class="admin-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="text-warning mb-0"><i class="bi bi-clock-history me-1"></i>Recent Reservations</h6>
        <a href="reservations.php" class="btn btn-sm btn-outline-warning">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table admin-table mb-0">
          <thead>
            <tr>
              <th>ID</th><th>Guest Name</th><th>Date</th><th>Time</th>
              <th>Guests</th><th>Table</th><th>Status</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($recent)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No reservations yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td class="text-muted">#<?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['full_name']) ?></td>
              <td><?= date('d M Y', strtotime($r['res_date'])) ?></td>
              <td><?= substr($r['res_time'], 0, 5) ?></td>
              <td><?= $r['guest_count'] ?></td>
              <td><?= htmlspecialchars($r['table_pref']) ?></td>
              <td><span class="badge badge-<?= $r['status'] ?> badge-status"><?= ucfirst($r['status']) ?></span></td>
              <td>
                <a href="reservation_view.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-warning">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
