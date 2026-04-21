<?php include 'header.php'; require 'db.php';

// Fetch available menu items — use DB description column, filter by is_available
$res        = $mysqli->query(
    "SELECT * FROM menu_items
     WHERE is_available = 1
     ORDER BY FIELD(category,
       'Appetizers',
       'Butter-Aged Imported Steak Meals',
       'Frozen Dry-Aged Imported Steaks',
       'Soup & Salad',
       'Sides',
       'Drinks'
     ), name"
);
$currentCat = '';
?>
<div class="container py-5">
  <h2 class="about-title mb-4">Menu</h2>

  <!-- Category Filter Bar -->
  <div class="filter-bar mb-4">
    <label class="me-2 text-light small fw-semibold">Filter Category:</label>
    <select id="categoryFilter" class="form-select form-select-sm w-auto d-inline-block">
      <option value="">All</option>
      <option>Appetizers</option>
      <option>Butter-Aged Imported Steak Meals</option>
      <option>Frozen Dry-Aged Imported Steaks</option>
      <option>Soup &amp; Salad</option>
      <option>Sides</option>
      <option>Drinks</option>
    </select>
  </div>

  <div class="row g-4" id="menuGrid">
    <?php while ($m = $res->fetch_assoc()):
      if ($currentCat !== $m['category']) {
          $currentCat = $m['category'];
          echo '<div class="col-12 cat-heading" data-cat-heading="' . htmlspecialchars($currentCat) . '">'
             . '<h4 class="category-heading">' . htmlspecialchars($currentCat) . '</h4></div>';
      }

      // FIX C4: Use description from DB column instead of hardcoded PHP array
      // Falls back to generic text if no description set yet
      $desc = !empty($m['description'])
          ? $m['description']
          : 'Exquisite selection prepared to order.';
    ?>
      <div class="col-sm-6 col-md-4 menu-col" data-category="<?= htmlspecialchars($m['category']) ?>">
        <div class="card menu-item" data-category="<?= htmlspecialchars($m['category']) ?>">
          <img src="images/<?= htmlspecialchars($m['image']) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
          <div class="card-body">
            <h6 class="card-title text-warning"><?= htmlspecialchars($m['name']) ?></h6>
            <p class="mb-1 price">₱<?= number_format($m['price'], 2) ?></p>
            <p class="menu-desc mb-2"><?= htmlspecialchars($desc) ?></p>
            <span class="badge bg-warning text-dark"><?= htmlspecialchars($m['category']) ?></span>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <div class="text-center mt-5">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="reservation.php" class="btn btn-gold btn-lg">Make a Reservation</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-gold btn-lg">Login to Reserve</a>
    <?php endif; ?>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const sel      = document.getElementById('categoryFilter');
  const cards    = [...document.querySelectorAll('.menu-col[data-category]')];
  const headings = [...document.querySelectorAll('.cat-heading')];

  function applyFilter() {
    const val = sel.value;
    cards.forEach(c => {
      c.style.display = (!val || c.dataset.category === val) ? '' : 'none';
    });
    // Hide category headings that have no visible cards
    headings.forEach(h => {
      const cat = h.getAttribute('data-cat-heading');
      const any = cards.some(c => c.dataset.category === cat && c.style.display !== 'none');
      h.style.display = any ? '' : 'none';
    });
  }

  sel.addEventListener('change', applyFilter);
  applyFilter();
});
</script>
<?php include 'footer.php'; ?>
