<?php include 'header.php'; require 'db.php';
$res=$mysqli->query("SELECT * FROM menu_items ORDER BY FIELD(category,
'Appetizers',
'Butter-Aged Imported Steak Meals',
'Frozen Dry-Aged Imported Steaks',
'Soup & Salad',
'Sides',
'Drinks'), id");
$currentCat='';
// added descriptions
$descriptions = [
  'Jumbo Shrimp Cocktail'=>'Succulent jumbo shrimps with zesty cocktail sauce.',
  'Sturia Oscieta Caviar'=>'Premium oscietra pearls, chilled and refined.',
  'Beef Tartare'=>'Hand-chopped prime beef, capers, delicate seasoning.',
  'La Flamme’s Crab Cake'=>'Golden crab cake, light herbs, crisp exterior.',
  'Lobster Cocktail'=>'Chilled lobster medallions with citrus accents.',
  'Seafood Platter'=>'Curated chilled shellfish selection for sharing.',
  'Tuna Tartare'=>'Silky tuna cubes, citrus, subtle spice.',
  'Fresh Oysters'=>'Seasonal oysters on crushed ice, mignonette.',
  'Signature Ribeye Steak'=>'Butter-aged ribeye, rich marbling, deep flavor.',
  'T-Bone Steak'=>'Classic T-bone: tender filet and robust strip.',
  'NY Strip Steak'=>'Firm, juicy strip with concentrated beef notes.',
  'Flat Iron Steak'=>'Lean, tender cut with bold savor.',
  'Wagyu Cubes'=>'Melt-in-mouth wagyu seared to perfection.',
  'Ribeye'=>'Dry-aged ribeye slices ready for custom prep.',
  'T-Bone'=>'Aged t-bone steak cut for grilling excellence.',
  'NY Strip'=>'Balanced dry-aged strip with refined texture.',
  'Flat Iron'=>'Flavor-forward dry-aged flat iron portion.',
  'Wagyu Cubes'=>'Premium wagyu cubes—intense marbling.',
  'Soup of the Day'=>'Chef’s seasonal crafted soup selection.',
  'French Onion Soup'=>'Slow-caramelized onions, rich broth, gratiné.',
  'Burrata Salad'=>'Creamy burrata, heirloom tomatoes, basil oil.',
  'Caesar Salad'=>'Crisp romaine, parmesan, classic dressing.',
  'La Flamme’s Salad'=>'House greens, signature vinaigrette balance.',
  'Classic Wedge Salad'=>'Iceberg wedge, blue cheese, smoked bacon.',
  'Steak Rice'=>'Savory beef-infused aromatic rice.',
  'Plain Rice'=>'Steamed white rice, fluffy and neutral.',
  'Mushroom Soup'=>'Earthy mushroom purée, velvety finish.',
  'Coleslaw'=>'Crisp cabbage slaw, light tangy dressing.',
  'Corn and Carrots'=>'Butter-glazed sweet vegetables medley.',
  'Mashed Potato'=>'Silky mashed potatoes, cream enriched.',
  'French Fries'=>'Crisp golden fries, sea salt finish.',
  'Mushroom Sauce'=>'Umami-rich mushroom reduction sauce.',
  'Pepper Sauce'=>'Bold cracked pepper cream sauce.',
  'Soda in Cans'=>'Chilled assorted premium soda selection.',
  'Bottled Water'=>'Pure bottled still hydration.',
  'Iced Tea'=>'Brewed iced tea, balanced citrus sweetness.'
];
?>
<div class="container py-5">
  <h2 class="about-title mb-4">Menu</h2>
  <!-- Filter Bar -->
  <div class="filter-bar mb-4">
    <label class="me-2 text-light small fw-semibold">Filter Category:</label>
    <select id="categoryFilter" class="form-select form-select-sm w-auto d-inline-block">
      <option value="">All</option>
      <option>Appetizers</option>
      <option>Butter-Aged Imported Steak Meals</option>
      <option>Frozen Dry-Aged Imported Steaks</option>
      <option>Soup & Salad</option>
      <option>Sides</option>
      <option>Drinks</option>
    </select>
  </div>
  <div class="row g-4" id="menuGrid">
    <?php while($m=$res->fetch_assoc()):
      if($currentCat!==$m['category']){
        $currentCat=$m['category'];
        echo '<div class="col-12 cat-heading" data-cat-heading="'.htmlspecialchars($currentCat).'"><h4 class="category-heading">'.$currentCat.'</h4></div>';
      } ?>
      <div class="col-sm-6 col-md-4 menu-col" data-category="<?=htmlspecialchars($m['category'])?>">
        <div class="card menu-item" data-category="<?=htmlspecialchars($m['category'])?>">
          <img src="images/<?=htmlspecialchars($m['image'])?>" alt="<?=htmlspecialchars($m['name'])?>">
          <div class="card-body">
            <h6 class="card-title text-warning"><?=htmlspecialchars($m['name'])?></h6>
            <p class="mb-1 price">₱<?=number_format($m['price'],2)?></p>
            <p class="menu-desc mb-2"><?=htmlspecialchars($descriptions[$m['name']] ?? 'Exquisite selection prepared to order.')?></p>
            <span class="badge bg-warning text-dark"><?=htmlspecialchars($m['category'])?></span>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
  <div class="text-center mt-5">
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="reservation.php" class="btn btn-gold btn-lg">Make a Reservation</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-gold btn-lg">Login to Reserve</a>
    <?php endif; ?>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const sel=document.getElementById('categoryFilter');
  const cards=[...document.querySelectorAll('.menu-col[data-category]')];
  const headings=[...document.querySelectorAll('.cat-heading')];
  function apply(){
    const val=sel.value;
    cards.forEach(c=>{
      const show=!val||c.dataset.category===val;
      c.style.display=show?'':'none';
    });
    // Hide headings with no visible cards
    headings.forEach(h=>{
      const cat=h.getAttribute('data-cat-heading');
      const any=cards.some(c=>c.dataset.category===cat && c.style.display!=='none');
      h.style.display=any?'':'none';
    });
  }
  sel.addEventListener('change',apply);
  apply();
});
</script>
<?php include 'footer.php'; ?>
