<?php
require 'db.php'; session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$full_name=$_POST['full_name']??''; $email=$_POST['email']??''; $phone=$_POST['phone']??'';
$date=$_POST['date']??''; $time=$_POST['time']??''; $table_pref=$_POST['table_pref']??''; $guest_count=(int)($_POST['guest_count']??0);

// New: strict date format & range check
$dtObj = DateTime::createFromFormat('Y-m-d',$date);
$validDate = $dtObj && $dtObj->format('Y-m-d') === $date;
$yearOk = $validDate && (int)$dtObj->format('Y') >= (int)date('Y') && (int)$dtObj->format('Y') <= (int)date('Y')+1;

if(!$full_name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^09\d{9}$/',$phone)||!$validDate||!$yearOk||!$time||$guest_count<1||$guest_count>10){
  $_SESSION['res_err']='Invalid input'; header('Location: reservation.php'); exit;
}
if($dtObj <= new DateTime('today')){ $_SESSION['res_err']='Date must be future'; header('Location: reservation.php'); exit; }
if($time<'11:00'||$time>'23:00'){ $_SESSION['res_err']='Time out of range'; header('Location: reservation.php'); exit; }

// Uniqueness
$stmt=$mysqli->prepare("SELECT id FROM reservations WHERE user_id=? AND res_date=? AND res_time=?");
$stmt->bind_param('iss',$_SESSION['user_id'],$date,$time); $stmt->execute(); $stmt->store_result();
if($stmt->num_rows){ $_SESSION['res_err']='You already have a reservation at this time.'; header('Location: reservation.php'); exit; }

// Safe insert with try/catch
try {
  $stmt=$mysqli->prepare("INSERT INTO reservations(user_id,res_date,res_time,full_name,email,phone,table_pref,guest_count) VALUES (?,?,?,?,?,?,?,?)");
  $stmt->bind_param('issssssi',$_SESSION['user_id'],$date,$time,$full_name,$email,$phone,$table_pref,$guest_count);
  $stmt->execute(); $res_id=$stmt->insert_id;
} catch(mysqli_sql_exception $e){
  $_SESSION['res_err']='Reservation could not be saved.'; header('Location: reservation.php'); exit;
}

$guest_names=$_POST['guest_name']??[];
$quantities=$_POST['qty']??[];
$total=0;
for($i=0;$i<count($guest_names);$i++){
  $gname=trim($guest_names[$i]); if(!$gname) $gname='Guest '.($i+1);
  $gstmt=$mysqli->prepare("INSERT INTO reservation_guests(reservation_id,guest_name) VALUES (?,?)");
  $gstmt->bind_param('is',$res_id,$gname); $gstmt->execute();
  if(isset($quantities[$i])){
    foreach($quantities[$i] as $mid=>$q){
      $q=(int)$q;
      if($q>0 && $q<=10){
        $mi=$mysqli->prepare("SELECT price FROM menu_items WHERE id=?");
        $mi->bind_param('i',$mid); $mi->execute(); $pr=$mi->get_result()->fetch_assoc()['price']??0;
        $line=$pr*$q; $total+=$line;
        $ist=$mysqli->prepare("INSERT INTO reservation_items(reservation_id,guest_name,menu_item_id,quantity,price) VALUES (?,?,?,?,?)");
        $ist->bind_param('isiii',$res_id,$gname,$mid,$q,$pr); $ist->execute();
      }
    }
  }
}
$_SESSION['last_res_id']=$res_id;
header("Location: reservation_summary.php?id=$res_id");
