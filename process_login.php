<?php
require 'db.php';
session_start();

$em = trim($_POST['email']    ?? '');
$pw = trim($_POST['password'] ?? '');

if (!$em || !$pw) {
    $_SESSION['msg'] = 'Please enter your email and password.';
    header('Location: login.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?");
$stmt->bind_param('s', $em);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row && password_verify($pw, $row['password_hash'])) {
    // FIX S7: Regenerate session ID after login to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id']   = $row['id'];
    $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
    header('Location: index.php');
    exit;
}

$_SESSION['msg'] = 'Invalid email or password.';
header('Location: login.php');
exit;
