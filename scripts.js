document.addEventListener('DOMContentLoaded',()=>{
  const applyBtn=document.getElementById('applyGuests');
  if(applyBtn){
    applyBtn.addEventListener('click',buildGuests);
    buildGuests();
  }
  function buildGuests(){
    const count=parseInt(document.getElementById('guestCount').value||'1');
    const wrap=document.getElementById('guestsContainer');
    wrap.innerHTML='';
    for(let g=0; g<count; g++){
      const div=document.createElement('div');
      div.className='guest-block';
      div.innerHTML='<h6 class="text-warning">Guest '+(g+1)+'</h6>'
        +'<input type="text" name="guest_name[]" class="form-control mb-2" placeholder="Guest Name">'
        +buildMenu(g);
      wrap.appendChild(div);
    }
  }
  function buildMenu(gi){
    let html='<div class="row g-2">';
    const grouped={};
    (window.items||[]).forEach(it=>{
      grouped[it.category]=grouped[it.category]||[];
      grouped[it.category].push(it);
    });
    for(const cat in grouped){
      html+='<div class="col-12"><strong class="text-light">'+cat+'</strong></div>';
      grouped[cat].forEach(it=>{
        html+='<div class="col-6 col-md-4"><div class="border p-2 h-100">'
          +'<img src="images/'+escapeHtml(it.image)+'" alt="'+escapeHtml(it.name)+'" class="img-fluid mb-2" style="width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;">'
          +'<div class="small text-warning">'+escapeHtml(it.name)+'</div>'
          +'<div class="small">₱'+Number(it.price).toFixed(2)+'</div>'
          +'<input type="number" min="0" max="10" value="0" name="qty['+gi+']['+it.id+']" class="form-control form-control-sm quantity-input mt-1" />'
          +'</div></div>';
      });
    }
    html+='</div>';
    return html;
  }
  function escapeHtml(s){ return s.replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

  // Phone enforce length & start
  const phone=document.querySelector('input[name="phone"]');
  if(phone){
    phone.addEventListener('input',()=>{
      phone.value=phone.value.replace(/[^0-9]/g,'').slice(0,11);
    });
  }
  // Time range extra block
  const time=document.querySelector('input[name="time"]');
  if(time){
    time.addEventListener('change',()=>{
      if(time.value<'11:00'||time.value>'23:00'){ alert('Time must be between 11:00 and 23:00'); time.value=''; }
    });
  }
  const categoryFilter=document.getElementById('categoryFilter');
  if(categoryFilter){
    categoryFilter.addEventListener('change',()=>{
      const val=categoryFilter.value;
      const cols=[...document.querySelectorAll('.menu-col[data-category]')];
      const heads=[...document.querySelectorAll('.cat-heading')];
      cols.forEach(col=>{
        col.style.display=(!val||col.dataset.category===val)?'':'none';
      });
      heads.forEach(h=>{
        const cat=h.getAttribute('data-cat-heading');
        const any=cols.some(c=>c.dataset.category===cat && c.style.display!=='none');
        h.style.display=any?'':'none';
      });
    });
  }
});
