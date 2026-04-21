<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

if ($_SESSION['admin_role'] != 1) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$posted = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted['name']        = trim($_POST['name'] ?? '');
    $posted['category']    = trim($_POST['category'] ?? '');
    $posted['price']       = $_POST['price'] ?? '';
    $posted['image']       = trim($_POST['image'] ?? '');
    $posted['description'] = trim($_POST['description'] ?? '');
    $posted['is_available'] = isset($_POST['is_available']) ? 1 : 0;

    if (!$posted['name'])        $errors[] = 'Item name is required.';
    if (!$posted['category'])    $errors[] = 'Category is required.';
    if (!is_numeric($posted['price']) || (float)$posted['price'] <= 0) {
        $errors[] = 'A valid price greater than 0 is required.';
    }

    if (!$errors) {
        $price = (float)$posted['price'];
        $stmt  = $mysqli->prepare(
            "INSERT INTO menu_items (name, category, price, image, description, is_available)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssdssi',
            $posted['name'], $posted['category'], $price,
            $posted['image'], $posted['description'], $posted['is_available']
        );
        $stmt->execute();
        $_SESSION['admin_msg'] = "Menu item \"{$posted['name']}\" added successfully.";
        header('Location: menu.php');
        exit;
    }
}

// Categories for datalist
$cats = $mysqli->query("SELECT DISTINCT category FROM menu_items ORDER BY category")
               ->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title"><i class="bi bi-plus-circle me-2"></i>Add Menu Item</h1>
    <a href="menu.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
  <div class="admin-content">
    <?php if ($errors): ?>
      <div class="alert alert-danger mb-3 small">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:580px">
      <form method="post">
        <div class="mb-3">
          <label class="form-label" for="itemName">Item Name *</label>
          <input id="itemName" type="text" name="name" class="form-control" required
                 value="<?= htmlspecialchars($posted['name'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemCategory">Category *</label>
          <input id="itemCategory" type="text" name="category" class="form-control"
                 list="catList" required
                 value="<?= htmlspecialchars($posted['category'] ?? '') ?>">
          <datalist id="catList">
            <?php foreach ($cats as $c): ?>
              <option value="<?= htmlspecialchars($c['category']) ?>">
            <?php endforeach; ?>
          </datalist>
          <small class="text-muted">Type an existing category or create a new one.</small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemPrice">Price (₱) *</label>
          <input id="itemPrice" type="number" step="0.01" min="0.01" name="price"
                 class="form-control"
                 value="<?= htmlspecialchars($posted['price'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemImage">Image Filename</label>
          <input id="itemImage" type="text" name="image" class="form-control"
                 placeholder="e.g. ribeye.jpg"
                 value="<?= htmlspecialchars($posted['image'] ?? '') ?>">
          <small class="text-muted">File must exist in the <code>/images/</code> folder.</small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemDesc">Description</label>
          <textarea id="itemDesc" name="description" class="form-control" rows="3"
                    placeholder="Short description for the dish..."><?= htmlspecialchars($posted['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-4">
          <div class="form-check">
            <input type="checkbox" name="is_available" id="isAvail" class="form-check-input"
                   <?= (!isset($posted['is_available']) || $posted['is_available']) ? 'checked' : '' ?>>
            <label class="form-check-label text-light" for="isAvail">
              Mark as available on menu
            </label>
          </div>
        </div>
        <button type="submit" class="btn btn-gold px-4">
          <i class="bi bi-plus-lg me-1"></i>Add Menu Item
        </button>
      </form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
