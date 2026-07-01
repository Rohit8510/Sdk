<?php
// Shared sidebar + header component
// Usage: include '_sidebar.php'; — pass $current_user and $page_title before including
$page_title = $page_title ?? 'RennZohh SDK Panel';
$current_user = $current_user ?? ($_SESSION['username'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title) ?> — RennZohh SDK Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<style>
:root{
    --primary:#7c3aed;--primary-light:#a855f7;
    --accent:#06b6d4;--amber:#f59e0b;--green:#22c55e;
    --bg:#07070f;--surface:rgba(255,255,255,0.04);
    --border:rgba(255,255,255,0.08);--text:#f1f5f9;--muted:#64748b;
    --sidebar-w:256px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}

/* Blobs */
.blob{position:fixed;border-radius:50%;filter:blur(100px);opacity:0.12;pointer-events:none;z-index:0;}
.b1{width:600px;height:600px;background:#7c3aed;top:-200px;left:-200px;animation:bd1 22s ease-in-out infinite alternate;}
.b2{width:500px;height:500px;background:#06b6d4;bottom:-150px;right:-150px;animation:bd2 18s ease-in-out infinite alternate;}
@keyframes bd1{to{transform:translate(80px,60px)}}
@keyframes bd2{to{transform:translate(-60px,-80px)}}
#particles-js{position:fixed;inset:0;z-index:1;pointer-events:none;}

/* Sidebar */
#sidebar{
    position:fixed;top:0;left:0;height:100%;width:var(--sidebar-w);
    background:rgba(7,7,15,0.9);
    border-right:1px solid var(--border);
    backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
    z-index:1100;
    display:flex;flex-direction:column;
    transform:translateX(-100%);
    transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);
}
#sidebar.open{transform:translateX(0);}

/* Desktop: always show */
@media(min-width:1024px){
    #sidebar{transform:translateX(0);}
    #overlay{display:none!important;}
    .main-wrap{margin-left:var(--sidebar-w);}
}

.sidebar-brand{
    padding:24px 20px 16px;
    display:flex;align-items:center;gap:14px;
    border-bottom:1px solid var(--border);
}
.brand-ring{
    width:46px;height:46px;border-radius:50%;padding:2px;flex-shrink:0;
    background:conic-gradient(from 0deg,#7c3aed,#06b6d4,#f59e0b,#a855f7,#7c3aed);
    animation:spin 4s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
.brand-ring-inner{
    width:100%;height:100%;border-radius:50%;background:var(--bg);
    display:flex;align-items:center;justify-content:center;overflow:hidden;
}
.brand-ring-inner img{width:42px;height:42px;border-radius:50%;object-fit:cover;}
.brand-name{font-family:'Space Grotesk',sans-serif;font-size:0.9rem;font-weight:700;
    background:linear-gradient(135deg,#a855f7,#06b6d4);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
    line-height:1.2;}
.brand-user{font-size:0.72rem;color:var(--muted);margin-top:2px;}

.sidebar-nav{flex:1;padding:12px 10px;overflow-y:auto;}
.nav-section{font-size:0.65rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;
    color:var(--muted);padding:14px 10px 6px;display:block;}
.nav-link{
    display:flex;align-items:center;gap:12px;
    padding:11px 14px;border-radius:12px;
    color:rgba(255,255,255,0.65);font-size:0.875rem;font-weight:500;
    text-decoration:none;transition:all 0.2s;margin-bottom:2px;
    border:1px solid transparent;
}
.nav-link i{width:18px;text-align:center;font-size:0.85rem;color:var(--muted);transition:color 0.2s;}
.nav-link:hover{background:rgba(124,58,237,0.12);color:var(--text);border-color:rgba(124,58,237,0.25);}
.nav-link:hover i{color:var(--primary-light);}
.nav-link.active{background:rgba(124,58,237,0.18);color:var(--text);border-color:rgba(124,58,237,0.35);}
.nav-link.active i{color:var(--primary-light);}
.nav-link.danger:hover{background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);color:#f87171;}
.nav-link.danger:hover i{color:#f87171;}

.sidebar-footer{padding:14px 10px;border-top:1px solid var(--border);}
.user-pill{
    display:flex;align-items:center;gap:10px;padding:10px 12px;
    border-radius:12px;background:var(--surface);border:1px solid var(--border);
}
.user-avatar{
    width:34px;height:34px;border-radius:50%;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    display:flex;align-items:center;justify-content:center;font-size:0.85rem;
    color:white;font-weight:700;flex-shrink:0;
}
.user-info .name{font-size:0.85rem;font-weight:600;}
.user-info .role{font-size:0.72rem;color:var(--muted);}

/* Header */
#topbar{
    position:fixed;top:0;right:0;left:0;height:58px;z-index:1000;
    background:rgba(7,7,15,0.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
    border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
    padding:0 20px 0 16px;
}
@media(min-width:1024px){#topbar{left:var(--sidebar-w);}}
.topbar-left{display:flex;align-items:center;gap:14px;}
.menu-toggle{
    background:none;border:none;color:var(--muted);font-size:1.1rem;
    cursor:pointer;padding:6px;border-radius:8px;transition:all 0.2s;
}
.menu-toggle:hover{background:var(--surface);color:var(--text);}
@media(min-width:1024px){.menu-toggle{display:none;}}
.page-title{font-family:'Space Grotesk',sans-serif;font-size:0.95rem;font-weight:700;color:var(--text);}
.topbar-right{display:flex;align-items:center;gap:10px;}
.topbar-badge{
    display:flex;align-items:center;gap:8px;
    padding:7px 14px;border-radius:999px;
    background:var(--surface);border:1px solid var(--border);
    font-size:0.82rem;font-weight:600;
}
.topbar-badge i{color:var(--primary-light);}

/* Overlay */
#overlay{
    position:fixed;inset:0;background:rgba(0,0,0,0.5);
    backdrop-filter:blur(4px);z-index:1050;
    opacity:0;pointer-events:none;transition:opacity 0.3s;
}
#overlay.show{opacity:1;pointer-events:auto;}

/* Main */
.main-wrap{
    padding-top:58px;min-height:100vh;
    position:relative;z-index:10;
}
.page-content{padding:28px 24px;max-width:1300px;margin:0 auto;}
@media(max-width:600px){.page-content{padding:20px 14px;}}

/* Cards */
.panel-card{
    background:rgba(255,255,255,0.03);
    border:1px solid var(--border);border-radius:20px;
    padding:24px;
}

/* Stat cards */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;margin-bottom:28px;}
.stat-card{
    background:rgba(255,255,255,0.04);border:1px solid var(--border);
    border-radius:16px;padding:20px 18px;transition:all 0.2s;
}
.stat-card:hover{border-color:rgba(124,58,237,0.4);transform:translateY(-3px);}
.stat-label{font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;}
.stat-value{font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;line-height:1;}
.stat-value.purple{color:var(--primary-light);}
.stat-value.green{color:#4ade80;}
.stat-value.red{color:#f87171;}
.stat-value.cyan{color:var(--accent);}
.stat-value.amber{color:var(--amber);}
.stat-value.white{color:var(--text);}

/* Buttons */
.btn-primary-rz{
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border:none;border-radius:12px;padding:11px 22px;
    color:white;font-size:0.875rem;font-weight:600;
    cursor:pointer;transition:all 0.2s;text-decoration:none;display:inline-flex;align-items:center;gap:8px;
}
.btn-primary-rz:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(124,58,237,0.4);color:white;}
.btn-danger-rz{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:8px 16px;color:#f87171;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;}
.btn-danger-rz:hover{background:rgba(239,68,68,0.25);}
.btn-outline-rz{background:transparent;border:1px solid var(--border);border-radius:10px;padding:8px 16px;color:var(--text);font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.2s;text-decoration:none;}
.btn-outline-rz:hover{border-color:var(--primary-light);background:rgba(124,58,237,0.1);}

/* Table */
.rz-table{width:100%;border-collapse:collapse;}
.rz-table th{font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);padding:10px 14px;border-bottom:1px solid var(--border);text-align:left;font-weight:600;}
.rz-table td{padding:13px 14px;border-bottom:1px solid rgba(255,255,255,0.04);font-size:0.875rem;vertical-align:middle;}
.rz-table tr:hover td{background:rgba(255,255,255,0.02);}

/* Badges */
.badge-active{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.25);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;}
.badge-blocked{background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.25);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;}
.badge-cyan{background:rgba(6,182,212,0.15);color:#22d3ee;border:1px solid rgba(6,182,212,0.25);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:600;}

/* Form elements */
.rz-input,.rz-select{
    width:100%;background:rgba(255,255,255,0.05);
    border:1px solid var(--border);border-radius:12px;
    padding:12px 16px;color:var(--text);font-size:0.9rem;
    font-family:'Inter',sans-serif;outline:none;transition:all 0.2s;
}
.rz-input:focus,.rz-select:focus{border-color:var(--primary-light);box-shadow:0 0 0 3px rgba(124,58,237,0.2);}
.rz-input::placeholder{color:var(--muted);}
.rz-select option{background:#0d0d1a;}
.rz-label{font-size:0.8rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;display:block;}

/* Section heading */
.section-heading{font-family:'Space Grotesk',sans-serif;font-size:1.25rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
.section-heading i{color:var(--primary-light);}

/* Key mono */
.key-mono{font-family:monospace;font-size:0.9rem;color:var(--amber);background:rgba(245,158,11,0.08);padding:3px 8px;border-radius:6px;}

/* Scrollbar */
::-webkit-scrollbar{width:6px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:rgba(124,58,237,0.4);border-radius:999px;}

/* Toast */
#rz-toast{
    position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(20px);
    background:linear-gradient(135deg,#059669,#10b981);padding:11px 24px;border-radius:999px;
    color:white;font-size:0.875rem;font-weight:600;opacity:0;transition:all 0.3s;z-index:9999;
}
#rz-toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
</style>
</head>
<body>
<div class="blob b1"></div>
<div class="blob b2"></div>
<div id="particles-js"></div>
<div id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-ring">
            <div class="brand-ring-inner">
                <img src="https://i.ibb.co/jvfbV6dw/4d9ba51ddd6e842980652b102e7d475c.jpg" alt="Logo">
            </div>
        </div>
        <div>
            <div class="brand-name">RennZohh SDK</div>
            <div class="brand-user">Panel v2.0</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section">Main</span>
        <a href="dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='dashboard.php')?'active':'' ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a href="generate_ui.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='generate_ui.php')?'active':'' ?>">
            <i class="fa-solid fa-key"></i> Generate License
        </a>
        <a href="license_list.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='license_list.php')?'active':'' ?>">
            <i class="fa-solid fa-list-check"></i> License List
        </a>

        <span class="nav-section">Management</span>
        <a href="manage_users.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='manage_users.php')?'active':'' ?>">
            <i class="fa-solid fa-users"></i> Users
        </a>
        <a href="manage_referrals.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='manage_referrals.php')?'active':'' ?>">
            <i class="fa-solid fa-share-nodes"></i> Referrals
        </a>
        <a href="manage_packages.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='manage_packages.php')?'active':'' ?>">
            <i class="fa-solid fa-box"></i> Packages
        </a>

        <span class="nav-section">System</span>
        <a href="online_server.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='online_server.php')?'active':'' ?>">
            <i class="fa-solid fa-server"></i> SDK Server
        </a>
        <a href="settings.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])=='settings.php')?'active':'' ?>">
            <i class="fa-solid fa-gear"></i> Settings
        </a>
        <a href="logout.php" class="nav-link danger">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-pill">
            <div class="user-avatar"><?= strtoupper(substr($current_user,0,1)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($current_user) ?></div>
                <div class="role">Administrator</div>
            </div>
        </div>
    </div>
</aside>

<!-- TOPBAR -->
<div id="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
        <span class="page-title"><?= htmlspecialchars($page_title) ?></span>
    </div>
    <div class="topbar-right">
        <div class="topbar-badge">
            <i class="fa-solid fa-user-shield"></i>
            <?= htmlspecialchars($current_user) ?>
        </div>
    </div>
</div>

<div class="main-wrap">
<div class="page-content">
<div id="rz-toast"></div>
