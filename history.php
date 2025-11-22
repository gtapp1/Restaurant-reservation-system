<?php include 'auth.php'; include 'header.php'; require 'db.php';
$res=$mysqli->prepare("SELECT * FROM reservations WHERE user_id=? ORDER BY res_date DESC,res_time DESC");
$res->bind_param('i',$_SESSION['user_id']); $res->execute(); $reservations=$res->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="container py-5">
  <h2 class="about-title mb-4">Booking History</h2>
  <?php if(!empty($_SESSION['res_msg'])){ echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['res_msg']).'</div>'; $_SESSION['res_msg']=null; } ?>
  <?php if(!empty($_SESSION['res_err'])){ echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['res_err']).'</div>'; $_SESSION['res_err']=null; } ?>
  <?php if(!$reservations): ?>
    <div class="alert alert-info">No reservations.</div>
  <?php endif; ?>
  <?php foreach($reservations as $r): 
    $rid=$r['id'];
    // determine if future
    $dtObj = DateTime::createFromFormat('Y-m-d H:i:s', $r['res_date'].' '.$r['res_time']);
    $isFuture = $dtObj && $dtObj > new DateTime();
    $items=$mysqli->prepare("SELECT ri.*,mi.name FROM reservation_items ri JOIN menu_items mi ON ri.menu_item_id=mi.id WHERE reservation_id=?");
    $items->bind_param('i',$rid); $items->execute(); $its=$items->get_result(); $total=0;
  ?>
  <div class="mb-4 p-3 bg-black border border-warning">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
      <div>
        <strong><?=htmlspecialchars($r['res_date'])?> <?=htmlspecialchars(substr($r['res_time'],0,5))?></strong>
        <span class="ms-2 badge bg-warning text-dark"><?=htmlspecialchars($r['table_pref'])?></span>
        <span class="ms-2 small text-light">ID: <?=$rid?></span>
      </div>
      <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group">
        <a href="reservation_summary.php?id=<?=$rid?>" target="_blank" class="btn btn-outline-light border-warning" title="View / Print">View / PDF</a>
        <?php if($isFuture): ?>
        <form method="post" action="reservation_cancel.php" onsubmit="return confirm('Cancel this future reservation?');">
          <input type="hidden" name="id" value="<?=$rid?>">
          <button class="btn btn-outline-danger" title="Cancel">Cancel</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <table class="table table-dark table-sm mt-2">
      <thead><tr><th>Guest</th><th>Dish</th><th>Qty</th><th>Price</th><th>Sub</th></tr></thead>
      <tbody>
      <?php while($it=$its->fetch_assoc()): $sub=$it['price']*$it['quantity']; $total+=$sub; ?>
        <tr>
          <td><?=htmlspecialchars($it['guest_name'])?></td>
          <td><?=htmlspecialchars($it['name'])?></td>
          <td><?=htmlspecialchars($it['quantity'])?></td>
          <td>₱<?=number_format($it['price'],2)?></td>
          <td>₱<?=number_format($sub,2)?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <p class="mb-0 text-end text-warning fw-semibold">Total: ₱<?=number_format($total,2)?></p>
    <?php if(!$isFuture): ?><p class="mb-0 text-end small text-muted">Completed / Past</p><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
