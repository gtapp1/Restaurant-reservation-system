<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

if ($_SESSION['admin_role'] != 1) {
    $_SESSION['admin_err'] = 'Permission denied.';
    header('Location: menu.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: menu.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    $_SESSION['admin_err'] = 'Invalid item ID.';
    header('Location: menu.php');
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM menu_items WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['admin_msg'] = "Menu item #{$id} deleted.";
} else {
    $_SESSION['admin_err'] = "Menu item #{$id} not found.";
}

header('Location: menu.php');
exit;
