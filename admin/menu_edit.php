<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

if ($_SESSION['admin_role'] != 1) {
    header('Location: dashboard.php');
    exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    header('Location: menu.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $price       = $_POST['price'] ?? '';
    $image       = trim($_POST['image'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    if (!$name)       $errors[] = 'Item name is required.';
    if (!$category)   $errors[] = 'Category is required.';
    if (!is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'A valid price greater than 0 is required.';
    }

    if (!$errors) {
        $priceFl = (float)$price;
        $stmt    = $mysqli->prepare(
            "UPDATE menu_items
             SET name=?, category=?, price=?, image=?, description=?, is_available=?
             WHERE id=?"
        );
        $stmt->bind_param('ssdssii', $name, $category, $priceFl, $image, $description, $isAvailable, $id);
        $stmt->execute();
        $_SESSION['admin_msg'] = "Menu item \"{$name}\" updated.";
        header('Location: menu.php');
        exit;
    }
    // Re-populate if errors
    $item = [
        'id' => $id, 'name' => $name, 'category' => $category, 'price' => $price,
        'image' => $image, 'description' => $description, 'is_available' => $isAvailable,
    ];
} else {
    $fetch = $mysqli->prepare("SELECT * FROM menu_items WHERE id = ?");
    $fetch->bind_param('i', $id);
    $fetch->execute();
    $item  = $fetch->get_result()->fetch_assoc();
    if (!$item) {
        header('Location: menu.php');
        exit;
    }
}

$cats = $mysqli->query("SELECT DISTINCT category FROM menu_items ORDER BY category")
               ->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>
<div id="adminMain">
  <div class="admin-topbar">
    <h1 class="page-title">
      <i class="bi bi-pencil me-2"></i>Edit — <?= htmlspecialchars($item['name']) ?>
    </h1>
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
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="mb-3">
          <label class="form-label" for="itemName">Item Name *</label>
          <input id="itemName" type="text" name="name" class="form-control" required
                 value="<?= htmlspecialchars($item['name']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemCategory">Category *</label>
          <input id="itemCategory" type="text" name="category" class="form-control"
                 list="catList" required
                 value="<?= htmlspecialchars($item['category']) ?>">
          <datalist id="catList">
            <?php foreach ($cats as $c): ?>
              <option value="<?= htmlspecialchars($c['category']) ?>">
            <?php endforeach; ?>
          </datalist>
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemPrice">Price (₱) *</label>
          <input id="itemPrice" type="number" step="0.01" min="0.01" name="price"
                 class="form-control"
                 value="<?= htmlspecialchars($item['price']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemImage">Image Filename</label>
          <div class="d-flex gap-2 align-items-center mb-2">
            <?php if (!empty($item['image'])): ?>
              <img src="../images/<?= htmlspecialchars($item['image']) ?>"
                   alt="preview"
                   style="height:50px;border-radius:4px;border:1px solid #333;">
            <?php endif; ?>
            <input id="itemImage" type="text" name="image" class="form-control"
                   value="<?= htmlspecialchars($item['image']) ?>">
          </div>
          <small class="text-muted">File must exist in <code>/images/</code>.</small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="itemDesc">Description</label>
          <textarea id="itemDesc" name="description" class="form-control"
                    rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
        </div>
        <div class="mb-4">
          <div class="form-check">
            <input type="checkbox" name="is_available" id="isAvail" class="form-check-input"
                   <?= $item['is_available'] ? 'checked' : '' ?>>
            <label class="form-check-label text-light" for="isAvail">
              Available on menu
            </label>
          </div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-gold px-4">
            <i class="bi bi-check-lg me-1"></i>Save Changes
          </button>
          <a href="menu.php" class="btn btn-outline-secondary px-4">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
