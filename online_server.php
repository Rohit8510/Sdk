<?php
include 'conn.php';
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'] ?? 0;
$current_user = $_SESSION['username'];
$page_title = 'SDK Server';

$uq = $conn->query("SELECT role FROM users WHERE id=$user_id");
$urole = $uq ? $uq->fetch_assoc()['role'] ?? 'user' : 'user';
$is_owner = $urole === 'owner';

// Fetch settings
$serverStatus = 'online'; $mainMsg = 'Server is under maintenance. Please try again later.';
$r1 = $conn->query("SELECT setting_value FROM server_settings WHERE setting_key='server_status'");
if ($r1 && $x=$r1->fetch_assoc()) $serverStatus = $x['setting_value'];
$r2 = $conn->query("SELECT setting_value FROM server_settings WHERE setting_key='maintenance_message'");
if ($r2 && $x=$r2->fetch_assoc()) $mainMsg = $x['setting_value'];

// Handle POST
if ($_SERVER['REQUEST_METHOD']==='POST' && $is_owner) {
    if (isset($_POST['update_status'])) {
        $ns  = mysqli_real_escape_string($conn, $_POST['server_status']);
        $nm  = mysqli_real_escape_string($conn, $_POST['maintenance_message']);
        $conn->query("INSERT INTO server_settings (setting_key,setting_value) VALUES ('server_status','$ns') ON DUPLICATE KEY UPDATE setting_value='$ns'");
        $conn->query("INSERT INTO server_settings (setting_key,setting_value) VALUES ('maintenance_message','$nm') ON DUPLICATE KEY UPDATE setting_value='$nm'");
        if ($ns === 'offline') {
            $conn->query("UPDATE licenses SET status=0, blocked_by_server=1, last_blocked_at=NOW() WHERE status=1");
            $conn->query("UPDATE devices SET status='disconnected' WHERE status='connected'");
        } elseif ($ns === 'online') {
            $conn->query("UPDATE licenses SET status=1, blocked_by_server=0 WHERE blocked_by_server=1");
        }
        $serverStatus = $ns; $mainMsg = $nm;
    }
}

// Stats
$hasDevices = mysqli_num_rows($conn->query("SHOW TABLES LIKE 'devices'")) > 0;
$onlineDevices = 0; $packageStats = [];
if ($hasDevices) {
    $d = $conn->query("SELECT COUNT(*) c FROM devices WHERE status='connected'");
    $onlineDevices = $d ? (int)$d->fetch_assoc()['c'] : 0;
    $ps = $conn->query("SELECT package_name, COUNT(*) as cnt FROM devices WHERE status='connected' GROUP BY package_name ORDER BY cnt DESC LIMIT 10");
    if ($ps) while($r=$ps->fetch_assoc()) $packageStats[] = $r;
}
$activeLic  = (int)($conn->query("SELECT COUNT(*) c FROM licenses WHERE status=1")->fetch_assoc()['c'] ?? 0);
$totalLic   = (int)($conn->query("SELECT COUNT(*) c FROM licenses")->fetch_assoc()['c'] ?? 0);
$blocked    = (int)($conn->query("SELECT COUNT(*) c FROM licenses WHERE blocked_by_server=1")->fetch_assoc()['c'] ?? 0);

include '_sidebar.php';
?>

<div class="section-heading"><i class="fa-solid fa-server"></i> SDK Server</div>

<!-- Status Banner -->
<div style="background:<?= $serverStatus==='online'?'rgba(34,197,94,0.1)':($serverStatus==='maintenance'?'rgba(245,158,11,0.1)':'rgba(239,68,68,0.1)') ?>;
     border:1px solid <?= $serverStatus==='online'?'rgba(34,197,94,0.3)':($serverStatus==='maintenance'?'rgba(245,158,11,0.3)':'rgba(239,68,68,0.3)') ?>;
     border-radius:16px;padding:18px 22px;margin-bottom:28px;display:flex;align-items:center;gap:14px;">
    <div style="width:12px;height:12px;border-radius:50%;background:<?= $serverStatus==='online'?'#22c55e':($serverStatus==='maintenance'?'#f59e0b':'#ef4444') ?>;
         box-shadow:0 0 10px <?= $serverStatus==='online'?'#22c55e':($serverStatus==='maintenance'?'#f59e0b':'#ef4444') ?>;animation:pulse 2s infinite;flex-shrink:0;"></div>
    <div>
        <div style="font-weight:700;font-size:0.95rem;">Server is <?= ucfirst($serverStatus) ?></div>
        <?php if($serverStatus!=='online'): ?>
        <div style="font-size:0.82rem;color:var(--muted);margin-top:2px;"><?= htmlspecialchars($mainMsg) ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="stat-grid" style="margin-bottom:28px;">
    <div class="stat-card"><div class="stat-label">Online Devices</div><div class="stat-value cyan"><?= $onlineDevices ?></div></div>
    <div class="stat-card"><div class="stat-label">Active Licenses</div><div class="stat-value green"><?= $activeLic ?></div></div>
    <div class="stat-card"><div class="stat-label">Total Licenses</div><div class="stat-value white"><?= $totalLic ?></div></div>
    <div class="stat-card"><div class="stat-label">Server Blocked</div><div class="stat-value red"><?= $blocked ?></div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:24px;">

<!-- Server Control (owner only) -->
<?php if($is_owner): ?>
<div class="panel-card">
    <div class="section-heading" style="font-size:1rem;margin-bottom:18px;"><i class="fa-solid fa-sliders"></i> Server Control</div>
    <form method="POST">
        <div style="margin-bottom:16px;">
            <label class="rz-label">Server Mode</label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                <?php foreach(['online'=>['🟢','#22c55e'],'maintenance'=>['🟡','#f59e0b'],'offline'=>['🔴','#ef4444']] as $mode=>[$icon,$col]): ?>
                <label style="cursor:pointer;">
                    <input type="radio" name="server_status" value="<?=$mode?>" <?=$serverStatus===$mode?'checked':''?> style="display:none;" class="mode-radio">
                    <div class="mode-btn" data-mode="<?=$mode?>" style="text-align:center;padding:14px 8px;border-radius:12px;border:2px solid <?=$serverStatus===$mode?$col:'var(--border)'?>;
                        background:<?=$serverStatus===$mode?'rgba('.($mode==='online'?'34,197,94':($mode==='maintenance'?'245,158,11':'239,68,68')).',0.1)':'transparent'?>;
                        font-size:0.82rem;font-weight:600;transition:all 0.2s;">
                        <?=$icon?> <?=ucfirst($mode)?>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="margin-bottom:18px;">
            <label class="rz-label">Maintenance Message</label>
            <textarea name="maintenance_message" class="rz-input" rows="3" style="resize:vertical;"><?= htmlspecialchars($mainMsg) ?></textarea>
        </div>
        <button type="submit" name="update_status" class="btn-primary-rz" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-floppy-disk"></i> Apply Changes
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Active Packages -->
<div class="panel-card">
    <div class="section-heading" style="font-size:1rem;margin-bottom:18px;"><i class="fa-solid fa-boxes-stacked"></i> Active Packages</div>
    <?php if(empty($packageStats)): ?>
    <div style="text-align:center;padding:24px;color:var(--muted);">
        <i class="fa-solid fa-circle-info" style="font-size:1.5rem;margin-bottom:10px;display:block;"></i>
        No active devices connected.
    </div>
    <?php else: ?>
    <?php foreach($packageStats as $p): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
        <span style="font-size:0.85rem;"><?= htmlspecialchars($p['package_name']) ?></span>
        <span class="badge-cyan"><?= $p['cnt'] ?> devices</span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>

<style>
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.6;transform:scale(0.9)}}
</style>
<script>
document.querySelectorAll('.mode-radio').forEach(r=>{
    r.addEventListener('change',()=>{
        document.querySelectorAll('.mode-btn').forEach(b=>b.style.borderColor='var(--border)');
        r.nextElementSibling.style.borderColor='currentColor';
    });
});
</script>

<?php include '_footer.php'; ?>
