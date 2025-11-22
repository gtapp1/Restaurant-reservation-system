<?php
$mysqli = new mysqli('localhost','root','1234','laflamme');
if ($mysqli->connect_errno) { die('DB error'); }
$mysqli->set_charset('utf8mb4');
?>
