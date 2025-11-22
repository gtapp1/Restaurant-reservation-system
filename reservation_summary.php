<?php include 'auth.php'; include 'header.php'; require 'db.php';
$id=(int)($_GET['id']??0);
$stmt=$mysqli->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
$stmt->bind_param('ii',$id,$_SESSION['user_id']); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
if(!$res){ echo '<div class="container py-5"><div class="alert alert-danger">Not found.</div></div>'; include 'footer.php'; exit; }
$items=$mysqli->prepare("SELECT ri.*, mi.name FROM reservation_items ri JOIN menu_items mi ON ri.menu_item_id=mi.id WHERE reservation_id=?");
$items->bind_param('i',$id); $items->execute(); $itres=$items->get_result();
$total=0; $rows=[];
while($r=$itres->fetch_assoc()){ $sub=$r['price']*$r['quantity']; $total+=$sub; $r['sub']=$sub; $rows[]=$r; }
$baseUrl = (isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']),'/');
$qrTarget = $baseUrl.'/reservation_summary.php?id='.$id;
$qrImage = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='.urlencode($qrTarget);
?>
<div class="container py-5 reservation-summary-print">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h2 class="about-title mb-0">Reservation Summary</h2>
    <div class="btn-group mt-3 mt-md-0" role="group">
      <a href="reservation_edit.php?id=<?=$id?>" class="btn btn-gold btn-sm">Edit</a>
      <form method="post" action="reservation_cancel.php" onsubmit="return confirm('Cancel this reservation?');" class="d-inline">
        <input type="hidden" name="id" value="<?=$id?>">
        <button class="btn btn-outline-light btn-sm border-warning">Cancel</button>
      </form>
      <button type="button" class="btn btn-outline-light btn-sm border-warning" onclick="window.print()">Print / PDF</button>
    </div>
  </div>
  <p class="small text-light mb-4">Reservation ID: <?=htmlspecialchars($res['id'])?></p>
  <div class="print-header d-none">
    <div class="w-100 text-center">
      <h1 style="margin:0;font-size:2.2rem;font-weight:700;background:#d4af37;color:#000;display:inline-block;padding:.6rem 1.4rem;border-radius:4px;box-shadow:0 0 8px rgba(212,175,55,.4);">La Flamme</h1>
      <div class="gold-line"></div>
      <p style="margin:0;font-size:.85rem;letter-spacing:.15em;color:#d4af37;">FINE DINING RESERVATION SUMMARY</p>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-4"><div class="p-3 bg-black border border-warning">
      <p class="mb-1"><strong>Date:</strong> <?=htmlspecialchars($res['res_date'])?></p>
      <p class="mb-1"><strong>Time:</strong> <?=htmlspecialchars(substr($res['res_time'],0,5))?></p>
      <p class="mb-1"><strong>Table:</strong> <?=htmlspecialchars($res['table_pref'])?></p>
      <p class="mb-1"><strong>Guests:</strong> <?=htmlspecialchars($res['guest_count'])?></p>
    </div></div>
    <div class="col-md-8">
      <table class="table table-dark table-striped table-bordered">
        <thead><tr><th>Guest</th><th>Dish</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['guest_name'])?></td>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=htmlspecialchars($r['quantity'])?></td>
            <td>₱<?=number_format($r['price'],2)?></td>
            <td>₱<?=number_format($r['sub'],2)?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <p class="summary-total">Grand Total: ₱<?=number_format($total,2)?></p>
    </div>
  </div>
  <div class="row mt-4">
    <div class="col-md-4">
      <div class="p-3 bg-black border border-warning text-center">
        <h6 class="text-warning mb-2">Scan Reservation</h6>
        <img src="<?=htmlspecialchars($qrImage)?>" alt="QR code for reservation <?=$id?>" class="qr-code img-fluid" style="width:180px;height:180px;">
        <p class="small text-light mt-2 mb-0">Scan to view / verify online<br>Reservation ID: <?=$id?></p>
      </div>
    </div>
    <div class="col-md-8">
      <div class="mt-4 p-4 bg-black border border-warning text-center">
        <h3 class="text-warning">Thank You</h3>
        <p>Your fine dining experience at La Flamme is confirmed.</p>
      </div>
    </div>
  </div>
</div>
<style>
@media print {
  body { background:#000 !important; color:#fff !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  nav, footer, .btn-group { display:none !important; }
  .about-title { display:none !important; }
  .reservation-summary-print { background:#000; border:2px solid #d4af37; padding:30px 26px 40px; border-radius:6px; }
  .print-header { display:block !important; margin-bottom:18px; }
  .gold-line { height:4px; background:#d4af37; width:160px; margin:10px auto 14px; border-radius:2px; }
  table.table-dark { background:#050505 !important; }
  table.table-dark thead { background:#000 !important; border-bottom:2px solid #d4af37; }
  table.table-dark th, table.table-dark td { border-color:#333 !important; }
  .summary-total { color:#d4af37 !important; font-weight:700; }
  .qr-code { border:1px solid #d4af37; padding:6px; background:#050505; border-radius:4px; }
  .qr-box, .thank-box { background:#050505 !important; border:1px solid #d4af37 !important; }
  a { color:#d4af37 !important; text-decoration:none; }
  .text-warning { color:#d4af37 !important; }
  .bg-black { background:#050505 !important; }
  .border-warning { border-color:#d4af37 !important; }
}
</style>
<?php include 'footer.php'; ?>
