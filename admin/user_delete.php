<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

if ($_SESSION['admin_role'] != 1) {
    $_SESSION['admin_err'] = 'Permission denied.';
    header('Location: users.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    $_SESSION['admin_err'] = 'Invalid user ID.';
    header('Location: users.php');
    exit;
}

// Verify user exists
$check = $mysqli->prepare("SELECT id FROM users WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
if (!$check->get_result()->fetch_assoc()) {
    $_SESSION['admin_err'] = "User #$id not found.";
    header('Location: users.php');
    exit;
}

// Delete user (cascade via FK for reservations, reservation_guests, reservation_items)
$del = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$del->bind_param('i', $id);
$del->execute();

// Log action
$adminId = (int)$_SESSION['admin_id'];
$log = $mysqli->prepare("INSERT INTO admin_logs (admin_id, action, target) VALUES (?, 'delete_user', ?)");
$target = "user_id={$id}";
$log->bind_param('is', $adminId, $target);
$log->execute();

$_SESSION['admin_msg'] = "User #$id and all associated data deleted.";
header('Location: users.php');
exit;
