<?php include 'header.php'; ?>
<section class="hero text-center flex-grow-1" id="heroRotator" style="background-image:url('images/hero1.jpg');">
  <!-- Added fade layers holder -->
  <div id="heroFadeHolder"></div>
  <div>
    <!-- <div class="logo-text">La Flamme</div> --><!-- removed text div .logo-text -->
    <img src="images/logo.png" alt="La Flamme" class="brand-logo mb-2">
    <!-- replaced static tagline -->
    <p id="heroTagline" class="text-light mt-3 fs-5 hero-tagline">Fine Dining Reservation Experience</p>
    <div class="d-flex justify-content-center mt-4 gap-3 flex-wrap">
      <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="signup.php" class="btn btn-gold btn-lg hero-btn">Get Started</a>
      <?php else: ?>
        <a href="reservation.php" class="btn btn-gold btn-lg hero-btn">Reserve Now</a>
      <?php endif; ?>
      <a href="menu.php" class="btn btn-outline-light btn-lg border-warning hero-btn">View Menu</a>
    </div>
  </div>
</section>

<style>
.ambient-gallery-carousel { background:#000; overflow:hidden; /* removed border-top */ }
.ambient-gallery-inner { position:relative; width:100%; }
.ambient-loop-track { display:flex; width:max-content; will-change:transform; }
.ambient-item { flex:0 0 160px; position:relative; margin:.65rem .65rem .65rem 0; border:1px solid #222; border-radius:4px; overflow:hidden; }
.ambient-item img { width:100%; height:140px; object-fit:cover; display:block; filter:brightness(.75); transition:filter .45s ease, transform .45s ease; }
.ambient-item:hover img { filter:brightness(1); transform:scale(1.07); }
.ambient-item:after { content:''; position:absolute; inset:0; background:linear-gradient(145deg,rgba(212,175,55,.18),rgba(0,0,0,.55)); opacity:0; transition:opacity .45s; }
.ambient-item:hover:after { opacity:.55; }
@media (max-width:600px){ .ambient-item { flex:0 0 42%; } }
.action-buttons { display:flex; justify-content:center; gap:1rem; flex-wrap:wrap; }
.action-buttons .btn { min-width:210px; letter-spacing:.05em; font-weight:600; }
.hero-btn { min-width:200px; letter-spacing:.06em; padding:.85rem 1.75rem; }
@media (max-width:575.98px){
  .hero-btn, .action-buttons .btn { width:100%; }
}
.hero { position:relative; }
#heroFadeHolder { position:absolute; inset:0; overflow:hidden; z-index:0; }
.hero-fade-layer {
  position:absolute; inset:0;
  background-size:cover; background-position:center;
  background-repeat:no-repeat;
  opacity:0; transition:opacity 1.2s ease;
  will-change:opacity;
}
.hero-fade-layer.active { opacity:1; }
.hero > div { position:relative; z-index:1; }
.hero-tagline { opacity:1; transition:opacity .6s ease; }
.hero-tagline.fading { opacity:0; }
</style>

<!-- Ambient Gallery Carousel -->
<section class="ambient-gallery-carousel py-3">
  <div class="ambient-gallery-inner">
    <div class="ambient-loop-track">
      <!-- Single set only -->
      <div class="ambient-item"><img src="images/gallery1.jpg" alt="Dining Atmosphere"></div>
      <div class="ambient-item"><img src="images/gallery2.jpg" alt="Aged Steaks"></div>
      <div class="ambient-item"><img src="images/gallery3.jpg" alt="Seafood Display"></div>
      <div class="ambient-item"><img src="images/gallery4.jpg" alt="Plated Wagyu"></div>
      <div class="ambient-item"><img src="images/gallery5.jpg" alt="Table Setting"></div>
      <div class="ambient-item"><img src="images/gallery6.jpg" alt="Chef Craft"></div>
    </div>
  </div>
</section>

<!-- Featured Dishes -->
<section class="py-5 bg-black">
  <div class="container">
    <h2 class="about-title mb-4">Featured Dishes</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/ribeye.jpg" alt="Signature Ribeye Steak">
          <div class="card-body">
            <h6 class="text-warning mb-1">Signature Ribeye Steak</h6>
            <p class="menu-desc mb-2">Butter-aged, rich marbling, deep beef flavor.</p>
            <p class="mb-1 price">₱519.00</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/wagyu_cubes.jpg" alt="Wagyu Cubes">
          <div class="card-body">
            <h6 class="text-warning mb-1">Wagyu Cubes</h6>
            <p class="menu-desc mb-2">Melt-in-mouth luxury seared perfection.</p>
            <p class="mb-1 price">₱1,499.00</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/seafood_platter.jpg" alt="Seafood Platter">
          <div class="card-body">
            <h6 class="text-warning mb-1">Seafood Platter</h6>
            <p class="menu-desc mb-2">Curated chilled shellfish selection.</p>
            <p class="mb-1 price">₱1,519.00</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/jumbo_shrimp.jpg" alt="Jumbo Shrimp Cocktail">
          <div class="card-body">
            <h6 class="text-warning mb-1">Jumbo Shrimp Cocktail</h6>
            <p class="menu-desc mb-2">Succulent jumbo shrimps with zesty sauce.</p>
            <p class="mb-1 price">₱519.00</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/beef_tartare.jpg" alt="Beef Tartare">
          <div class="card-body">
            <h6 class="text-warning mb-1">Beef Tartare</h6>
            <p class="menu-desc mb-2">Hand-chopped prime beef, delicate seasoning.</p>
            <p class="mb-1 price">₱519.00</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card menu-item h-100">
          <img src="images/burrata.jpg" alt="Burrata Salad">
          <div class="card-body">
            <h6 class="text-warning mb-1">Burrata Salad</h6>
            <p class="menu-desc mb-2">Creamy burrata with vibrant heirloom tomatoes.</p>
            <p class="mb-1 price">₱519.00</p>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-4 action-buttons">
      <a href="menu.php" class="btn btn-gold btn-lg">View Full Menu</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="reservation.php" class="btn btn-outline-light btn-lg border-warning">Reserve a Table</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline-light btn-lg border-warning">Login to Reserve</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
// Tagline rotation
(function(){
  const taglines=[
    'Fine Dining Reservation Experience',
    'Butter-Aged Steaks • Refined Texture',
    'Dry-Aged Cuts • Depth & Character',
    'Seafood Elegance • Chilled Purity',
    'Crafted Sauces • Balanced Pairings'
  ];
  let ti=0;
  const el=document.getElementById('heroTagline');
  function nextTagline(){
    el.classList.add('fading');
    setTimeout(()=>{
      ti=(ti+1)%taglines.length;
      el.textContent=taglines[ti];
      el.classList.remove('fading');
    },600);
  }
  setInterval(nextTagline,5000);
})();

/* Replaced static background swap with fade layers */
(function(){
  const imgs=['images/hero1.jpg','images/hero2.jpg','images/hero3.jpg'];
  const holder=document.getElementById('heroFadeHolder');
  if(!holder) return;
  const layers=imgs.map(src=>{
    const d=document.createElement('div');
    d.className='hero-fade-layer';
    d.style.backgroundImage="url('"+src+"')";
    holder.appendChild(d);
    return d;
  });
  let idx=0;
  layers[idx].classList.add('active');
  function rotate(){
    const prev=layers[idx];
    idx=(idx+1)%layers.length;
    const next=layers[idx];
    prev.classList.remove('active');
    next.classList.add('active');
  }
  setInterval(rotate,7000);
})();

(function(){
  const hero=document.getElementById('heroRotator');
  function adjust(){
    const nav=document.querySelector('nav');
    const foot=document.querySelector('footer');
    hero.style.minHeight=(window.innerHeight - (nav?nav.offsetHeight:0) - (foot?foot.offsetHeight:0))+'px';
  }
  window.addEventListener('resize',adjust); adjust();
})();

// Endless ambient loop (no reset jump)
(function(){
  const track=document.querySelector('.ambient-loop-track');
  const container=document.querySelector('.ambient-gallery-carousel');
  if(!track||!container) return;
  const baseItems=[...track.children];
  function totalWidth(){ return track.scrollWidth; }
  while(totalWidth() < container.offsetWidth * 2){
    baseItems.forEach(it=> track.appendChild(it.cloneNode(true)));
  }
  let offset=0, paused=false;
  const speed=0.4; // px per frame
  function itemFullWidth(el){
    const rect=el.getBoundingClientRect();
    const cs=getComputedStyle(el);
    return rect.width + parseFloat(cs.marginLeft||0) + parseFloat(cs.marginRight||0);
  }
  function step(){
    if(!paused){
      offset += speed;
      // Move first items to end when fully passed
      let first=track.firstElementChild;
      while(first && offset >= itemFullWidth(first)){
        offset -= itemFullWidth(first);
        track.appendChild(first);
        first=track.firstElementChild;
      }
      track.style.transform='translateX('+-offset+'px)';
    }
    requestAnimationFrame(step);
  }
  container.addEventListener('mouseenter',()=>paused=true);
  container.addEventListener('mouseleave',()=>paused=false);
  requestAnimationFrame(step);
  window.addEventListener('resize',()=>{ /* ensure enough clones after resize */ 
    while(totalWidth() < container.offsetWidth * 2){
      [...baseItems].forEach(it=> track.appendChild(it.cloneNode(true)));
    }
  });
})();
</script>
<?php include 'footer.php'; ?>
