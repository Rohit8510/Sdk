<?php
session_start();
include 'conn.php';
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
$current_user = $_SESSION['username'] ?? 'Admin';
$page_title = 'Dashboard';

$total   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM licenses"))['c'] ?? 0;
$active  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM licenses WHERE status=1"))['c'] ?? 0;
$blocked = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM licenses WHERE status=0"))['c'] ?? 0;
$used    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT license_id) c FROM activated_packages"))['c'] ?? 0;
$unused  = $total - $used;
$users   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users"))['c'] ?? 0;

$licenses = mysqli_query($conn,"
    SELECT l.*, COUNT(DISTINCT ap.device_id) as unique_devices,
           MAX(ap.last_used) as last_activity,
           (SELECT ap2.ip_address FROM activated_packages ap2 WHERE ap2.license_id=l.id ORDER BY ap2.last_used DESC LIMIT 1) as last_ip
    FROM licenses l LEFT JOIN activated_packages ap ON ap.license_id=l.id
    GROUP BY l.id ORDER BY l.id DESC LIMIT 20");

include '_sidebar.php';
?>

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total</div>
        <div class="stat-value white"><?= $total ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active</div>
        <div class="stat-value green"><?= $active ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Blocked</div>
        <div class="stat-value red"><?= $blocked ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Used</div>
        <div class="stat-value cyan"><?= $used ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Unused</div>
        <div class="stat-value amber"><?= $unused ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Users</div>
        <div class="stat-value purple"><?= $users ?></div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap;">
    <a href="generate_ui.php" class="btn-primary-rz"><i class="fa-solid fa-plus"></i> New License</a>
    <a href="license_list.php" class="btn-outline-rz"><i class="fa-solid fa-list"></i> View All</a>
    <a href="online_server.php" class="btn-outline-rz"><i class="fa-solid fa-server"></i> Server Status</a>
</div>

<!-- Recent Licenses -->
<div class="panel-card">
    <div class="section-heading"><i class="fa-solid fa-key"></i> Recent Licenses</div>
    <div style="overflow-x:auto;">
    <table class="rz-table">
        <thead>
            <tr>
                <th>License Key</th>
                <th>Package</th>
                <th>Expiry</th>
                <th>Devices</th>
                <th>Last IP</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($licenses)):
            $exp = strtotime($r['expiry_date']);
            $expired = $exp < time();
        ?>
        <tr>
            <td>
                <span class="key-mono"><?= htmlspecialchars($r['license_key']) ?></span>
                <button onclick="copyText('<?= htmlspecialchars($r['license_key']) ?>')"
                    style="background:none;border:none;color:#64748b;cursor:pointer;margin-left:6px;font-size:0.75rem;">
                    <i class="fa-regular fa-copy"></i>
                </button>
            </td>
            <td style="color:#94a3b8;font-size:0.82rem;"><?= htmlspecialchars($r['package_name'] ?? '—') ?></td>
            <td style="font-size:0.82rem;color:<?= $expired?'#f87171':'#94a3b8' ?>"><?= $r['expiry_date'] ?></td>
            <td><span class="badge-cyan"><?= (int)($r['unique_devices'] ?? 0) ?></span></td>
            <td style="font-size:0.78rem;color:#64748b;"><?= $r['last_ip'] ?: '—' ?></td>
            <td><?php if($r['status']==1 && !$expired): ?>
                <span class="badge-active">Active</span>
            <?php elseif($expired): ?>
                <span class="badge-blocked">Expired</span>
            <?php else: ?>
                <span class="badge-blocked">Blocked</span>
            <?php endif; ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include '_footer.php'; ?>
