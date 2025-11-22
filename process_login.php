<?php
require 'db.php'; session_start();
$em=$_POST['email']??''; $pw=$_POST['password']??'';
$stmt=$mysqli->prepare("SELECT id,first_name,last_name,password_hash FROM users WHERE email=?");
$stmt->bind_param('s',$em); $stmt->execute(); $res=$stmt->get_result();
if($row=$res->fetch_assoc()){
  if(password_verify($pw,$row['password_hash'])){
    $_SESSION['user_id']=$row['id'];
    $_SESSION['user_name']=$row['first_name'].' '.$row['last_name'];
    header('Location: index.php'); exit;
  }
}
$_SESSION['msg']='Invalid credentials';
header('Location: login.php');
