<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

// Super admin only
if ($_SESSION['admin_role'] != 1) {
    header('Location: dashboard.php');
    exit;
}

$search   = trim($_GET['search'] ?? '');
$catFilter = trim($_GET['cat'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $like    = '%' . $search . '%';
    $where[] = '(name LIKE ? OR category LIKE ?)';
    $params  = array_merge($params, [$like, $like]);
    $types  .= 'ss';
}
if ($catFilter !== '') {
    $where[] = 'category = ?';
    $params[] = $catFilter;
    $types   .= 's';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$dataParams = array_merge($params);
$dataTypes  = $types;

$params[] = 200; // limit
$types   .= 'i';

$stmt = $mysqli->prepare("
    SELECT * FROM menu_items $whereSql ORDER BY category, name LIMIT ?
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// All categories for filter
$cats = $mysqli->query("SELECT DISTINCT category FROM menu_items ORDER BY category")
               ->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-egg-fried me-2"></i>Menu Items</h1>
    <a href="menu_add.php" class="btn btn-gold btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Add New Item
    </a>
  </div>
  <div class="admin-content">

    <?php if (!empty($_SESSION['admin_msg'])): ?>
      <div class="alert alert-success py-2 small mb-3">
        <?= htmlspecialchars($_SESSION['admin_msg']) ?>
        <?php unset($_SESSION['admin_msg']); ?>
      </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="admin-card mb-4">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Item name..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Category</label>
          <select name="cat" class="form-select">
            <option value="">All Categories</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= htmlspecialchars($c['category']) ?>"
                <?= $catFilter===$c['category']?'selected':'' ?>>
                <?= htmlspecialchars($c['category']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-gold">Filter</button>
          <a href="menu.php" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
      </form>
    </div>

    <!-- Menu table -->
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table admin-table mb-0">
          <thead>
            <tr>
              <th>#</th><th>Image</th><th>Name</th><th>Category</th>
              <th>Price</th><th>Available</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$items): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="bi bi-egg-fried fs-3 d-block mb-2"></i>No items found.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($items as $item): ?>
            <tr>
              <td class="text-muted">#<?= $item['id'] ?></td>
              <td>
                <?php if ($item['image']): ?>
                  <img src="../images/<?= htmlspecialchars($item['image']) ?>"
                       alt="<?= htmlspecialchars($item['name']) ?>"
                       style="width:56px;height:40px;object-fit:cover;border-radius:3px;border:1px solid #333;">
                <?php else: ?>
                  <div style="width:56px;height:40px;background:#1a1a1a;border-radius:3px;border:1px solid #222;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-image text-muted"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($item['category']) ?></td>
              <td>₱<?= number_format($item['price'], 2) ?></td>
              <td>
                <?php if ($item['is_available']): ?>
                  <span class="badge badge-confirmed badge-status">Yes</span>
                <?php else: ?>
                  <span class="badge badge-cancelled badge-status">No</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="menu_edit.php?id=<?= $item['id'] ?>"
                   class="btn btn-sm btn-outline-warning" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form method="post" action="menu_delete.php" style="display:inline"
                      onsubmit="return confirm('Delete \"<?= htmlspecialchars(addslashes($item['name'])) ?>\"?')">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
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
