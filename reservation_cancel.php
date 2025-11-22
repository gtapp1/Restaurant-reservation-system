<?php
require 'db.php'; session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$id=(int)($_POST['id']??0);
$stmt=$mysqli->prepare("SELECT res_date,res_time FROM reservations WHERE id=? AND user_id=?");
$stmt->bind_param('ii',$id,$_SESSION['user_id']); $stmt->execute(); $res=$stmt->get_result()->fetch_assoc();
if(!$res){ $_SESSION['res_err']='Reservation not found.'; header('Location: history.php'); exit; }

$dt = DateTime::createFromFormat('Y-m-d H:i:s', $res['res_date'].' '.$res['res_time']);
if(!$dt){ $_SESSION['res_err']='Invalid reservation data.'; header('Location: history.php'); exit; }
if($dt <= new DateTime()){ $_SESSION['res_err']='Cannot cancel past or ongoing reservation.'; header('Location: history.php'); exit; }

$del=$mysqli->prepare("DELETE FROM reservations WHERE id=?");
$del->bind_param('i',$id); $del->execute();
$_SESSION['res_msg']='Reservation canceled successfully.';
header('Location: history.php');
