<?php
session_start();
include('conn.php');
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit(); }

$current_user    = $_SESSION['username'];
$current_user_id = $_SESSION['user_id'] ?? 0;
$page_title      = 'Manage Users';

$uq = $conn->prepare("SELECT * FROM users WHERE id = ?");
$uq->bind_param("i", $current_user_id);
$uq->execute();
$current_user_data = $uq->get_result()->fetch_assoc();
$is_owner = ($current_user_data['role'] ?? '') === 'owner';

// Delete
if (isset($_GET['delete']) && $is_owner) {
    $del = (int)$_GET['delete'];
    if ($del !== $current_user_id) {
        $conn->query("DELETE FROM users WHERE id=$del");
    }
    header("Location: manage_users.php"); exit();
}

// Edit
if (isset($_POST['edit_user']) && $is_owner) {
    $eid    = (int)$_POST['user_id'];
    $status = (int)$_POST['status'];
    $role   = mysqli_real_escape_string($conn, $_POST['role']);
    $conn->query("UPDATE users SET role='$role', status=$status WHERE id=$eid");
    header("Location: manage_users.php"); exit();
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
include '_sidebar.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div class="section-heading" style="margin:0;"><i class="fa-solid fa-users"></i> Manage Users</div>
    <?php if($is_owner): ?>
    <span style="background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.35);color:#a855f7;padding:5px 14px;border-radius:999px;font-size:0.75rem;font-weight:700;letter-spacing:0.5px;">
        <i class="fa-solid fa-crown"></i> Owner
    </span>
    <?php endif; ?>
</div>

<div class="panel-card" style="padding:0;overflow:hidden;">
<div style="overflow-x:auto;">
<table class="rz-table">
    <thead><tr>
        <th>#</th><th>Username</th><th>Role</th><th>Status</th><th>Registered</th>
        <?php if($is_owner): ?><th>Actions</th><?php endif; ?>
    </tr></thead>
    <tbody>
    <?php $i=0; while($u = mysqli_fetch_assoc($users)): $i++;
        $isSelf = ($u['id'] == $current_user_id);
        $statusActive = ($u['status'] ?? 1) == 1;
    ?>
    <tr>
        <td style="color:var(--muted);font-size:0.8rem;"><?= $i ?></td>
        <td>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">
                    <?= strtoupper(substr($u['username'],0,1)) ?>
                </div>
                <span style="font-weight:600;"><?= htmlspecialchars($u['username']) ?></span>
                <?php if($isSelf): ?><span style="font-size:0.7rem;color:#a855f7;background:rgba(124,58,237,0.15);padding:2px 8px;border-radius:999px;">You</span><?php endif; ?>
            </div>
        </td>
        <td>
            <?php if($u['role']==='owner'): ?>
            <span style="color:#f59e0b;font-size:0.8rem;font-weight:600;"><i class="fa-solid fa-crown"></i> Owner</span>
            <?php else: ?>
            <span style="color:var(--muted);font-size:0.8rem;"><i class="fa-solid fa-user"></i> <?= ucfirst($u['role']??'user') ?></span>
            <?php endif; ?>
        </td>
        <td><?= $statusActive ? '<span class="badge-active">Active</span>' : '<span class="badge-blocked">Inactive</span>' ?></td>
        <td style="color:var(--muted);font-size:0.8rem;"><?= $u['created_at'] ?? '—' ?></td>
        <?php if($is_owner): ?>
        <td>
            <?php if(!$isSelf): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button onclick="openEdit(<?= $u['id'] ?>,'<?= htmlspecialchars($u['username']) ?>','<?= $u['role']??'user' ?>',<?= $u['status']??1 ?>)"
                    class="btn-outline-rz" style="padding:6px 12px;font-size:0.78rem;"><i class="fa-solid fa-pen"></i></button>
                <a href="manage_users.php?delete=<?= $u['id'] ?>" class="btn-danger-rz" style="padding:6px 12px;font-size:0.78rem;"
                    onclick="return confirm('Delete this user?')"><i class="fa-solid fa-trash"></i></a>
            </div>
            <?php else: ?>
            <span style="color:var(--muted);font-size:0.78rem;">—</span>
            <?php endif; ?>
        </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
</div>

<!-- Edit Modal -->
<?php if($is_owner): ?>
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);z-index:9000;align-items:center;justify-content:center;">
<div style="background:#0d0d1a;border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:32px;width:100%;max-width:380px;margin:20px;">
    <div style="font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-pen" style="color:#a855f7;"></i> Edit User: <span id="editName"></span>
    </div>
    <form method="POST">
        <input type="hidden" name="user_id" id="editUserId">
        <div style="margin-bottom:16px;">
            <label class="rz-label">Role</label>
            <select name="role" id="editRole" class="rz-select">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
            </select>
        </div>
        <div style="margin-bottom:22px;">
            <label class="rz-label">Status</label>
            <select name="status" id="editStatus" class="rz-select">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" name="edit_user" class="btn-primary-rz" style="flex:1;justify-content:center;"><i class="fa-solid fa-check"></i> Save</button>
            <button type="button" onclick="closeEdit()" class="btn-outline-rz" style="flex:1;">Cancel</button>
        </div>
    </form>
</div>
</div>
<script>
function openEdit(id,name,role,status){
    document.getElementById('editModal').style.display='flex';
    document.getElementById('editUserId').value=id;
    document.getElementById('editName').textContent=name;
    document.getElementById('editRole').value=role;
    document.getElementById('editStatus').value=status;
}
function closeEdit(){document.getElementById('editModal').style.display='none';}
</script>
<?php endif; ?>

<?php include '_footer.php'; ?>
