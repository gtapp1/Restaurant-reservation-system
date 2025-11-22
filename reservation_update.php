<?php
require 'db.php'; session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$id=(int)($_POST['id']??0);
$stmt=$mysqli->prepare("SELECT * FROM reservations WHERE id=? AND user_id=?");
$stmt->bind_param('ii',$id,$_SESSION['user_id']); $stmt->execute(); $cur=$stmt->get_result()->fetch_assoc();
if(!$cur){ $_SESSION['res_err']='Not found'; header('Location: history.php'); exit; }

$full_name=$_POST['full_name']??''; $email=$_POST['email']??''; $phone=$_POST['phone']??'';
$date=$_POST['date']??''; $time=$_POST['time']??''; $table_pref=$_POST['table_pref']??''; $guest_count=(int)($_POST['guest_count']??0);
if(!$full_name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^09\d{9}$/',$phone)||!$date||!$time||$guest_count<1||$guest_count>10){
  $_SESSION['res_err']='Invalid input'; header("Location: reservation_edit.php?id=$id"); exit;
}
if(strtotime($date)<strtotime(date('Y-m-d'))){ $_SESSION['res_err']='Date must be future'; header("Location: reservation_edit.php?id=$id"); exit; }
if($time<'11:00'||$time>'23:00'){ $_SESSION['res_err']='Time out of range'; header("Location: reservation_edit.php?id=$id"); exit; }

$chk=$mysqli->prepare("SELECT id FROM reservations WHERE user_id=? AND res_date=? AND res_time=? AND id<>?");
$chk->bind_param('issi',$_SESSION['user_id'],$date,$time,$id); $chk->execute(); $chk->store_result();
if($chk->num_rows){ $_SESSION['res_err']='You already have a reservation at this time.'; header("Location: reservation_edit.php?id=$id"); exit; }

$upd=$mysqli->prepare("UPDATE reservations SET res_date=?, res_time=?, full_name=?, email=?, phone=?, table_pref=?, guest_count=? WHERE id=?");
$upd->bind_param('ssssssii',$date,$time,$full_name,$email,$phone,$table_pref,$guest_count,$id);
$upd->execute();

// Clear existing guest/item data then reinsert
$mysqli->prepare("DELETE FROM reservation_guests WHERE reservation_id=$id")->execute();
$mysqli->prepare("DELETE FROM reservation_items WHERE reservation_id=$id")->execute();

$guest_names=$_POST['guest_name']??[];
$quantities=$_POST['qty']??[];
for($i=0;$i<count($guest_names);$i++){
  $gname=trim($guest_names[$i])?:'Guest '.($i+1);
  $gstmt=$mysqli->prepare("INSERT INTO reservation_guests(reservation_id,guest_name) VALUES (?,?)");
  $gstmt->bind_param('is',$id,$gname); $gstmt->execute();
  if(isset($quantities[$i])){
    foreach($quantities[$i] as $mid=>$q){
      $q=(int)$q;
      if($q>0 && $q<=10){
        $mi=$mysqli->prepare("SELECT price FROM menu_items WHERE id=?");
        $mi->bind_param('i',$mid); $mi->execute(); $pr=$mi->get_result()->fetch_assoc()['price']??0;
        $ist=$mysqli->prepare("INSERT INTO reservation_items(reservation_id,guest_name,menu_item_id,quantity,price) VALUES (?,?,?,?,?)");
        $ist->bind_param('isiii',$id,$gname,$mid,$q,$pr); $ist->execute();
      }
    }
  }
}
$_SESSION['res_msg']='Reservation updated.';
header("Location: reservation_summary.php?id=$id");
