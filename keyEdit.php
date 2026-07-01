<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit(); }
include('conn.php');

$current_user = $_SESSION['username'];
$page_title   = 'Edit License';

if (isset($_POST['update'])) {
    $id           = (int)$_POST['id'];
    $license_key  = mysqli_real_escape_string($conn, $_POST['license_key']);
    $expiry_date  = $_POST['expiry_date'];
    $status       = (int)$_POST['status'];
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $daemon       = (int)($_POST['daemon'] ?? 0);
    $hide_root    = (int)($_POST['hide_root'] ?? 0);
    $toggle_expiry= (int)($_POST['toggle_expiry'] ?? 0);
    mysqli_query($conn, "UPDATE licenses SET license_key='$license_key', expiry_date='$expiry_date', status=$status, package_name='$package_name', daemon=$daemon, hide_root=$hide_root, toggle_expiry=$toggle_expiry WHERE id=$id");
    header('Location: license_list.php'); exit();
}

$id = (int)($_GET['id'] ?? 0);
$result = mysqli_query($conn, "SELECT * FROM licenses WHERE id=$id");
if (!$result || mysqli_num_rows($result) === 0) { echo "<script>alert('License not found');history.back();</script>"; exit(); }
$row = mysqli_fetch_assoc($result);

include '_sidebar.php';
?>

<div style="max-width:560px;margin:0 auto;">
<div class="panel-card">
    <div class="section-heading"><i class="fa-solid fa-pen-to-square"></i> Edit License</div>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">

        <div style="margin-bottom:18px;">
            <label class="rz-label">License Key</label>
            <input type="text" name="license_key" class="rz-input" value="<?= htmlspecialchars($row['license_key']) ?>" required>
        </div>
        <div style="margin-bottom:18px;">
            <label class="rz-label">Package Name</label>
            <input type="text" name="package_name" class="rz-input" value="<?= htmlspecialchars($row['package_name'] ?? '') ?>" placeholder="com.example.app">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
            <div>
                <label class="rz-label">Expiry Date</label>
                <input type="date" name="expiry_date" class="rz-input" value="<?= $row['expiry_date'] ?>">
            </div>
            <div>
                <label class="rz-label">Status</label>
                <select name="status" class="rz-select">
                    <option value="1" <?= $row['status']==1?'selected':'' ?>>Active</option>
                    <option value="0" <?= $row['status']==0?'selected':'' ?>>Blocked</option>
                </select>
            </div>
        </div>

        <!-- Feature toggles -->
        <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:22px;">
            <div style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);margin-bottom:14px;">Feature Flags</div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                <?php foreach(['daemon'=>'Daemon','hide_root'=>'Hide Root','toggle_expiry'=>'Show Expiry'] as $field=>$label): ?>
                <label style="display:flex;flex-direction:column;align-items:center;gap:8px;background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:14px 10px;cursor:pointer;font-size:0.8rem;color:var(--muted);transition:border-color 0.2s;" 
                    onmouseover="this.style.borderColor='rgba(124,58,237,0.4)'" onmouseout="this.style.borderColor='var(--border)'">
                    <input type="checkbox" name="<?=$field?>" value="1" <?= ($row[$field]??0)?'checked':'' ?> style="accent-color:#7c3aed;width:18px;height:18px;">
                    <?=$label?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" name="update" class="btn-primary-rz" style="flex:1;justify-content:center;">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
            <a href="license_list.php" class="btn-outline-rz" style="flex:1;text-align:center;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-xmark"></i> Cancel
            </a>
        </div>
    </form>
</div>
</div>

<?php include '_footer.php'; ?>
