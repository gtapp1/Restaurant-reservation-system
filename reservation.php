<?php include 'auth.php'; include 'header.php'; require 'db.php';
$items = $mysqli->query("SELECT * FROM menu_items ORDER BY category,name")->fetch_all(MYSQLI_ASSOC);
$today = date('Y-m-d');
?>
<style>
/* Inline UX improvements */
.res-section-box{background:#111;border:1px solid #333;border-radius:6px;padding:1.2rem 1.3rem;margin-bottom:1.2rem;}
.res-section-title{font-size:.85rem;font-weight:600;letter-spacing:.12em;color:#d4af37;margin-bottom:.65rem;text-transform:uppercase;display:flex;align-items:center;gap:.5rem;}
.res-section-title:before{content:'';display:inline-block;width:26px;height:2px;background:#d4af37;border-radius:2px;}
.res-grid .form-label{font-size:.7rem;font-weight:600;letter-spacing:.05em;color:#d4af37;margin-bottom:.35rem;}
.res-grid input.form-control,
.res-grid select.form-select{background:#151515;border:1px solid #2a2a2a;color:#fff;font-size:.8rem;}
.res-grid input.form-control:focus,
.res-grid select.form-select:focus{border-color:#d4af37;box-shadow:0 0 0 .15rem rgba(212,175,55,.25);}
.small-help{font-size:.6rem;color:#888;margin-top:.15rem;}
.divider-thin{height:2px;background:#222;border:none;margin:1.4rem 0;}
.sticky-actions{position:sticky;bottom:0;background:#0c0c0c;padding:.75rem 1rem;border-top:1px solid #333;display:flex;justify-content:flex-end;gap:.75rem;}
@media (max-width:768px){.sticky-actions{flex-wrap:wrap;justify-content:center;}}
.guest-block{position:relative;}
.guest-block h6{margin-bottom:.4rem;}
.auto-fade{animation:fadeIn .35s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(4px);}to{opacity:1;transform:translateY(0);}}
</style>

<div class="container py-5">
  <h2 class="about-title mb-4">Reservation</h2>
  <?php if(!empty($_SESSION['res_err'])){ echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['res_err']).'</div>'; $_SESSION['res_err']=null; } ?>

  <form method="post" action="reservation_submit.php" id="reservationForm" novalidate>
    <!-- Reservation Details Section -->
    <div class="res-section-box auto-fade">
      <div class="res-section-title">Reservation Details</div>
      <div class="row g-3 res-grid">
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="fullName">Full Name</label>
          <input id="fullName" name="full_name" class="form-control" value="<?=htmlspecialchars($_SESSION['user_name'])?>" placeholder="e.g. Jovi Josef" autocomplete="name" required>
        </div>
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="emailField">Email</label>
          <input id="emailField" type="email" name="email" class="form-control" placeholder="your@email.com" autocomplete="email" required>
        </div>
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="phoneField">Phone (11 digits)</label>
          <input id="phoneField" name="phone" class="form-control" maxlength="11" pattern="^09\d{9}$" inputmode="numeric" placeholder="09xxxxxxxxx" required>
          <div class="small-help">Must start with 09</div>
        </div>
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="dateField">Date</label>
          <input id="dateField" type="date" name="date" class="form-control" min="<?=$today?>" max="<?=date('Y-m-d',strtotime('+1 year'))?>" required>
          <div class="small-help">Future dates only</div>
        </div>
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="timeField">Time (11:00–23:00)</label>
          <input id="timeField" type="time" name="time" class="form-control" min="11:00" max="23:00" required>
          <div class="small-help">Outside range blocked</div>
        </div>
        <div class="col-md-4 col-sm-6">
          <label class="form-label" for="tablePref">Table Preference</label>
          <select id="tablePref" name="table_pref" class="form-select" required>
            <option value="" disabled selected>Select location...</option>
            <option>Window</option><option>Center</option><option>Corner</option><option>Alfresco</option>
          </select>
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label" for="guestCountEdit">Guests (1–10)</label>
          <input id="guestCountEdit" type="number" name="guest_count" min="1" max="10" value="1" class="form-control" aria-describedby="guestCountHelp">
          <div id="guestCountHelp" class="small-help">Adjust to auto-build guest sections</div>
        </div>
      </div>
    </div>

    <!-- Guest & Dish Selection -->
    <div class="res-section-box auto-fade">
      <div class="res-section-title">Guests & Dishes</div>
      <p class="small text-muted mb-3">Set guest names (optional). Use category filter + Add Dish to build each guest's order. Quantity max 10 per dish.</p>
      <div id="guestsContainerEdit"></div>
      <hr class="divider-thin">
      <div class="d-flex justify-content-end align-items-center gap-2">
        <span class="small text-light">Reservation Total:</span>
        <span id="reservationTotalEdit" class="fw-bold text-warning">₱0.00</span>
      </div>
    </div>

    <!-- Actions -->
    <div class="sticky-actions">
      <button class="btn btn-gold px-4" type="submit">Submit Reservation</button>
      <a href="menu.php" class="btn btn-outline-light border-warning px-4">View Menu</a>
    </div>
  </form>
</div>

<script>
const items = <?=json_encode($items)?>;
const CATS = Array.from(new Set(items.map(i=>i.category)));

// Builder (refactored from previous version; auto rebuild on guest count change)
document.addEventListener('DOMContentLoaded', () => {
  const guestCountEl=document.getElementById('guestCountEdit');
  const wrap=document.getElementById('guestsContainerEdit');
  let buildTimer=null;

  function scheduleBuild(){
    clearTimeout(buildTimer);
    buildTimer=setTimeout(buildGuests,180);
  }
  guestCountEl.addEventListener('input',scheduleBuild);
  buildGuests(); // initial

  function buildGuests(){
    const count=clamp(parseInt(guestCountEl.value||'1'),1,10);
    guestCountEl.value=count;
    wrap.innerHTML='';
    for(let gi=0; gi<count; gi++){
      wrap.appendChild(buildGuestBlock(gi));
    }
    updateTotals();
  }

  function buildGuestBlock(gi){
    const div=document.createElement('div');
    div.className='guest-block auto-fade mb-3 p-3 border border-secondary rounded';
    div.innerHTML=`
      <h6 class="text-warning">Guest ${gi+1}</h6>
      <input type="text" name="guest_name[]" class="form-control form-control-sm mb-3" placeholder="Guest name (optional)">
      <div class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label small mb-1">Category</label>
          <select class="form-select form-select-sm" id="cat-${gi}">
            <option value="">All</option>
            ${CATS.map(c=>`<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('')}
          </select>
        </div>
        <div class="col-sm-8">
          <label class="form-label small mb-1">Add Dish</label>
          <div class="dropdown">
            <button class="btn btn-sm btn-gold dropdown-toggle" type="button" data-bs-toggle="dropdown">Select Dish</button>
            <div class="dropdown-menu dropdown-menu-dark p-2 shadow" id="menu-${gi}" style="max-height:320px;overflow:auto;width:380px;"></div>
          </div>
        </div>
      </div>
      <div class="mt-3" id="sel-${gi}">
        <div class="text-muted small">No dishes selected yet.</div>
      </div>
      <div class="mt-2 text-end">
        <span class="small text-light">Guest Total:</span>
        <span class="fw-semibold text-warning" id="gt-${gi}">₱0.00</span>
      </div>
    `;
    const catSel=div.querySelector('#cat-'+gi);
    const menuBox=div.querySelector('#menu-'+gi);
    catSel.addEventListener('change',()=>renderMenu(menuBox,catSel.value,gi));
    renderMenu(menuBox,'',gi);
    return div;
  }

  function renderMenu(menuBox,cat,gi){
    const list=items.filter(it=>!cat||it.category===cat);
    menuBox.innerHTML=list.map(it=>menuItemRow(it,gi)).join('') || `<div class="px-2 py-1 text-muted">No items.</div>`;
    menuBox.querySelectorAll('.add-item').forEach(a=>{
      a.addEventListener('click',e=>{
        e.preventDefault();
        addSelectedLine(document.getElementById('sel-'+gi), gi, parseInt(a.dataset.id), a.dataset.name, parseFloat(a.dataset.price), a.dataset.image);
      });
    });
  }

  function menuItemRow(it,gi){
    const price=Number(it.price).toFixed(2);
    return `
      <a href="#" class="dropdown-item d-flex align-items-center gap-2 add-item" data-id="${it.id}" data-name="${escapeHtml(it.name)}" data-price="${price}" data-image="${escapeHtml(it.image)}">
        <img src="images/${escapeHtml(it.image)}" alt="${escapeHtml(it.name)}" style="width:72px;height:48px;object-fit:cover;border:1px solid #333;">
        <div class="flex-grow-1">
          <div class="small text-warning">${escapeHtml(it.name)}</div>
          <div class="small">₱${price}</div>
        </div>
        <span class="badge bg-warning text-dark">Add</span>
      </a>`;
  }

  function addSelectedLine(container, gi, id, name, price, image){
    const existing=container.querySelector(`[data-line-id="${id}"] input[type="number"]`);
    if(existing){
      existing.value=Math.min(10, parseInt(existing.value||'1')+1);
      updateTotals(); return;
    }
    const hint=container.querySelector('.text-muted.small'); if(hint) hint.remove();
    const row=document.createElement('div');
    row.className='d-flex align-items-center border border-secondary p-2 mb-2 bg-black rounded';
    row.setAttribute('data-line-id',id);
    row.setAttribute('data-price',String(price));
    row.innerHTML=`
      <img src="images/${escapeHtml(image)}" alt="${escapeHtml(name)}" style="width:80px;height:54px;object-fit:cover;border:1px solid #333;border-radius:3px;">
      <div class="ms-2 flex-grow-1 small">
        <div class="text-warning">${escapeHtml(name)}</div>
        <div class="text-light">₱${Number(price).toFixed(2)}</div>
      </div>
      <input type="number" min="1" max="10" value="1" name="qty[${gi}][${id}]" class="form-control form-control-sm quantity-input" style="width:72px">
      <button type="button" class="btn btn-sm btn-outline-light ms-2 remove-line" aria-label="Remove">×</button>
    `;
    const qty=row.querySelector('input[type="number"]');
    qty.addEventListener('input',()=>{ qty.value=clamp(parseInt(qty.value||'1'),1,10); updateTotals(); });
    row.querySelector('.remove-line').addEventListener('click',()=>{
      row.remove();
      if(!container.querySelector('[data-line-id]')){
        const empty=document.createElement('div');
        empty.className='text-muted small';
        empty.textContent='No dishes selected yet.';
        container.appendChild(empty);
      }
      updateTotals();
    });
    container.appendChild(row);
    updateTotals();
  }

  function calcGuestTotal(container){
    let total=0;
    container.querySelectorAll('[data-line-id]').forEach(row=>{
      const price=parseFloat(row.getAttribute('data-price')||'0');
      const qty=parseInt(row.querySelector('input[type="number"]').value||'0');
      total+=price*qty;
    });
    return total;
  }

  function updateTotals(){
    let grand=0;
    const count=parseInt(guestCountEl.value||'1');
    for(let gi=0; gi<count; gi++){
      const cont=document.getElementById('sel-'+gi);
      if(!cont) continue;
      const t=calcGuestTotal(cont);
      grand+=t;
      const tgt=document.getElementById('gt-'+gi);
      if(tgt) tgt.textContent=peso(t);
    }
    const rtot=document.getElementById('reservationTotalEdit');
    if(rtot) rtot.textContent=peso(grand);
  }

  // Validation helpers
  const phone=document.getElementById('phoneField');
  if(phone){
    phone.addEventListener('input',()=>{
      phone.value=phone.value.replace(/[^0-9]/g,'').slice(0,11);
    });
  }
  const time=document.getElementById('timeField');
  if(time){
    time.addEventListener('change',()=>{
      if(time.value<'11:00'||time.value>'23:00'){ alert('Time must be between 11:00 and 23:00'); time.value=''; }
    });
  }

  function peso(n){ return '₱'+Number(n).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
  function clamp(n,min,max){ return Math.max(min,Math.min(max,n)); }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
});
</script>
<?php include 'footer.php'; ?>
