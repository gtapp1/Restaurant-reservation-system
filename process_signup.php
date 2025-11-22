<?php
require 'db.php';
session_start();
$fn=$_POST['first_name']??'';
$ln=$_POST['last_name']??'';
$em=$_POST['email']??'';
$pw=$_POST['password']??'';
if(!$fn||!$ln||!$em||!$pw){ header('Location: signup.php'); exit; }
$stmt=$mysqli->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param('s',$em); $stmt->execute(); $stmt->store_result();
if($stmt->num_rows){ $_SESSION['msg']='Email exists'; header('Location: signup.php'); exit; }
$stmt=$mysqli->prepare("INSERT INTO users(first_name,last_name,email,password_hash) VALUES (?,?,?,?)");
$hash=password_hash($pw,PASSWORD_BCRYPT);
$stmt->bind_param('ssss',$fn,$ln,$em,$hash);
$stmt->execute();
$_SESSION['user_id']=$stmt->insert_id;
$_SESSION['user_name']=$fn.' '.$ln;
header('Location: index.php');
