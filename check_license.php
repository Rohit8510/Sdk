<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    include('conn.php');

    $license_key = $_POST['user_key'] ?? '';

    if (!$license_key) {
        echo json_encode(['status' => 'fail', 'reason' => 'License key missing']);
        exit;
    }

    $license_key = mysqli_real_escape_string($conn, $license_key);
    $result = mysqli_query($conn, "SELECT * FROM licenses WHERE license_key='$license_key'");

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['status' => 'fail', 'reason' => 'License key invalid']);
        exit;
    }

    $license = mysqli_fetch_assoc($result);

    if ((int)$license['status'] !== 1) {
        echo json_encode(['status' => 'fail', 'reason' => 'License inactive']);
        exit;
    }

    date_default_timezone_set('Asia/Kolkata');
    $current_time = new DateTime();
    $expiry_time = new DateTime($license['expiry_date']);

    if ($current_time > $expiry_time) {
        echo json_encode(['status' => 'fail', 'reason' => 'License expired']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'expiry' => $license['expiry_date']
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>License Validation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #1f1c2c, #928dab);
            font-family: 'Segoe UI', sans-serif;
            color: white;
            margin: 0;
            padding: 0;
        }
        #sidebar {
            position: fixed;
            top: 0;
            right: -260px;
            height: 100%;
            width: 260px;
            background: rgba(40, 40, 60, 0.95);
            backdrop-filter: blur(8px);
            color: white;
            padding-top: 20px;
            transition: right 0.3s ease;
            z-index: 9999;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.3);
            border-left: 1px solid #555;
        }
        #sidebar.active { right: 0; }
        .sidebar-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .vip-icon-box {
            width: 50px;
            height: 50px;
            margin: 0 auto;
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border-radius: 12px;
            box-shadow: 0 8px 15px rgba(255, 65, 108, 0.4);
        }
        .sidebar-title {
            margin-top: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffdfdf;
        }
        .sidebar-btn {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid #6c757d;
            color: #fff;
            text-align: center;
            width: 80%;
            margin: 10px auto;
            padding: 10px 15px;
            font-size: 0.95rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            display: block;
        }
        .sidebar-btn:hover {
            background-color: #6c757d;
            transform: scale(1.03);
        }
        #navbar {
            background-color: transparent;
            box-shadow: none;
            color: white;
        }
        .hamburger {
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 20px;
            height: 14px;
        }
        .hamburger span {
            display: block;
            height: 2.5px;
            background-color: white;
            border-radius: 2px;
        }
        .main-content {
            padding: 60px 20px;
            max-width: 700px;
            margin: auto;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <div class="sidebar-header">
        <div class="vip-icon-box"></div>
        <div class="sidebar-title">VIP Panel</div>
    </div>
   <button class="sidebar-btn" onclick="location.href='/dashboard'">Dashboard</button>
    <button class="sidebar-btn" onclick="location.href='/generate'">Generate License</button>
    <button class="sidebar-btn" onclick="location.href='license_check_ui.php'">Check Licenses</button>
    <button class="sidebar-btn" onclick="location.href='/licenses'">List Licenses</button>
    <button class="sidebar-btn" onclick="location.href='/edit-license'">Edit License</button>
    <button class="sidebar-btn" onclick="location.href='/users'">Manage Users</button>
    <button class="sidebar-btn" onclick="location.href='/referrals'">Manage Referrals</button>
    <button class="sidebar-btn" onclick="location.href='/logout'">Logout</button>
</div>

<nav id="navbar" class="navbar p-3 text-white">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div style="width: 40px;"></div>
        <h4 class="text-center flex-grow-1 m-0">License Validation</h4>
        <div class="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<div class="main-content">
    <h3>Welcome to the License Validation Page</h3>
    <p>This page is used to validate licenses. Please use the appropriate form to submit a license key for validation.</p>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}
</script>

</body>
</html>