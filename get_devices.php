<?php
include 'conn.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if package name is provided
if (!isset($_GET['package']) || empty($_GET['package'])) {
    echo json_encode(['success' => false, 'message' => 'Package name required']);
    exit;
}

$package = $_GET['package'];

// Check if devices table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'devices'");
if ($tableCheck->num_rows == 0) {
    // Create devices table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS `devices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `device_id` varchar(255) NOT NULL,
        `package_name` varchar(255) NOT NULL,
        `license_key` varchar(255) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `status` enum('connected','disconnected','blocked') DEFAULT 'disconnected',
        `last_seen` timestamp NULL DEFAULT NULL,
        `connected_at` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `device_id` (`device_id`),
        KEY `package_name` (`package_name`),
        KEY `license_key` (`license_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    
    // Insert sample data for testing
    $conn->query("INSERT INTO `devices` (`device_id`, `package_name`, `license_key`, `ip_address`, `status`, `last_seen`) VALUES
        ('DEVICE-001', 'com.riyaz', 'VBOX-6B48C85A', '103.137.204.101', 'connected', NOW()),
        ('DEVICE-002', 'com.riyaz', 'VBOX-6B48C85A', '103.137.204.102', 'connected', NOW()),
        ('DEVICE-003', 'com.Dynamic.Loader', 'DYNAMIC-KEY-SDK', '103.137.204.103', 'connected', NOW()),
        ('DEVICE-004', 'com.Dynamic.Loader', 'DYNAMIC-KEY-SDK', '103.137.204.104', 'connected', DATE_SUB(NOW(), INTERVAL 5 MINUTE))");
}

// Get devices for this package with IP addresses
$stmt = $conn->prepare("SELECT device_id, ip_address, DATE_FORMAT(last_seen, '%Y-%m-%d %H:%i:%s') as last_seen 
                        FROM devices 
                        WHERE package_name = ? AND status = 'connected'
                        ORDER BY last_seen DESC");
$stmt->bind_param("s", $package);
$stmt->execute();
$result = $stmt->get_result();

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

echo json_encode([
    'success' => true,
    'devices' => $devices,
    'count' => count($devices)
]);
?>