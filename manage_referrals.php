<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
include 'conn.php';

$current_user    = $_SESSION['username'];
$current_user_id = $_SESSION['user_id'] ?? 0;
$page_title      = 'Referrals';

$uq = $conn->prepare("SELECT role FROM users WHERE id=?");
$uq->bind_param("i",$current_user_id); $uq->execute();
$urow = $uq->get_result()->fetch_assoc();
$is_owner = ($urow['role']??'') === 'owner';
$is_admin = in_array($urow['role']??'',['owner','admin']);

if (isset($_POST['generate'])) {
    $newCode = strtoupper(bin2hex(random_bytes(4)));
    $assigned = $_POST['assigned_to'] ?? 'user';
    $cols = mysqli_query($conn,"SHOW COLUMNS FROM referral_codes LIKE 'assigned_to'");
    if (mysqli_num_rows($cols)>0) {
        $s=$conn->prepare("INSERT INTO referral_codes (code,assigned_to,created_by) VALUES (?,?,?)");
        $s->bind_param("ssi",$newCode,$assigned,$current_user_id);
    } else {
        $s=$conn->prepare("INSERT INTO referral_codes (code) VALUES (?)");
        $s->bind_param("s",$newCode);
    }
    $s->execute();
    header("Location: manage_referrals.php?gen=".urlencode($newCode)); exit();
}

if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $s = $conn->prepare("DELETE FROM referral_codes WHERE id=?");
    $s->bind_param("i", $id);
    $s->execute();
    header("Location: manage_referrals.php"); exit();
}

$hasUsedBy = mysqli_num_rows(mysqli_query($conn,"SHOW COLUMNS FROM referral_codes LIKE 'used_by'"))>0;
$referrals = $hasUsedBy
    ? mysqli_query($conn,"SELECT r.*,u.username AS used_by_username FROM referral_codes r LEFT JOIN users u ON r.used_by=u.id ORDER BY r.id DESC")
    : mysqli_query($conn,"SELECT * FROM referral_codes ORDER BY id DESC");

include '_sidebar.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div class="section-heading" style="margin:0;"><i class="fa-solid fa-share-nodes"></i> Referral Codes</div>
    <?php if($is_admin): ?>
    <button onclick="document.getElementById('genModal').style.display='flex'" class="btn-primary-rz">
        <i class="fa-solid fa-plus"></i> Generate Code
    </button>
    <?php endif; ?>
</div>

<?php if(isset($_GET['gen'])): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:14px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
    <i class="fa-solid fa-circle-check" style="color:#4ade80;font-size:1.2rem;"></i>
    <div>
        <div style="font-weight:700;font-size:0.9rem;margin-bottom:4px;">Code Generated!</div>
        <span class="key-mono" style="font-size:1rem;"><?= htmlspecialchars($_GET['gen']) ?></span>
    </div>
    <button onclick="copyText('<?= htmlspecialchars($_GET['gen']) ?>')" class="btn-outline-rz" style="margin-left:auto;padding:7px 14px;font-size:0.8rem;">
        <i class="fa-regular fa-copy"></i> Copy
    </button>
</div>
<?php endif; ?>

<div class="panel-card" style="padding:0;overflow:hidden;">
<div style="overflow-x:auto;">
<table class="rz-table">
    <thead><tr>
        <th>#</th><th>Code</th><?php if($hasUsedBy): ?><th>Used By</th><?php endif; ?>
        <th>Status</th><?php if($is_admin): ?><th>Actions</th><?php endif; ?>
    </tr></thead>
    <tbody>
    <?php if(mysqli_num_rows($referrals)===0): ?>
    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">No referral codes yet.</td></tr>
    <?php endif; ?>
    <?php $i=0; while($r=mysqli_fetch_assoc($referrals)): $i++;
        $used = !empty($r['used_by']);
    ?>
    <tr>
        <td style="color:var(--muted);font-size:0.8rem;"><?=$i?></td>
        <td>
            <span class="key-mono"><?=htmlspecialchars($r['code'])?></span>
            <button onclick="copyText('<?=htmlspecialchars($r['code'])?>')"
                style="background:none;border:none;color:var(--muted);cursor:pointer;margin-left:6px;font-size:0.78rem;">
                <i class="fa-regular fa-copy"></i>
            </button>
        </td>
        <?php if($hasUsedBy): ?>
        <td>
            <?php if($used): ?>
            <span style="color:#a855f7;font-size:0.85rem;font-weight:600;">
                <i class="fa-solid fa-user"></i> <?=htmlspecialchars($r['used_by_username']??'Unknown')?>
            </span>
            <?php else: ?>
            <span style="color:var(--muted);font-size:0.82rem;">—</span>
            <?php endif; ?>
        </td>
        <?php endif; ?>
        <td>
            <?php if($used): ?>
            <span class="badge-blocked">Used</span>
            <?php else: ?>
            <span class="badge-active">Available</span>
            <?php endif; ?>
        </td>
        <?php if($is_admin): ?>
        <td>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this code?')">
                <input type="hidden" name="delete_id" value="<?=$r['id']?>">
                <button type="submit" class="btn-danger-rz" style="padding:6px 12px;font-size:0.78rem;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
</div>

<!-- Generate Modal -->
<?php if($is_admin): ?>
<div id="genModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:9000;align-items:center;justify-content:center;">
<div style="background:#0d0d1a;border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;width:100%;max-width:360px;margin:20px;">
    <div style="font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;margin-bottom:20px;">
        <i class="fa-solid fa-share-nodes" style="color:#a855f7;margin-right:8px;"></i>Generate Referral Code
    </div>
    <form method="POST">
        <div style="margin-bottom:20px;">
            <label class="rz-label">Assign To Role</label>
            <select name="assigned_to" class="rz-select">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" name="generate" class="btn-primary-rz" style="flex:1;justify-content:center;">
                <i class="fa-solid fa-bolt"></i> Generate
            </button>
            <button type="button" onclick="document.getElementById('genModal').style.display='none'" class="btn-outline-rz" style="flex:1;">Cancel</button>
        </div>
    </form>
</div>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>