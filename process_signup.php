<?php
require 'db.php';
session_start();

$fn = trim($_POST['first_name'] ?? '');
$ln = trim($_POST['last_name']  ?? '');
$em = trim($_POST['email']      ?? '');
$pw = $_POST['password']        ?? '';

if (!$fn || !$ln || !filter_var($em, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
    $_SESSION['msg'] = 'Please fill in all fields correctly. Password must be at least 6 characters.';
    header('Location: signup.php');
    exit;
}

// Check if email already registered
$check = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param('s', $em);
$check->execute();
$check->store_result();
if ($check->num_rows) {
    $_SESSION['msg'] = 'This email is already registered. Please log in.';
    header('Location: signup.php');
    exit;
}

// Insert new user
$hash = password_hash($pw, PASSWORD_BCRYPT);
$ins  = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
$ins->bind_param('ssss', $fn, $ln, $em, $hash);
$ins->execute();

// FIX B5: Check insert actually succeeded before trusting insert_id
if ($ins->affected_rows !== 1) {
    $_SESSION['msg'] = 'Registration failed. Please try again.';
    header('Location: signup.php');
    exit;
}

// FIX S7: Regenerate session ID after successful registration/login
session_regenerate_id(true);
$_SESSION['user_id']   = $ins->insert_id;
$_SESSION['user_name'] = $fn . ' ' . $ln;
header('Location: index.php');
exit;
