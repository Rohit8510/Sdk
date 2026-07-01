<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit(); }
include('conn.php');

$current_user = $_SESSION['username'];
$page_title = 'Packages';

$license_id = intval($_GET['license_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle'])) {
    $pkg_id = intval($_POST['id']);
    $status = intval($_POST['status']);
    mysqli_query($conn, "UPDATE activated_packages SET is_allowed=$status WHERE id=$pkg_id");
}

$pkgs = $license_id
    ? mysqli_query($conn, "SELECT * FROM activated_packages WHERE license_id=$license_id ORDER BY id DESC")
    : mysqli_query($conn, "SELECT * FROM activated_packages ORDER BY id DESC LIMIT 100");

$license_key = '';
if ($license_id) {
    $lr = $conn->query("SELECT license_key FROM licenses WHERE id=$license_id");
    if ($lr && $row = $lr->fetch_assoc()) $license_key = $row['license_key'];
}

include '_sidebar.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div class="section-heading" style="margin:0;">
        <i class="fa-solid fa-box"></i>
        <?= $license_id ? 'Packages for <span class="key-mono">'.htmlspecialchars($license_key).'</span>' : 'All Activated Packages' ?>
    </div>
    <?php if($license_id): ?>
    <a href="license_list.php" class="btn-outline-rz"><i class="fa-solid fa-arrow-left"></i> Back to Licenses</a>
    <?php endif; ?>
</div>

<?php if(mysqli_num_rows($pkgs)===0): ?>
<div class="panel-card" style="text-align:center;padding:60px 24px;">
    <i class="fa-solid fa-box-open" style="font-size:2.5rem;color:var(--muted);margin-bottom:16px;display:block;"></i>
    <div style="font-size:1rem;font-weight:600;margin-bottom:8px;">No packages found</div>
    <div style="color:var(--muted);font-size:0.875rem;">No packages have been activated yet.</div>
</div>
<?php else: ?>
<div class="panel-card" style="padding:0;overflow:hidden;">
<div style="overflow-x:auto;">
<table class="rz-table">
    <thead><tr>
        <th>#</th><th>Package Name</th><th>License Key</th><th>Device ID</th><th>IP Address</th><th>Last Used</th><th>Status</th><th>Action</th>
    </tr></thead>
    <tbody>
    <?php $i=0; while($p=mysqli_fetch_assoc($pkgs)): $i++;
        $allowed = ($p['is_allowed'] ?? 1) == 1;
    ?>
    <tr>
        <td style="color:var(--muted);font-size:0.8rem;"><?=$i?></td>
        <td><span style="font-size:0.85rem;font-weight:500;"><?=htmlspecialchars($p['package_name']??'—')?></span></td>
        <td><span class="key-mono" style="font-size:0.78rem;"><?=htmlspecialchars(substr($p['license_key']??'—',0,20))?>…</span></td>
        <td style="font-size:0.78rem;color:var(--muted);font-family:monospace;"><?=htmlspecialchars(substr($p['device_id']??'—',0,16))?>…</td>
        <td style="font-size:0.8rem;color:var(--muted);"><?=htmlspecialchars($p['ip_address']??'—')?></td>
        <td style="font-size:0.78rem;color:var(--muted);"><?=$p['last_used']??'—'?></td>
        <td><?=$allowed?'<span class="badge-active">Allowed</span>':'<span class="badge-blocked">Blocked</span>'?></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?=$p['id']?>">
                <input type="hidden" name="toggle" value="1">
                <input type="hidden" name="status" value="<?=$allowed?0:1?>">
                <button type="submit" class="<?=$allowed?'btn-danger-rz':'btn-outline-rz'?>" style="padding:6px 12px;font-size:0.78rem;">
                    <i class="fa-solid fa-<?=$allowed?'ban':'check'?>"></i> <?=$allowed?'Block':'Allow'?>
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>
