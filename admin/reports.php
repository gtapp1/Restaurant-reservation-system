<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

$from = $_GET['from'] ?? date('Y-m-01'); // First of current month
$to   = $_GET['to']   ?? date('Y-m-d');

// ---- Daily Revenue (confirmed/completed only) ----
$revStmt = $mysqli->prepare("
    SELECT r.res_date,
           COUNT(DISTINCT r.id) AS res_count,
           COALESCE(SUM(ri.price * ri.quantity), 0) AS daily_rev
    FROM reservations r
    LEFT JOIN reservation_items ri ON ri.reservation_id = r.id
    WHERE r.res_date BETWEEN ? AND ?
      AND r.status IN ('confirmed', 'completed')
    GROUP BY r.res_date
    ORDER BY r.res_date ASC
");
$revStmt->bind_param('ss', $from, $to);
$revStmt->execute();
$revenueData = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$grandTotal  = array_sum(array_column($revenueData, 'daily_rev'));

// ---- Reservation counts by status (for range) ----
$statStmt = $mysqli->prepare("
    SELECT status, COUNT(*) AS cnt
    FROM reservations
    WHERE res_date BETWEEN ? AND ?
    GROUP BY status
");
$statStmt->bind_param('ss', $from, $to);
$statStmt->execute();
$statusCounts = [];
foreach ($statStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $statusCounts[$row['status']] = $row['cnt'];
}

// ---- Top ordered menu items (all time confirmed) ----
$topItems = $mysqli->query("
    SELECT mi.name, mi.category,
           SUM(ri.quantity) AS total_qty,
           SUM(ri.price * ri.quantity) AS total_rev
    FROM reservation_items ri
    JOIN menu_items mi ON ri.menu_item_id = mi.id
    JOIN reservations r ON ri.reservation_id = r.id
    WHERE r.status IN ('confirmed','completed')
    GROUP BY mi.id
    ORDER BY total_qty DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// ---- Table preference breakdown ----
$tableStats = $mysqli->query("
    SELECT table_pref, COUNT(*) AS cnt
    FROM reservations
    GROUP BY table_pref
    ORDER BY cnt DESC
")->fetch_all(MYSQLI_ASSOC);
$totalTableRes = array_sum(array_column($tableStats, 'cnt'));

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-bar-chart-line me-2"></i>Reports</h1>
    <span class="admin-user"><?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?></span>
  </div>
  <div class="admin-content">

    <!-- Date range filter -->
    <div class="admin-card mb-4">
      <form method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">From</label>
          <input type="date" name="from" class="form-control" value="<?= $from ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">To</label>
          <input type="date" name="to" class="form-control" value="<?= $to ?>">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-gold">Apply Range</button>
          <a href="reports.php" class="btn btn-outline-secondary ms-1">This Month</a>
        </div>
      </form>
    </div>

    <!-- Period summary strip -->
    <div class="row g-3 mb-4">
      <?php
      $pCards = [
        ['Revenue (Confirmed)', '₱'.number_format($grandTotal,2), 'cash-coin'],
        ['Reservations in Range', array_sum($statusCounts), 'calendar2-check'],
        ['Pending', $statusCounts['pending'] ?? 0, 'hourglass-split'],
        ['Confirmed', $statusCounts['confirmed'] ?? 0, 'check-circle'],
        ['Cancelled', $statusCounts['cancelled'] ?? 0, 'x-circle'],
      ];
      foreach ($pCards as [$label, $value, $icon]): ?>
      <div class="col-6 col-lg">
        <div class="stat-card d-flex justify-content-between align-items-start">
          <div>
            <div class="stat-number" style="font-size:1.4rem"><?= $value ?></div>
            <div class="stat-label"><?= $label ?></div>
          </div>
          <div class="stat-icon"><i class="bi bi-<?= $icon ?>"></i></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="row g-4">
      <!-- Daily Revenue Table -->
      <div class="col-lg-6">
        <div class="admin-card h-100">
          <h6 class="text-warning mb-3">
            <i class="bi bi-calendar-week me-1"></i>
            Daily Revenue (<?= $from ?> → <?= $to ?>)
          </h6>
          <div class="table-responsive" style="max-height:380px;overflow-y:auto;">
            <table class="table admin-table mb-0">
              <thead><tr><th>Date</th><th>Reservations</th><th>Revenue</th></tr></thead>
              <tbody>
              <?php if (!$revenueData): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">
                  No confirmed revenue in this range.
                </td></tr>
              <?php endif; ?>
              <?php foreach ($revenueData as $d): ?>
                <tr>
                  <td><?= date('D, d M Y', strtotime($d['res_date'])) ?></td>
                  <td><?= $d['res_count'] ?></td>
                  <td class="text-warning">₱<?= number_format($d['daily_rev'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" class="text-end text-warning fw-bold">Period Total</td>
                  <td class="text-warning fw-bold">₱<?= number_format($grandTotal, 2) ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <!-- Right column -->
      <div class="col-lg-6">
        <!-- Top dishes -->
        <div class="admin-card mb-4">
          <h6 class="text-warning mb-3">
            <i class="bi bi-trophy me-1"></i>Top 10 Ordered Dishes (All Time)
          </h6>
          <div class="table-responsive" style="max-height:220px;overflow-y:auto;">
            <table class="table admin-table mb-0">
              <thead><tr><th>Dish</th><th>Category</th><th>Total Qty</th><th>Revenue</th></tr></thead>
              <tbody>
              <?php if (!$topItems): ?>
                <tr><td colspan="4" class="text-muted text-center py-3">No data yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($topItems as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['name']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($t['category']) ?></td>
                  <td><?= number_format($t['total_qty']) ?></td>
                  <td>₱<?= number_format($t['total_rev'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Table preference breakdown -->
        <div class="admin-card">
          <h6 class="text-warning mb-3">
            <i class="bi bi-layout-split me-1"></i>Table Preference Breakdown
          </h6>
          <?php foreach ($tableStats as $t): ?>
            <?php $pct = $totalTableRes > 0 ? round(($t['cnt'] / $totalTableRes) * 100) : 0; ?>
            <div class="mb-2">
              <div class="d-flex justify-content-between small mb-1">
                <span><?= htmlspecialchars($t['table_pref']) ?></span>
                <span class="text-warning"><?= $t['cnt'] ?> (<?= $pct ?>%)</span>
              </div>
              <div style="background:#1a1a1a;border-radius:20px;height:6px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;background:#d4af37;height:100%;border-radius:20px;transition:width .5s;"></div>
              </div>
            </div>
          <?php endforeach; ?>
          <?php if (!$tableStats): ?>
            <p class="text-muted small">No reservation data.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
