<?php
require_once 'includes/auth_admin.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservations.php');
    exit;
}

$id     = (int)($_POST['id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if (!$id || !in_array($action, ['confirm', 'cancel', 'delete'])) {
    $_SESSION['admin_err'] = 'Invalid request.';
    header('Location: reservations.php');
    exit;
}

// Fetch reservation
$check = $mysqli->prepare("SELECT id, status FROM reservations WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if (!$res) {
    $_SESSION['admin_err'] = "Reservation #{$id} not found.";
    header('Location: reservations.php');
    exit;
}

// Helper: log admin action
function logAdminAction(mysqli $db, int $adminId, string $action, string $target): void {
    $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $adminId, $action, $target);
    $stmt->execute();
}

$adminId = (int)$_SESSION['admin_id'];
$target  = "reservation_id={$id}";

switch ($action) {
    case 'confirm':
        $stmt = $mysqli->prepare("UPDATE reservations SET status='confirmed' WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        logAdminAction($mysqli, $adminId, 'confirm_reservation', $target);
        $_SESSION['admin_msg'] = "Reservation #{$id} has been confirmed.";
        break;

    case 'cancel':
        if (!in_array($res['status'], ['pending','confirmed'])) {
            $_SESSION['admin_err'] = "Cannot cancel a {$res['status']} reservation.";
            header('Location: reservations.php');
            exit;
        }
        $stmt = $mysqli->prepare("UPDATE reservations SET status='cancelled' WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        logAdminAction($mysqli, $adminId, 'cancel_reservation', $target);
        $_SESSION['admin_msg'] = "Reservation #{$id} has been cancelled.";
        break;

    case 'delete':
        if ($_SESSION['admin_role'] != 1) {
            $_SESSION['admin_err'] = 'Permission denied. Only Super Admins can delete reservations.';
            header('Location: reservations.php');
            exit;
        }
        $stmt = $mysqli->prepare("DELETE FROM reservations WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        logAdminAction($mysqli, $adminId, 'delete_reservation', $target);
        $_SESSION['admin_msg'] = "Reservation #{$id} permanently deleted.";
        break;
}

$ref = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($ref, 'reservation_view.php') !== false) {
    header('Location: reservations.php');
} else {
    header('Location: reservations.php');
}
exit;
