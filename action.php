<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<?php
include('conn.php');

// Toggle Active/Inactive
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    mysqli_query($conn, "UPDATE licenses SET status = NOT status WHERE id=$id");
    header('Location: dashboard.php');
}

// Delete License
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM licenses WHERE id=$id");
    header('Location: dashboard.php');
}
?>
