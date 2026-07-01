<?php
session_start();
include 'conn.php';
if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }
$current_user = $_SESSION['username'] ?? 'Admin';
$page_title = 'Generate License';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $package   = trim($_POST['package_name'] ?? '');
    $key_type  = $_POST['key_type'] ?? 'auto';
    $custom_key= trim($_POST['custom_key'] ?? '');

    if ($key_type==='custom' && !empty($_POST['custom_date'])) {
        $expiry = $_POST['custom_date'];
        $days   = 'custom';
    } else {
        $days   = intval($_POST['expiry_duration'] ?? 30);
        $expiry = date('Y-m-d', strtotime("+$days days"));
    }

    if ($package!=='' && $expiry!=='') {
        $license = ($key_type==='custom' && $custom_key!=='')
            ? strtoupper($custom_key)
            : strtoupper('REN-'.bin2hex(random_bytes(4)).'-'.rand(100,999));

        $stmt = $conn->prepare("INSERT INTO licenses (license_key,expiry_date,status,package_name) VALUES (?,?,1,?)");
        $stmt->bind_param("sss",$license,$expiry,$package);
        $stmt->execute();
        $_SESSION['success_license'] = compact('package','license','expiry','days','key_type');
        header("Location: generate_ui.php"); exit();
    }
}
include '_sidebar.php';
?>

<div style="max-width:520px;margin:0 auto;">
<div class="panel-card">
    <div class="section-heading"><i class="fa-solid fa-key"></i> Generate License</div>

    <?php if(isset($_SESSION['success_license'])): $s=$_SESSION['success_license']; unset($_SESSION['success_license']); ?>
    <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:14px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <i class="fa-solid fa-circle-check" style="color:#4ade80;font-size:1.4rem;"></i>
            <span style="font-weight:700;font-size:1rem;">License Generated!</span>
        </div>
        <?php foreach(['package'=>'Package','license'=>'License Key','expiry'=>'Expires','days'=>'Duration','key_type'=>'Type'] as $k=>$label): ?>
        <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:0.875rem;">
            <span style="color:#94a3b8;"><?= $label ?></span>
            <span style="font-weight:600;" id="copy_<?=$k?>"><?= htmlspecialchars($s[$k]) ?></span>
        </div>
        <?php endforeach; ?>
        <button onclick="copyText(document.getElementById('copy_license').innerText)" class="btn-primary-rz" style="width:100%;justify-content:center;margin-top:16px;">
            <i class="fa-regular fa-copy"></i> Copy License Key
        </button>
    </div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom:18px;">
            <label class="rz-label">Package Name</label>
            <input name="package_name" class="rz-input" placeholder="com.example.app" required>
        </div>

        <!-- Key Type Toggle -->
        <div style="margin-bottom:18px;">
            <label class="rz-label">Key Type</label>
            <div style="display:flex;gap:8px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:14px;padding:6px;">
                <button type="button" id="btn-auto" onclick="setKeyType('auto')"
                    style="flex:1;padding:10px;border-radius:10px;border:none;background:linear-gradient(135deg,#7c3aed,#a855f7);color:white;font-weight:600;font-size:0.875rem;cursor:pointer;transition:all 0.2s;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Auto
                </button>
                <button type="button" id="btn-custom" onclick="setKeyType('custom')"
                    style="flex:1;padding:10px;border-radius:10px;border:none;background:transparent;color:#94a3b8;font-weight:600;font-size:0.875rem;cursor:pointer;transition:all 0.2s;">
                    <i class="fa-solid fa-pen"></i> Custom
                </button>
            </div>
        </div>

        <input type="hidden" name="key_type" id="keyType" value="auto">

        <!-- Auto preview -->
        <div id="auto-section">
            <div style="background:rgba(124,58,237,0.08);border:1px dashed rgba(124,58,237,0.3);border-radius:12px;padding:14px;text-align:center;margin-bottom:18px;">
                <div style="font-family:monospace;font-size:1.1rem;color:#a855f7;font-weight:700;" id="preview-key">REN-XXXX-XXX</div>
                <div style="font-size:0.75rem;color:#64748b;margin-top:4px;">Format: REN-XXXXXXXX-000</div>
            </div>
            <div style="margin-bottom:18px;">
                <label class="rz-label">Expiry Duration</label>
                <select name="expiry_duration" id="expiryDuration" class="rz-select" onchange="updateExpiry()">
                    <option value="1">1 Day</option>
                    <option value="3">3 Days</option>
                    <option value="7">7 Days</option>
                    <option value="30" selected>30 Days</option>
                    <option value="60">60 Days</option>
                    <option value="90">90 Days</option>
                    <option value="180">6 Months</option>
                    <option value="365">1 Year</option>
                </select>
                <div style="margin-top:8px;font-size:0.8rem;color:#64748b;">
                    <i class="fa-regular fa-calendar" style="color:#a855f7;"></i>
                    Expires: <span id="expiry-preview" style="color:#a855f7;font-weight:600;"></span>
                </div>
            </div>
        </div>

        <!-- Custom section -->
        <div id="custom-section" style="display:none;">
            <div style="margin-bottom:18px;">
                <label class="rz-label">Custom Key</label>
                <input type="text" name="custom_key" class="rz-input" placeholder="YOUR-CUSTOM-KEY">
            </div>
            <div style="margin-bottom:18px;">
                <label class="rz-label">Expiry Date</label>
                <input type="date" name="custom_date" class="rz-input" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d',strtotime('+30 days')) ?>">
            </div>
        </div>

        <button type="submit" name="generate" class="btn-primary-rz" style="width:100%;justify-content:center;padding:14px;">
            <i class="fa-solid fa-bolt"></i> Generate License
        </button>
    </form>
</div>
</div>

<script>
function setKeyType(t){
    document.getElementById('keyType').value=t;
    const isAuto=t==='auto';
    document.getElementById('auto-section').style.display=isAuto?'block':'none';
    document.getElementById('custom-section').style.display=isAuto?'none':'block';
    document.getElementById('btn-auto').style.background=isAuto?'linear-gradient(135deg,#7c3aed,#a855f7)':'transparent';
    document.getElementById('btn-auto').style.color=isAuto?'white':'#94a3b8';
    document.getElementById('btn-custom').style.background=!isAuto?'linear-gradient(135deg,#7c3aed,#a855f7)':'transparent';
    document.getElementById('btn-custom').style.color=!isAuto?'white':'#94a3b8';
}
function updateExpiry(){
    const d=parseInt(document.getElementById('expiryDuration').value);
    const dt=new Date(); dt.setDate(dt.getDate()+d);
    document.getElementById('expiry-preview').textContent=dt.toISOString().split('T')[0];
}
function randKey(){
    const hex=()=>Math.floor(Math.random()*16).toString(16).toUpperCase();
    return 'REN-'+Array(8).fill(0).map(()=>hex()).join('')+'-'+Math.floor(100+Math.random()*900);
}
setInterval(()=>document.getElementById('preview-key').textContent=randKey(),2000);
window.onload=()=>{updateExpiry();document.getElementById('preview-key').textContent=randKey();};
</script>

<?php include '_footer.php'; ?>
