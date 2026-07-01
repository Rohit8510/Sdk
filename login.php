<?php
session_start();
include('conn.php');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $valid = password_verify($password, $row['password'])
                  || (sha1($password) === $row['password'])
                  || ($password === $row['password']);
            if ($valid) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username']  = $row['username'];
                $_SESSION['user_id']   = $row['id'];
                header("Location: dashboard.php");
                exit();
            }
        }
        $error = 'Invalid username or password.';
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In — RennZohh SDK</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<style>
:root{
  --primary:#7c3aed;--primary-light:#a855f7;
  --accent:#06b6d4;--bg:#07070f;
  --border:rgba(255,255,255,0.08);--text:#f1f5f9;--muted:#64748b;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.blob{position:fixed;border-radius:50%;filter:blur(100px);opacity:0.13;pointer-events:none;}
.b1{width:600px;height:600px;background:#7c3aed;top:-200px;left:-200px;animation:bd1 22s ease-in-out infinite alternate;}
.b2{width:500px;height:500px;background:#06b6d4;bottom:-150px;right:-150px;animation:bd2 18s ease-in-out infinite alternate;}
@keyframes bd1{to{transform:translate(80px,60px)}}
@keyframes bd2{to{transform:translate(-60px,-80px)}}
#particles-js{position:fixed;inset:0;z-index:1;pointer-events:none;}

.auth-wrap{position:relative;z-index:10;width:100%;max-width:420px;padding:20px;}
.auth-card{
  background:rgba(255,255,255,0.04);
  border:1px solid var(--border);
  border-radius:24px;
  padding:40px 36px;
  backdrop-filter:blur(24px);
  box-shadow:0 40px 80px rgba(0,0,0,0.5);
}
.logo-ring{
  width:72px;height:72px;border-radius:50%;
  background:conic-gradient(from 0deg,#7c3aed,#06b6d4,#f59e0b,#a855f7,#7c3aed);
  padding:3px;animation:spin 4s linear infinite;margin:0 auto 20px;
}
@keyframes spin{to{transform:rotate(360deg)}}
.logo-inner{width:100%;height:100%;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;overflow:hidden;}
.logo-inner img{width:66px;height:66px;border-radius:50%;object-fit:cover;}
.auth-title{font-family:'Space Grotesk',sans-serif;font-size:1.75rem;font-weight:800;text-align:center;margin-bottom:4px;
  background:linear-gradient(135deg,#f1f5f9 40%,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.auth-sub{text-align:center;color:var(--muted);font-size:0.875rem;margin-bottom:28px;}
.field{margin-bottom:18px;}
.field label{display:block;font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:var(--muted);margin-bottom:8px;}
.input-wrap{position:relative;}
.input-wrap i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.9rem;}
.input-wrap input{
  width:100%;background:rgba(255,255,255,0.05);border:1px solid var(--border);
  border-radius:12px;padding:13px 44px;color:var(--text);font-size:0.9rem;
  font-family:'Inter',sans-serif;outline:none;transition:all 0.2s;
}
.input-wrap input:focus{border-color:var(--primary-light);box-shadow:0 0 0 3px rgba(124,58,237,0.2);}
.input-wrap input::placeholder{color:var(--muted);}
.eye-btn{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:0.9rem;padding:4px;}
.eye-btn:hover{color:var(--text);}
.err-box{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:11px 16px;font-size:0.85rem;color:#f87171;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
.btn-login{
  width:100%;background:linear-gradient(135deg,var(--primary),var(--primary-light));
  border:none;border-radius:12px;padding:14px;
  color:white;font-size:0.95rem;font-weight:700;
  font-family:'Space Grotesk',sans-serif;
  cursor:pointer;transition:all 0.2s;margin-top:4px;
}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(124,58,237,0.45);}
.auth-foot{text-align:center;margin-top:22px;font-size:0.85rem;color:var(--muted);}
.auth-foot a{color:var(--primary-light);text-decoration:none;font-weight:600;}
.auth-foot a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="blob b1"></div>
<div class="blob b2"></div>
<div id="particles-js"></div>

<div class="auth-wrap">
  <div class="auth-card">
    <div class="logo-ring"><div class="logo-inner"><img src="https://i.ibb.co/jvfbV6dw/4d9ba51ddd6e842980652b102e7d475c.jpg" alt="Logo"></div></div>
    <div class="auth-title">Welcome Back</div>
    <div class="auth-sub">Sign in to RennZohh SDK Panel</div>

    <?php if($error): ?>
    <div class="err-box"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <i class="fa-solid fa-user"></i>
          <input type="text" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username']??'') ?>" required autocomplete="username">
        </div>
      </div>
      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" id="pw" placeholder="Enter password" required autocomplete="current-password">
          <button type="button" class="eye-btn" onclick="togglePw()"><i class="fa-regular fa-eye" id="eyeIcon"></i></button>
        </div>
      </div>
      <button type="submit" class="btn-login"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In</button>
    </form>
    <div class="auth-foot">Don't have an account? <a href="register.php">Register here</a></div>
  </div>
</div>

<script>
function togglePw(){
  const p=document.getElementById('pw'),i=document.getElementById('eyeIcon');
  if(p.type==='password'){p.type='text';i.className='fa-regular fa-eye-slash';}
  else{p.type='password';i.className='fa-regular fa-eye';}
}
particlesJS('particles-js',{
  particles:{number:{value:50,density:{enable:true,value_area:900}},
  color:{value:['#7c3aed','#06b6d4','#a855f7']},shape:{type:'circle'},
  opacity:{value:0.2,random:true},size:{value:2,random:true},
  line_linked:{enable:true,distance:130,color:'#7c3aed',opacity:0.07},
  move:{enable:true,speed:0.8}},
  interactivity:{events:{onhover:{enable:true,mode:'grab'}},
  modes:{grab:{distance:140,line_linked:{opacity:0.15}}}}
});
</script>
</body>
</html>
