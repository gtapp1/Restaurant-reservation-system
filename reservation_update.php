<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

// Verify ownership of the reservation
$own = $mysqli->prepare("SELECT id FROM reservations WHERE id = ? AND user_id = ?");
$own->bind_param('ii', $id, $_SESSION['user_id']);
$own->execute();
if (!$own->get_result()->fetch_assoc()) {
    $_SESSION['res_err'] = 'Reservation not found.';
    header('Location: history.php');
    exit;
}

// Gather and trim inputs
$full_name   = trim($_POST['full_name']   ?? '');
$email       = trim($_POST['email']       ?? '');
$phone       = trim($_POST['phone']       ?? '');
$date        = trim($_POST['date']        ?? '');
$time        = trim($_POST['time']        ?? '');
$table_pref  = trim($_POST['table_pref']  ?? '');
$guest_count = (int)($_POST['guest_count'] ?? 0);

// Validate basic inputs
if (!$full_name
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !preg_match('/^09\d{9}$/', $phone)
    || !$date || !$time
    || $guest_count < 1 || $guest_count > 10
) {
    $_SESSION['res_err'] = 'Invalid input. Please check all fields.';
    header("Location: reservation_edit.php?id=$id");
    exit;
}

// FIX B3: Use DateTime for date validation (consistent with reservation_submit.php)
$dtObj = DateTime::createFromFormat('Y-m-d', $date);
if (!$dtObj || $dtObj->format('Y-m-d') !== $date || $dtObj <= new DateTime('today')) {
    $_SESSION['res_err'] = 'Date must be a future date.';
    header("Location: reservation_edit.php?id=$id");
    exit;
}

// Validate time range
if ($time < '11:00' || $time > '23:00') {
    $_SESSION['res_err'] = 'Time must be between 11:00 and 23:00.';
    header("Location: reservation_edit.php?id=$id");
    exit;
}

// Check for duplicate reservation at same date/time (excluding current)
$chk = $mysqli->prepare(
    "SELECT id FROM reservations WHERE user_id = ? AND res_date = ? AND res_time = ? AND id <> ?"
);
$chk->bind_param('issi', $_SESSION['user_id'], $date, $time, $id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows) {
    $_SESSION['res_err'] = 'You already have a reservation at this date and time.';
    header("Location: reservation_edit.php?id=$id");
    exit;
}

// Update main reservation record
$upd = $mysqli->prepare(
    "UPDATE reservations SET res_date=?, res_time=?, full_name=?, email=?, phone=?, table_pref=?, guest_count=? WHERE id=?"
);
$upd->bind_param('ssssssii', $date, $time, $full_name, $email, $phone, $table_pref, $guest_count, $id);
$upd->execute();

// FIX B1/S1: Use prepared statements for DELETE (was SQL injection via string interpolation)
$del1 = $mysqli->prepare("DELETE FROM reservation_guests WHERE reservation_id = ?");
$del1->bind_param('i', $id);
$del1->execute();

$del2 = $mysqli->prepare("DELETE FROM reservation_items WHERE reservation_id = ?");
$del2->bind_param('i', $id);
$del2->execute();

// FIX C1: Batch-fetch all menu item prices in one query (was N+1 loop)
$guest_names = $_POST['guest_name'] ?? [];
$quantities  = $_POST['qty'] ?? [];

$allMenuIds = [];
foreach ($quantities as $gItems) {
    foreach ($gItems as $mid => $q) {
        $mid = (int)$mid;
        if ($mid > 0) {
            $allMenuIds[] = $mid;
        }
    }
}
$priceMap = [];
if (!empty($allMenuIds)) {
    $uniqueIds = array_unique($allMenuIds);
    $in        = implode(',', $uniqueIds); // Safe: all cast to (int) above
    $pRes      = $mysqli->query("SELECT id, price FROM menu_items WHERE id IN ($in)");
    while ($pr = $pRes->fetch_assoc()) {
        $priceMap[(int)$pr['id']] = (float)$pr['price'];
    }
}

// Re-insert guests and ordered items
for ($i = 0; $i < count($guest_names); $i++) {
    $gname = trim($guest_names[$i]) ?: 'Guest ' . ($i + 1);

    $gstmt = $mysqli->prepare("INSERT INTO reservation_guests (reservation_id, guest_name) VALUES (?, ?)");
    $gstmt->bind_param('is', $id, $gname);
    $gstmt->execute();

    if (isset($quantities[$i])) {
        foreach ($quantities[$i] as $mid => $q) {
            $mid = (int)$mid;
            $q   = (int)$q;
            if ($q > 0 && $q <= 10 && isset($priceMap[$mid])) {
                $pr  = $priceMap[$mid];
                $ist = $mysqli->prepare(
                    "INSERT INTO reservation_items (reservation_id, guest_name, menu_item_id, quantity, price) VALUES (?, ?, ?, ?, ?)"
                );
                // FIX B2: Use 'd' (double) for price — was 'i' (int) causing wrong price storage
                $ist->bind_param('isiid', $id, $gname, $mid, $q, $pr);
                $ist->execute();
            }
        }
    }
}

$_SESSION['res_msg'] = 'Reservation updated successfully.';
header("Location: reservation_summary.php?id=$id");
exit; // FIX C11: was missing exit after header()
