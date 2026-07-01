<?php
session_start();
include('conn.php');
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'] ?? 0;
$current_user = $_SESSION['username'];
$page_title = 'Settings';
$message = ''; $error = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (isset($_POST['change_username'])) {
    $new_u = trim($_POST['new_username']);
    if (strlen($new_u) < 3) { $error = 'Username must be at least 3 characters.'; }
    else {
        $chk = $conn->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $chk->bind_param("si",$new_u,$user_id); $chk->execute();
        if ($chk->get_result()->num_rows > 0) { $error = 'Username already taken.'; }
        else {
            $upd = $conn->prepare("UPDATE users SET username=? WHERE id=?");
            $upd->bind_param("si",$new_u,$user_id);
            if ($upd->execute()) { $_SESSION['username']=$new_u; $current_user=$new_u; $user['username']=$new_u; $message='Username updated!'; }
            else { $error='Update failed.'; }
        }
    }
}

if (isset($_POST['change_password'])) {
    $cur = $_POST['current_password'];
    $new = $_POST['new_password'];
    $con = $_POST['confirm_password'];
    if (!password_verify($cur,$user['password'])) { $error='Current password is incorrect.'; }
    elseif (strlen($new)<6) { $error='New password must be at least 6 characters.'; }
    elseif ($new!==$con) { $error='Passwords do not match.'; }
    else {
        $hashed=password_hash($new,PASSWORD_DEFAULT);
        $upd=$conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si",$hashed,$user_id);
        if ($upd->execute()) $message='Password updated!';
        else $error='Update failed.';
    }
}

// Server settings save
if (isset($_POST['save_server'])) {
    $mode = mysqli_real_escape_string($conn, $_POST['server_mode'] ?? 'online');
    $contact = mysqli_real_escape_string($conn, $_POST['owner_contact'] ?? '');
    $notif = json_encode([
        'enabled'  => isset($_POST['notif_enabled']) ? 1 : 0,
        'title'    => $_POST['notif_title'] ?? '',
        'message'  => $_POST['notif_message'] ?? '',
        'iconType' => $_POST['notif_icon'] ?? 'info',
    ]);
    $conn->query("INSERT INTO server_settings (setting_key,setting_value) VALUES ('server_mode','$mode') ON DUPLICATE KEY UPDATE setting_value='$mode'");
    $conn->query("INSERT INTO server_settings (setting_key,setting_value) VALUES ('owner_contact','$contact') ON DUPLICATE KEY UPDATE setting_value='$contact'");
    $conn->query("INSERT INTO server_settings (setting_key,setting_value) VALUES ('server_notification_json','".mysqli_real_escape_string($conn,$notif)."') ON DUPLICATE KEY UPDATE setting_value='".mysqli_real_escape_string($conn,$notif)."'");
    $message = 'Server settings saved!';
}

// Fetch server settings
$serverMode = 'online'; $ownerContact = '@RennZohh';
$notifData = ['enabled'=>0,'title'=>'','message'=>'','iconType'=>'info'];
$res = $conn->query("SELECT setting_key,setting_value FROM server_settings WHERE setting_key IN ('server_mode','owner_contact','server_notification_json')");
while($r=$res->fetch_assoc()){
    if($r['setting_key']==='server_mode') $serverMode=$r['setting_value'];
    if($r['setting_key']==='owner_contact') $ownerContact=$r['setting_value'];
    if($r['setting_key']==='server_notification_json') $notifData=json_decode($r['setting_value'],true)??$notifData;
}

include '_sidebar.php';
?>

<div class="section-heading"><i class="fa-solid fa-gear"></i> Settings</div>

<?php if($message): ?>
<div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:12px;padding:13px 18px;margin-bottom:24px;display:flex;align-items:center;gap:10px;color:#4ade80;font-size:0.875rem;font-weight:600;">
    <i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if($error): ?>
<div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:13px 18px;margin-bottom:24px;display:flex;align-items:center;gap:10px;color:#f87171;font-size:0.875rem;">
    <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:24px;">

<!-- Change Username -->
<div class="panel-card">
    <div class="section-heading" style="font-size:1rem;margin-bottom:18px;"><i class="fa-solid fa-user-pen"></i> Change Username</div>
    <form method="POST">
        <div style="margin-bottom:16px;">
            <label class="rz-label">Current Username</label>
            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:12px 16px;color:var(--muted);font-size:0.875rem;"><?= htmlspecialchars($user['username']) ?></div>
        </div>
        <div style="margin-bottom:18px;">
            <label class="rz-label">New Username</label>
            <input type="text" name="new_username" class="rz-input" placeholder="Enter new username" required minlength="3">
        </div>
        <button type="submit" name="change_username" class="btn-primary-rz" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-check"></i> Update Username
        </button>
    </form>
</div>

<!-- Change Password -->
<div class="panel-card">
    <div class="section-heading" style="font-size:1rem;margin-bottom:18px;"><i class="fa-solid fa-lock"></i> Change Password</div>
    <form method="POST">
        <div style="margin-bottom:16px;">
            <label class="rz-label">Current Password</label>
            <input type="password" name="current_password" class="rz-input" placeholder="Enter current password" required>
        </div>
        <div style="margin-bottom:16px;">
            <label class="rz-label">New Password</label>
            <input type="password" name="new_password" class="rz-input" placeholder="At least 6 characters" required>
        </div>
        <div style="margin-bottom:18px;">
            <label class="rz-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="rz-input" placeholder="Repeat new password" required>
        </div>
        <button type="submit" name="change_password" class="btn-primary-rz" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-key"></i> Update Password
        </button>
    </form>
</div>

<!-- Server Settings -->
<div class="panel-card" style="grid-column:1/-1;">
    <div class="section-heading" style="font-size:1rem;margin-bottom:18px;"><i class="fa-solid fa-server"></i> Server Settings</div>
    <form method="POST">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:16px;">
            <div>
                <label class="rz-label">Server Mode</label>
                <select name="server_mode" class="rz-select">
                    <option value="online" <?= $serverMode==='online'?'selected':'' ?>>🟢 Online</option>
                    <option value="maintenance" <?= $serverMode==='maintenance'?'selected':'' ?>>🟡 Maintenance</option>
                    <option value="offline" <?= $serverMode==='offline'?'selected':'' ?>>🔴 Offline</option>
                </select>
            </div>
            <div>
                <label class="rz-label">Owner Contact</label>
                <input type="text" name="owner_contact" class="rz-input" value="<?= htmlspecialchars($ownerContact) ?>" placeholder="@YourTelegram">
            </div>
        </div>
        <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                <span style="font-size:0.875rem;font-weight:600;"><i class="fa-solid fa-bell" style="color:#a855f7;margin-right:8px;"></i>Server Notification</span>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;color:var(--muted);">
                    <input type="checkbox" name="notif_enabled" <?= ($notifData['enabled']??0)?'checked':'' ?> style="accent-color:#7c3aed;width:16px;height:16px;"> Enable
                </label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div>
                    <label class="rz-label">Title</label>
                    <input type="text" name="notif_title" class="rz-input" value="<?= htmlspecialchars($notifData['title']??'') ?>" placeholder="Notification title">
                </div>
                <div>
                    <label class="rz-label">Icon Type</label>
                    <select name="notif_icon" class="rz-select">
                        <?php foreach(['info','warning','error','event','update'] as $ico): ?>
                        <option value="<?=$ico?>" <?= ($notifData['iconType']??'info')===$ico?'selected':'' ?>><?= ucfirst($ico) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="rz-label">Message</label>
                <textarea name="notif_message" class="rz-input" rows="3" placeholder="Notification message" style="resize:vertical;"><?= htmlspecialchars($notifData['message']??'') ?></textarea>
            </div>
        </div>
        <button type="submit" name="save_server" class="btn-primary-rz"><i class="fa-solid fa-floppy-disk"></i> Save Server Settings</button>
    </form>
</div>

</div>

<?php include '_footer.php'; ?>
