<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
    header('Location: reservation.php');
    exit;
}

// Validate date using DateTime
$dtObj    = DateTime::createFromFormat('Y-m-d', $date);
$validDate = $dtObj && $dtObj->format('Y-m-d') === $date;
$yearOk   = $validDate
    && (int)$dtObj->format('Y') >= (int)date('Y')
    && (int)$dtObj->format('Y') <= (int)date('Y') + 1;

if (!$validDate || !$yearOk) {
    $_SESSION['res_err'] = 'Invalid date.';
    header('Location: reservation.php');
    exit;
}
if ($dtObj <= new DateTime('today')) {
    $_SESSION['res_err'] = 'Date must be a future date.';
    header('Location: reservation.php');
    exit;
}

// Validate time range
if ($time < '11:00' || $time > '23:00') {
    $_SESSION['res_err'] = 'Time must be between 11:00 and 23:00.';
    header('Location: reservation.php');
    exit;
}

// Check for duplicate reservation at same date/time
$dup = $mysqli->prepare("SELECT id FROM reservations WHERE user_id = ? AND res_date = ? AND res_time = ?");
$dup->bind_param('iss', $_SESSION['user_id'], $date, $time);
$dup->execute();
$dup->store_result();
if ($dup->num_rows) {
    $_SESSION['res_err'] = 'You already have a reservation at this date and time.';
    header('Location: reservation.php');
    exit;
}

// Insert main reservation record
try {
    $stmt = $mysqli->prepare(
        "INSERT INTO reservations (user_id, res_date, res_time, full_name, email, phone, table_pref, guest_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('issssssi', $_SESSION['user_id'], $date, $time, $full_name, $email, $phone, $table_pref, $guest_count);
    $stmt->execute();
    $res_id = $stmt->insert_id;
} catch (mysqli_sql_exception $e) {
    $_SESSION['res_err'] = 'Reservation could not be saved. Please try again.';
    header('Location: reservation.php');
    exit;
}

// FIX C2: Batch-fetch all menu item prices in one query (was N+1 loop)
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

// Insert guests and ordered items
$total = 0;
for ($i = 0; $i < count($guest_names); $i++) {
    $gname = trim($guest_names[$i]) ?: 'Guest ' . ($i + 1);

    $gstmt = $mysqli->prepare("INSERT INTO reservation_guests (reservation_id, guest_name) VALUES (?, ?)");
    $gstmt->bind_param('is', $res_id, $gname);
    $gstmt->execute();

    if (isset($quantities[$i])) {
        foreach ($quantities[$i] as $mid => $q) {
            $mid = (int)$mid;
            $q   = (int)$q;
            if ($q > 0 && $q <= 10 && isset($priceMap[$mid])) {
                $pr    = $priceMap[$mid];
                $line  = $pr * $q;
                $total += $line;
                $ist   = $mysqli->prepare(
                    "INSERT INTO reservation_items (reservation_id, guest_name, menu_item_id, quantity, price) VALUES (?, ?, ?, ?, ?)"
                );
                // FIX: Use 'd' (double) for price — was 'i' (int) causing wrong price storage
                $ist->bind_param('isiid', $res_id, $gname, $mid, $q, $pr);
                $ist->execute();
            }
        }
    }
}

$_SESSION['last_res_id'] = $res_id;
header("Location: reservation_summary.php?id=$res_id");
exit;
