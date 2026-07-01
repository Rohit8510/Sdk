<?php
session_start();
include 'conn.php';
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
$current_user = $_SESSION['username'] ?? 'Admin';
$page_title = 'License List';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    $id=(int)$_POST['delete_id'];
    $conn->query("DELETE FROM licenses WHERE id=$id");
    header("Location: license_list.php"); exit();
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_id'])) {
    $id=(int)$_POST['toggle_id'];
    $conn->query("UPDATE licenses SET status = IF(status=1,0,1) WHERE id=$id");
    header("Location: license_list.php"); exit();
}

$search = trim($_GET['q'] ?? '');
$where  = $search ? "WHERE license_key LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR package_name LIKE '%".mysqli_real_escape_string($conn,$search)."%'" : '';
$result = $conn->query("SELECT * FROM licenses $where ORDER BY id DESC");
include '_sidebar.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div class="section-heading" style="margin:0;"><i class="fa-solid fa-list-check"></i> License List</div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <form method="GET" style="display:flex;gap:8px;">
            <input name="q" value="<?= htmlspecialchars($search) ?>" class="rz-input" placeholder="Search key or package..." style="width:220px;">
            <button type="submit" class="btn-primary-rz"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
        <a href="generate_ui.php" class="btn-primary-rz"><i class="fa-solid fa-plus"></i> New</a>
    </div>
</div>

<div class="panel-card" style="padding:0;overflow:hidden;">
<div style="overflow-x:auto;">
<table class="rz-table">
    <thead>
        <tr>
            <th>#</th>
            <th>License Key</th>
            <th>Package</th>
            <th>Expiry</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php $i=0; while($r=$result->fetch_assoc()): $i++;
        $exp=strtotime($r['expiry_date']);
        $expired=$exp<time();
    ?>
    <tr>
        <td style="color:#64748b;font-size:0.8rem;"><?= $i ?></td>
        <td>
            <span class="key-mono"><?= htmlspecialchars($r['license_key']) ?></span>
            <button onclick="copyText('<?= htmlspecialchars($r['license_key']) ?>')" style="background:none;border:none;color:#64748b;cursor:pointer;margin-left:4px;"><i class="fa-regular fa-copy" style="font-size:0.75rem;"></i></button>
        </td>
        <td style="color:#94a3b8;font-size:0.82rem;"><?= htmlspecialchars($r['package_name'] ?? '—') ?></td>
        <td style="font-size:0.82rem;color:<?= $expired?'#f87171':'#94a3b8' ?>"><?= $r['expiry_date'] ?></td>
        <td>
            <?php if($r['status']==1 && !$expired): ?><span class="badge-active">Active</span>
            <?php elseif($expired): ?><span class="badge-blocked">Expired</span>
            <?php else: ?><span class="badge-blocked">Blocked</span><?php endif; ?>
        </td>
        <td>
            <div style="display:flex;gap:6px;">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="toggle_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn-outline-rz" style="padding:6px 12px;font-size:0.75rem;">
                        <?= $r['status']==1 ? 'Block' : 'Unblock' ?>
                    </button>
                </form>
                <a href="keyEdit.php?id=<?= $r['id'] ?>" class="btn-outline-rz" style="padding:6px 12px;font-size:0.75rem;">Edit</a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this license?')">
                    <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn-danger-rz" style="padding:6px 12px;font-size:0.75rem;">Del</button>
                </form>
            </div>
        </td>
    </tr>
    <?php endwhile; ?>
    <?php if($i===0): ?>
    <tr><td colspan="6" style="text-align:center;color:#64748b;padding:40px;">No licenses found</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
</div>

<?php include '_footer.php'; ?>
