<?php
// ========== DATABASE CONNECTION ==========
include 'conn.php';

// ========== HANDLE POST (SDK ACTIVATION) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_key'])) {
    header('Content-Type: application/json');
    error_reporting(0);

    $ownerContact = '@RennZohh';
    $res = $conn->query("SELECT setting_value FROM server_settings WHERE setting_key='owner_contact'");
    if ($res && $row = $res->fetch_assoc()) $ownerContact = $row['setting_value'];

    $serverMode = 'online';
    $modeRes = $conn->query("SELECT setting_value FROM server_settings WHERE setting_key='server_mode'");
    if ($modeRes && $row = $modeRes->fetch_assoc()) $serverMode = $row['setting_value'];

    $user_key    = trim($_POST['user_key']    ?? '');
    $package_name = trim($_POST['package_name'] ?? '');
    $device_id   = trim($_POST['device_id']   ?? '');

    if (empty($user_key) || empty($package_name)) {
        echo json_encode(["status" => "fail", "server_mode" => $serverMode, "message" => "Missing key or package"]);
        exit;
    }

    if ($serverMode !== 'online') {
        $msg = ($serverMode === 'maintenance') ? "Server under maintenance — try again soon" : "Server is offline";
        echo json_encode(["status" => "fail", "server_mode" => $serverMode, "message" => $msg]);
        exit;
    }

    $stmt = $conn->prepare("SELECT expiry_date, daemon, hide_root, toggle_expiry FROM licenses WHERE license_key = ? AND package_name = ? AND status = 1");
    $stmt->bind_param("ss", $user_key, $package_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $expiry = strtotime($row['expiry_date']);
        $now = time();
        if ($expiry < $now) {
            echo json_encode([
                "status" => "fail", "server_mode" => "online",
                "message" => "License expired — contact $ownerContact",
                "toggle_expiry" => (int)$row['toggle_expiry'],
                "feature1" => (int)$row['daemon'],
                "feature2" => (int)$row['hide_root']
            ]);
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $devStmt = $conn->prepare("INSERT INTO devices (device_id, package_name, license_key, ip_address, status, last_seen, connected_at) VALUES (?, ?, ?, ?, 'connected', NOW(), NOW()) ON DUPLICATE KEY UPDATE ip_address=VALUES(ip_address), status='connected', last_seen=NOW()");
        $devStmt->bind_param("ssss", $device_id, $package_name, $user_key, $ip);
        $devStmt->execute();
        $devStmt->close();

        $response = [
            "status"        => "success",
            "server_mode"   => "online",
            "expiry"        => $row['expiry_date'] ?: "",
            "toggle_expiry" => (int)$row['toggle_expiry'],
            "feature1"      => (int)$row['daemon'],
            "feature2"      => (int)$row['hide_root'],
            "message"       => "✅ RennZohh SDK Access Active"
        ];

        $notifRes = $conn->query("SELECT setting_value FROM server_settings WHERE setting_key='server_notification_json'");
        if ($notifRes && $notifRow = $notifRes->fetch_assoc()) {
            $notif = json_decode($notifRow['setting_value'], true);
            if ($notif && isset($notif['enabled']) && $notif['enabled'] == 1) {
                $response['server_notification'] = [
                    "enabled"  => 1,
                    "title"    => $notif['title']    ?? "System Note",
                    "message"  => $notif['message']  ?? "Welcome to RennZohh SDK!",
                    "iconType" => $notif['iconType'] ?? "event"
                ];
            }
        }

        echo json_encode($response);
    } else {
        echo json_encode(["status" => "fail", "server_mode" => "online", "message" => "Invalid license key — contact $ownerContact"]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// ========== GET REQUEST – PUBLIC LANDING PAGE ==========
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>RennZohh SDK Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<style>
:root {
    --primary: #7c3aed;
    --primary-light: #a855f7;
    --accent: #06b6d4;
    --accent2: #f59e0b;
    --bg: #080810;
    --card: rgba(255,255,255,0.04);
    --border: rgba(255,255,255,0.08);
    --text: #f1f5f9;
    --muted: #94a3b8;
}
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; }

/* Blobs */
.blob { position:fixed; border-radius:50%; filter:blur(100px); opacity:0.15; animation:drift 20s ease-in-out infinite alternate; z-index:0; pointer-events:none; }
.b1 { width:600px;height:600px;background:#7c3aed;top:-200px;left:-200px; }
.b2 { width:500px;height:500px;background:#06b6d4;bottom:-150px;right:-150px;animation-delay:-8s; }
.b3 { width:300px;height:300px;background:#f59e0b;top:50%;left:50%;animation-delay:-15s; }
@keyframes drift { from{transform:translate(0,0)} to{transform:translate(80px,-60px)} }

#particles-js { position:fixed;inset:0;z-index:1;pointer-events:none; }

/* Layout */
.site { position:relative;z-index:10;min-height:100vh;display:flex;flex-direction:column; }

/* Nav */
nav {
    display:flex; align-items:center; justify-content:space-between;
    padding:20px 48px;
    border-bottom:1px solid var(--border);
    backdrop-filter:blur(16px);
}
.nav-brand {
    font-family:'Space Grotesk',sans-serif;
    font-size:1.15rem; font-weight:700;
    background:linear-gradient(135deg,#a855f7,#06b6d4);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.nav-btn {
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border:none; border-radius:10px;
    padding:10px 24px; color:white;
    font-size:0.9rem; font-weight:600;
    cursor:pointer; text-decoration:none;
    box-shadow:0 4px 16px rgba(124,58,237,0.4);
    transition:transform 0.2s, box-shadow 0.2s;
}
.nav-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(124,58,237,0.55); }

/* Hero */
.hero {
    flex:1; display:flex;
    flex-direction:column; align-items:center; justify-content:center;
    text-align:center; padding:80px 24px 60px;
}
.hero-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(124,58,237,0.15); border:1px solid rgba(124,58,237,0.35);
    border-radius:999px; padding:6px 18px;
    font-size:0.78rem; font-weight:600; letter-spacing:1px;
    color:var(--primary-light); text-transform:uppercase;
    margin-bottom:28px;
    animation:fadeUp 0.5s ease both;
}
.hero-badge i { font-size:0.7rem; }
.hero h1 {
    font-family:'Space Grotesk',sans-serif;
    font-size:clamp(2.2rem, 5vw, 4rem);
    font-weight:800; line-height:1.1;
    margin-bottom:16px;
    background:linear-gradient(135deg,#f1f5f9 30%,#a855f7 65%,#06b6d4 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
    animation:fadeUp 0.5s 0.1s ease both;
}
.hero p {
    max-width:520px; font-size:1.05rem;
    color:var(--muted); line-height:1.7;
    margin-bottom:40px;
    animation:fadeUp 0.5s 0.2s ease both;
}
.hero-actions {
    display:flex; gap:14px; flex-wrap:wrap; justify-content:center;
    animation:fadeUp 0.5s 0.3s ease both;
}
.btn-primary {
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border:none; border-radius:12px; padding:14px 32px;
    color:white; font-size:1rem; font-weight:700;
    font-family:'Space Grotesk',sans-serif;
    cursor:pointer; text-decoration:none;
    box-shadow:0 8px 24px rgba(124,58,237,0.45);
    transition:transform 0.2s,box-shadow 0.2s;
}
.btn-primary:hover { transform:translateY(-3px); box-shadow:0 14px 36px rgba(124,58,237,0.6); }
.btn-outline {
    background:transparent;
    border:1px solid var(--border);
    border-radius:12px; padding:14px 32px;
    color:var(--text); font-size:1rem; font-weight:600;
    cursor:pointer; text-decoration:none;
    transition:border-color 0.2s, background 0.2s;
}
.btn-outline:hover { border-color:var(--primary-light); background:rgba(124,58,237,0.08); }

/* Status pill */
.status-pill {
    display:inline-flex; align-items:center; gap:6px;
    margin-top:28px;
    background:rgba(6,182,212,0.1); border:1px solid rgba(6,182,212,0.25);
    border-radius:999px; padding:6px 16px;
    font-size:0.8rem; color:var(--accent);
    animation:fadeUp 0.5s 0.4s ease both;
}
.pulse { width:8px;height:8px;border-radius:50%;background:#06b6d4;
    animation:pulse 2s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.85)} }

/* Feature cards */
.features {
    padding:0 48px 80px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px; max-width:1100px; margin:0 auto; width:100%;
}
.feat {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:20px; padding:28px 24px;
    transition:border-color 0.2s, transform 0.2s;
    animation:fadeUp 0.5s ease both;
}
.feat:hover { border-color:rgba(124,58,237,0.4); transform:translateY(-4px); }
.feat-icon {
    width:48px;height:48px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom:16px; font-size:1.2rem;
}
.feat-icon.purple { background:rgba(124,58,237,0.15); color:var(--primary-light); }
.feat-icon.cyan   { background:rgba(6,182,212,0.12);  color:var(--accent); }
.feat-icon.amber  { background:rgba(245,158,11,0.12); color:var(--accent2); }
.feat-icon.green  { background:rgba(34,197,94,0.12);  color:#4ade80; }
.feat h3 { font-family:'Space Grotesk',sans-serif; font-size:1rem; font-weight:700; margin-bottom:8px; }
.feat p  { font-size:0.85rem; color:var(--muted); line-height:1.6; }

/* Footer */
footer {
    text-align:center; padding:24px;
    border-top:1px solid var(--border);
    font-size:0.78rem; color:rgba(255,255,255,0.2);
    letter-spacing:0.5px;
}

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

@media(max-width:768px) {
    nav { padding:16px 20px; }
    .hero { padding:60px 20px 40px; }
    .features { padding:0 20px 60px; }
}
</style>
</head>
<body>
<div class="blob b1"></div>
<div class="blob b2"></div>
<div class="blob b3"></div>
<div id="particles-js"></div>

<div class="site">
    <nav>
        <div class="nav-brand"><i class="fa-solid fa-code"></i> RennZohh SDK</div>
        <a href="/login" class="nav-btn"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In</a>
    </nav>

    <section class="hero">
        <div class="hero-badge"><i class="fa-solid fa-bolt"></i> SDK License Management</div>
        <h1>RennZohh SDK Panel</h1>
        <p>Secure, fast, and reliable license management for your apps. Manage keys, devices, and users from one powerful dashboard.</p>
        <div class="hero-actions">
            <a href="/login" class="btn-primary"><i class="fa-solid fa-arrow-right-to-bracket"></i> Access Panel</a>
            <a href="/register" class="btn-outline"><i class="fa-solid fa-user-plus"></i> Get Started</a>
        </div>
        <div class="status-pill">
            <div class="pulse"></div>
            API Endpoint Active &mdash; POST /connect
        </div>
    </section>

    <section class="features">
        <div class="feat" style="animation-delay:0.1s">
            <div class="feat-icon purple"><i class="fa-solid fa-key"></i></div>
            <h3>License Keys</h3>
            <p>Generate, edit, and manage SDK license keys with expiry control and per-device binding.</p>
        </div>
        <div class="feat" style="animation-delay:0.2s">
            <div class="feat-icon cyan"><i class="fa-solid fa-shield-halved"></i></div>
            <h3>Secure Verification</h3>
            <p>Real-time key validation with server-mode control — online, maintenance, or offline.</p>
        </div>
        <div class="feat" style="animation-delay:0.3s">
            <div class="feat-icon amber"><i class="fa-solid fa-mobile-screen"></i></div>
            <h3>Device Tracking</h3>
            <p>Monitor connected devices, IP addresses, and session activity across all licenses.</p>
        </div>
        <div class="feat" style="animation-delay:0.4s">
            <div class="feat-icon green"><i class="fa-solid fa-users"></i></div>
            <h3>User Management</h3>
            <p>Multi-role support with owner, admin, and reseller scopes for team control.</p>
        </div>
    </section>

    <footer>
        &copy; <?= date('Y') ?> RennZohh SDK Panel &bull; All rights reserved
    </footer>
</div>

<script>
particlesJS('particles-js', {
    particles: {
        number: { value: 50, density: { enable:true, value_area:900 } },
        color: { value: ['#7c3aed','#06b6d4','#a855f7','#f59e0b'] },
        shape: { type:'circle' },
        opacity: { value:0.3, random:true },
        size: { value:2, random:true },
        line_linked: { enable:true, distance:130, color:'#7c3aed', opacity:0.1 },
        move: { enable:true, speed:1 }
    },
    interactivity: {
        events: { onhover:{enable:true,mode:'grab'} },
        modes: { grab:{distance:150,line_linked:{opacity:0.25}} }
    }
});
</script>
</body>
</html>
