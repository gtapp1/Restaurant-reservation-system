<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: reservations.php');
    exit;
}

// Fetch reservation + user info
$stmt = $mysqli->prepare("
    SELECT r.*,
           u.first_name AS u_first, u.last_name AS u_last, u.email AS u_email
    FROM reservations r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    header('Location: reservations.php');
    exit;
}

// Fetch ordered items grouped by guest
$items = $mysqli->prepare("
    SELECT ri.guest_name, mi.name AS dish, mi.category,
           ri.quantity, ri.price, (ri.price * ri.quantity) AS subtotal
    FROM reservation_items ri
    JOIN menu_items mi ON ri.menu_item_id = mi.id
    WHERE ri.reservation_id = ?
    ORDER BY ri.guest_name, mi.name
");
$items->bind_param('i', $id);
$items->execute();
$itemRows = $items->get_result()->fetch_all(MYSQLI_ASSOC);

$grandTotal = array_sum(array_column($itemRows, 'subtotal'));

// Group by guest name
$byGuest = [];
foreach ($itemRows as $it) {
    $byGuest[$it['guest_name']][] = $it;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title">
      <i class="bi bi-calendar-event me-2"></i>Reservation #<?= $id ?>
      <span class="badge badge-<?= $res['status'] ?> badge-status ms-2 fs-6">
        <?= ucfirst($res['status']) ?>
      </span>
    </h1>
    <a href="reservations.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
  </div>
  <div class="admin-content">

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['admin_msg'])): ?>
      <div class="alert alert-success py-2 small mb-3">
        <?= htmlspecialchars($_SESSION['admin_msg']) ?>
        <?php unset($_SESSION['admin_msg']); ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Left: Reservation info -->
      <div class="col-lg-5">
        <div class="admin-card mb-4">
          <h6 class="text-warning mb-3"><i class="bi bi-info-circle me-1"></i>Reservation Details</h6>
          <table class="table table-dark table-borderless table-sm small mb-0">
            <tbody>
              <tr><th class="text-muted" style="width:38%">ID</th><td>#<?= $res['id'] ?></td></tr>
              <tr><th class="text-muted">Guest Name</th><td><?= htmlspecialchars($res['full_name']) ?></td></tr>
              <tr><th class="text-muted">Email</th><td><?= htmlspecialchars($res['email']) ?></td></tr>
              <tr><th class="text-muted">Phone</th><td><?= htmlspecialchars($res['phone']) ?></td></tr>
              <tr><th class="text-muted">Date</th><td><?= date('D, d M Y', strtotime($res['res_date'])) ?></td></tr>
              <tr><th class="text-muted">Time</th><td><?= substr($res['res_time'], 0, 5) ?></td></tr>
              <tr><th class="text-muted">Table</th><td><?= htmlspecialchars($res['table_pref']) ?></td></tr>
              <tr><th class="text-muted">No. of Guests</th><td><?= $res['guest_count'] ?></td></tr>
              <tr><th class="text-muted">Status</th>
                  <td><span class="badge badge-<?= $res['status'] ?> badge-status"><?= ucfirst($res['status']) ?></span></td>
              </tr>
              <tr><th class="text-muted">Booked At</th><td><?= date('d M Y H:i', strtotime($res['created_at'])) ?></td></tr>
              <?php if (!empty($res['admin_notes'])): ?>
              <tr><th class="text-muted">Admin Notes</th><td><?= htmlspecialchars($res['admin_notes']) ?></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($res['u_first']): ?>
        <div class="admin-card mb-4">
          <h6 class="text-warning mb-3"><i class="bi bi-person me-1"></i>Registered User</h6>
          <p class="mb-1 small"><?= htmlspecialchars($res['u_first'] . ' ' . $res['u_last']) ?></p>
          <p class="mb-0 small text-muted"><?= htmlspecialchars($res['u_email']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="admin-card">
          <h6 class="text-warning mb-3"><i class="bi bi-lightning me-1"></i>Actions</h6>
          <div class="d-flex flex-wrap gap-2">
            <?php if ($res['status'] === 'pending'): ?>
              <form method="post" action="reservation_action.php">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="action" value="confirm">
                <button class="btn btn-success btn-sm px-3">
                  <i class="bi bi-check-lg me-1"></i>Confirm
                </button>
              </form>
            <?php endif; ?>
            <?php if (in_array($res['status'], ['pending','confirmed'])): ?>
              <form method="post" action="reservation_action.php"
                    onsubmit="return confirm('Cancel reservation #<?= $id ?>?')">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="action" value="cancel">
                <button class="btn btn-warning btn-sm px-3">
                  <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
              </form>
            <?php endif; ?>
            <?php if ($_SESSION['admin_role'] == 1): ?>
              <form method="post" action="reservation_action.php"
                    onsubmit="return confirm('Permanently delete reservation #<?= $id ?>? This cannot be undone.')">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-danger btn-sm px-3">
                  <i class="bi bi-trash me-1"></i>Delete
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right: Ordered items -->
      <div class="col-lg-7">
        <div class="admin-card">
          <h6 class="text-warning mb-3"><i class="bi bi-receipt me-1"></i>Order Details</h6>
          <?php if (empty($itemRows)): ?>
            <p class="text-muted small">No items ordered for this reservation.</p>
          <?php else: ?>

          <?php foreach ($byGuest as $guestName => $guestItems):
            $guestTotal = array_sum(array_column($guestItems, 'subtotal'));
          ?>
            <div class="mb-4">
              <h6 class="small text-secondary mb-2 fw-bold">
                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($guestName) ?>
              </h6>
              <table class="table admin-table mb-1">
                <thead>
                  <tr><th>Dish</th><th>Category</th><th>Qty</th><th>Price</th><th>Sub</th></tr>
                </thead>
                <tbody>
                <?php foreach ($guestItems as $it): ?>
                  <tr>
                    <td><?= htmlspecialchars($it['dish']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($it['category']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td>₱<?= number_format($it['price'], 2) ?></td>
                    <td>₱<?= number_format($it['subtotal'], 2) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
              <div class="text-end small text-muted">
                Guest total: <strong class="text-warning">₱<?= number_format($guestTotal, 2) ?></strong>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="border-top border-secondary pt-3 text-end">
            <span class="text-muted me-2">Reservation Total:</span>
            <strong class="text-warning fs-5">₱<?= number_format($grandTotal, 2) ?></strong>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include 'includes/footer.php'; ?>
