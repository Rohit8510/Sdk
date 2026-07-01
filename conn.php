<?php
$servername = "localhost";
$username = "your_usernamr";
$password = "your_password";
$dbname = "your_db";

$conn = mysqli_connect($servername,$username,$password,$dbname);

if (!$conn) {
    die(json_encode(['status' => 'fail', 'reason' => 'Database connection failed']));
}
?>
