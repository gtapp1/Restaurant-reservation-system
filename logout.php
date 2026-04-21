<?php
session_start();
// FIX B4/S2: Properly clear session before destroying it
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
