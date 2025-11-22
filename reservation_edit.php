<?php include 'auth.php'; include 'header.php'; require 'db.php';
$id=(int)($_GET['id']??0);
$stmt=$mysqli->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
$stmt->bind_param('ii',$id,$_SESSION['user_id']); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
if(!$res){ echo '<div class="container py-5"><div class="alert alert-danger">Not found.</div></div>'; include 'footer.php'; exit; }

$guests = $mysqli->prepare("SELECT * FROM reservation_guests WHERE reservation_id=?");
$guests->bind_param('i',$id); $guests->execute(); $guestRows=$guests->get_result()->fetch_all(MYSQLI_ASSOC);

$itemsAll = $mysqli->query("SELECT * FROM menu_items ORDER BY category,name")->fetch_all(MYSQLI_ASSOC);
$itemsMap=[]; // guest_name => [ {id,qty}... ]
$ri=$mysqli->prepare("SELECT guest_name, menu_item_id, quantity FROM reservation_items WHERE reservation_id=?");
$ri->bind_param('i',$id); $ri->execute(); $rs=$ri->get_result();
while($row=$rs->fetch_assoc()){ $itemsMap[$row['guest_name']][]=['id'=>$row['menu_item_id'],'qty'=>$row['quantity']]; }

$today=date('Y-m-d');
?>
<div class="container py-5">
  <h2 class="about-title mb-4">Edit Reservation</h2>
  <form method="post" action="reservation_update.php" id="editReservationForm">
    <input type="hidden" name="id" value="<?=htmlspecialchars($id)?>">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Full Name</label>
        <input name="full_name" class="form-control" value="<?=htmlspecialchars($res['full_name'])?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($res['email'])?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" maxlength="11" pattern="^09\d{9}$" value="<?=htmlspecialchars($res['phone'])?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" min="<?=$today?>" value="<?=htmlspecialchars($res['res_date'])?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Time (11:00 - 23:00)</label>
        <input type="time" name="time" class="form-control" min="11:00" max="23:00" value="<?=htmlspecialchars(substr($res['res_time'],0,5))?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Table Preference</label>
        <select name="table_pref" class="form-select" required>
          <?php foreach(['Window','Center','Corner','Outdoor','Alfresco'] as $tp): ?>
            <option <?=$tp===$res['table_pref']?'selected':''?>><?=$tp?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Guests (1–10)</label>
        <div class="d-flex gap-2">
          <input type="number" id="guestCountEdit" name="guest_count" min="1" max="10" value="<?=count($guestRows)?>" class="form-control w-auto">
          <button type="button" class="btn btn-gold" id="applyGuestsEdit">Apply</button>
        </div>
      </div>
    </div>
    <hr class="border-warning my-4">
    <h5 class="text-warning mb-3">Guest Dish Selection</h5>
    <div id="guestsContainerEdit"></div>
    <div class="mt-3 text-end">
      <span class="small text-light">Reservation Total:</span>
      <span id="reservationTotalEdit" class="fw-bold text-warning">₱0.00</span>
    </div>
    <button class="btn btn-gold mt-3">Save Changes</button>
    <a href="reservation_summary.php?id=<?=$id?>" class="btn btn-outline-light mt-3 border-warning">Back</a>
  </form>
</div>
<script>
const items = <?=json_encode($itemsAll)?>;
const existingGuests = <?=json_encode(array_values(array_map(fn($g)=>$g['guest_name'], $guestRows)))?>;
const existingSelections = <?=json_encode($itemsMap)?>;
const CATS = Array.from(new Set(items.map(i=>i.category)));

document.addEventListener('DOMContentLoaded',()=>{
  const guestCountEl=document.getElementById('guestCountEdit');
  const applyBtn=document.getElementById('applyGuestsEdit');
  const wrap=document.getElementById('guestsContainerEdit');
  applyBtn.addEventListener('click',buildGuests);
  buildGuests();

  function buildGuests(){
    const count=clamp(parseInt(guestCountEl.value||'1'),1,10);
    guestCountEl.value=count;
    wrap.innerHTML='';
    for(let gi=0; gi<count; gi++){
      wrap.appendChild(buildGuestBlock(gi, existingGuests[gi]||''));
    }
    prefill();
    updateTotals();
  }
  function buildGuestBlock(gi, gname){
    const div=document.createElement('div');
    div.className='guest-block';
    div.innerHTML=`
      <h6 class="text-warning">Guest ${gi+1}</h6>
      <input type="text" name="guest_name[]" class="form-control mb-3" placeholder="Guest Name" value="${escapeHtml(gname)}">
      <div class="row g-2 align-items-end">
        <div class="col-sm-4">
          <label class="form-label small">Category</label>
          <select class="form-select form-select-sm" id="cat-${gi}">
            <option value="">All</option>
            ${CATS.map(c=>`<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`).join('')}
          </select>
        </div>
        <div class="col-sm-8">
          <label class="form-label small">Add Dish</label>
          <div class="dropdown">
            <button class="btn btn-sm btn-gold dropdown-toggle" type="button" data-bs-toggle="dropdown">Select</button>
            <div class="dropdown-menu dropdown-menu-dark p-2" id="menu-${gi}" style="max-height:320px;overflow:auto;width:380px;"></div>
          </div>
        </div>
      </div>
      <div class="mt-3" id="sel-${gi}">
        <div class="text-muted small">No dishes selected yet.</div>
      </div>
      <div class="mt-2 text-end">
        <span class="small text-light">Guest Total:</span>
        <span class="fw-semibold text-warning" id="gt-${gi}">₱0.00</span>
      </div>`;
    const catSel=div.querySelector('#cat-'+gi);
    const menuBox=div.querySelector('#menu-'+gi);
    catSel.addEventListener('change',()=>renderMenu(menuBox,catSel.value,gi));
    renderMenu(menuBox,'',gi);
    return div;
  }
  function renderMenu(menuBox,cat,gi){
    const list=items.filter(it=>!cat||it.category===cat);
    menuBox.innerHTML=list.map(it=>`
      <a href="#" class="dropdown-item d-flex align-items-center gap-2 add-item" data-id="${it.id}" data-name="${escapeHtml(it.name)}" data-price="${Number(it.price).toFixed(2)}" data-image="${escapeHtml(it.image)}">
        <img src="images/${escapeHtml(it.image)}" alt="${escapeHtml(it.name)}" style="width:72px;height:48px;object-fit:cover;border:1px solid #333;">
        <div class="flex-grow-1">
          <div class="small text-warning">${escapeHtml(it.name)}</div>
          <div class="small">₱${Number(it.price).toFixed(2)}</div>
        </div>
        <span class="badge bg-warning text-dark">Add</span>
      </a>`).join('');
    menuBox.querySelectorAll('.add-item').forEach(a=>{
      a.addEventListener('click',e=>{
        e.preventDefault();
        addLine(gi, parseInt(a.dataset.id), a.dataset.name, parseFloat(a.dataset.price), a.dataset.image);
      });
    });
  }
  function prefill(){
    existingGuests.forEach((g,i)=>{
      const selections=existingSelections[g]||[];
      selections.forEach(sel=>{
        const it=items.find(x=>x.id==sel.id);
        if(it) addLine(i,it.id,it.name,it.price,it.image,sel.qty);
      });
    });
    updateTotals();
  }
  function addLine(gi,id,name,price,image,qty=1){
    const container=document.getElementById('sel-'+gi);
    const existing=container.querySelector(`[data-line-id="${id}"] input`);
    if(existing){
      existing.value=Math.min(10, parseInt(existing.value||'1')+(qty||1));
      updateTotals(); return;
    }
    const hint=container.querySelector('.text-muted.small'); if(hint) hint.remove();
    const row=document.createElement('div');
    row.className='d-flex align-items-center border border-secondary p-2 mb-2 bg-black';
    row.setAttribute('data-line-id',id);
    row.setAttribute('data-price',String(price));
    row.innerHTML=`
      <img src="images/${escapeHtml(image)}" alt="${escapeHtml(name)}" style="width:90px;height:60px;object-fit:cover;border:1px solid #333;">
      <div class="ms-2 flex-grow-1 small">
        <div class="text-warning">${escapeHtml(name)}</div>
        <div class="text-light">₱${Number(price).toFixed(2)}</div>
      </div>
      <input type="number" min="1" max="10" value="${qty}" name="qty[${gi}][${id}]" class="form-control form-control-sm quantity-input" style="width:80px">
      <button type="button" class="btn btn-sm btn-outline-light ms-2 remove-line">×</button>`;
    const qtyInput=row.querySelector('input');
    qtyInput.addEventListener('input',()=>{ qtyInput.value=clamp(parseInt(qtyInput.value||'1'),1,10); updateTotals(); });
    row.querySelector('.remove-line').addEventListener('click',()=>{
      row.remove();
      if(!container.querySelector('[data-line-id]')){
        const empty=document.createElement('div'); empty.className='text-muted small'; empty.textContent='No dishes selected yet.'; container.appendChild(empty);
      }
      updateTotals();
    });
    container.appendChild(row);
    updateTotals();
  }
  function calcGuestTotal(container){
    let t=0;
    container.querySelectorAll('[data-line-id]').forEach(r=>{
      const p=parseFloat(r.getAttribute('data-price')||'0');
      const q=parseInt(r.querySelector('input').value||'0');
      t+=p*q;
    });
    return t;
  }
  function updateTotals(){
    let grand=0;
    const count=parseInt(guestCountEl.value||'1');
    for(let gi=0; gi<count; gi++){
      const cont=document.getElementById('sel-'+gi);
      const gt=calcGuestTotal(cont);
      grand+=gt;
      const tgt=document.getElementById('gt-'+gi); if(tgt) tgt.textContent=peso(gt);
    }
    const rtot=document.getElementById('reservationTotalEdit');
    if(rtot) rtot.textContent=peso(grand);
  }
  function peso(n){ return '₱'+Number(n).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
  function clamp(n,min,max){ return Math.max(min,Math.min(max,n)); }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
});
</script>
<?php include 'footer.php'; ?>
