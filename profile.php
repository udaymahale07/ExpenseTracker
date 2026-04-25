<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit(); }

$user_id = $_SESSION['user_id'];
$user_data = [
    'email'      => $_SESSION['email'] ?? '',
    'first_name' => 'User',
    'last_name'  => '',
    'phone'      => 'Not set',
    'dob'        => 'Not set',
    'gender'     => 'Not set',
    'address'    => 'Not set',
];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        foreach (['email','phone','first_name','last_name','dob','gender','address'] as $k) {
            $user_data[$k] = !empty($row[$k]) ? $row[$k] : ($k === 'first_name' ? 'User' : 'Not set');
        }
    }
} catch (PDOException $e) { error_log("Profile fetch: " . $e->getMessage()); }

$display_name = trim($user_data['first_name'] . ' ' . ($user_data['last_name'] === 'Not set' ? '' : $user_data['last_name']));
if (!$display_name) $display_name = 'User';
$initials = strtoupper(substr($user_data['first_name'],0,1) . substr($user_data['last_name'],0,1));
if (!$initials) $initials = strtoupper(substr($user_data['email'],0,2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Expense Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="theme.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @keyframes etFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .page-wrapper, .main-content, .content-wrapper { animation: etFadeIn 0.22s ease both; }

        :root {
            --primary: #2b6777;
            --primary-light: #52ab98;
            --bg: #f0f4f8;
            --card-bg: #ffffff;
            --text: #2d3742;
            --text-light: #6b7a8d;
            --border: #e4eaf0;
        }
        [data-theme="dark"] {
            --bg: #0d1520;
            --card-bg: #19263a;
            --text: #dce8f0;
            --text-light: #7a93a8;
            --border: #243447;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        /* ── Dark Sidebar ── */
        .sidebar {
            width: 256px;
            background: linear-gradient(180deg, #1b3e48 0%, #132e36 100%);
            box-shadow: 2px 0 20px rgba(0,0,0,0.25);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 150;
            transition: transform 0.3s ease;
        }
        .sidebar-header { padding: 22px 18px; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .sidebar-header h1 { color:#fff; margin:0; font-size:1rem; font-weight:600; display:flex; align-items:center; justify-content:center; gap:10px; }
        .sidebar-logo { width:32px; height:32px; object-fit:contain; border-radius:6px; }
        .nav-links { padding:10px 0; display:flex; flex-direction:column; flex:1; overflow-y:auto; }
        .nav-link { padding:13px 22px; display:flex; align-items:center; gap:12px; color:rgba(255,255,255,0.62); text-decoration:none; font-weight:500; font-size:0.9rem; transition:all 0.2s; position:relative; }
        .nav-link:hover { background:rgba(82,171,152,0.14); color:rgba(255,255,255,0.92); }
        .nav-link.active { background:rgba(82,171,152,0.22); color:#fff; font-weight:600; }
        .nav-link.active::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:#52ab98; border-radius:0 2px 2px 0; }
        .logout-link { margin-top:auto; border-top:1px solid rgba(255,255,255,0.07); color:rgba(255,130,120,0.8) !important; }
        .logout-link:hover { background:rgba(229,57,53,0.14) !important; color:#ff8a80 !important; }

        /* Mobile */
        .mobile-topbar { display:none; position:fixed; top:0; left:0; right:0; height:58px; background:linear-gradient(90deg,#1b3e48,#2b6777); z-index:200; align-items:center; padding:0 18px; gap:12px; box-shadow:0 2px 10px rgba(0,0,0,0.25); }
        .hamburger-btn { background:none; border:none; color:white; cursor:pointer; padding:6px; display:flex; align-items:center; border-radius:6px; transition:background 0.2s; }
        .hamburger-btn:hover { background:rgba(255,255,255,0.12); }
        .mobile-logo { width:28px; height:28px; object-fit:contain; border-radius:5px; }
        .mobile-app-title { color:white; font-weight:600; font-size:0.95rem; }
        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.52); z-index:140; backdrop-filter:blur(2px); }
        @media (max-width:768px) {
            .mobile-topbar { display:flex; }
            .sidebar { transform:translateX(-100%); }
            .main-content { margin-left:0 !important; padding:80px 18px 30px !important; }
            body.sidebar-open .sidebar { transform:translateX(0); }
            body.sidebar-open .sidebar-overlay { display:block; }
        }

        /* ── Main ── */
        .main-content { flex:1; margin-left:256px; padding:36px 40px; display:flex; justify-content:center; align-items:flex-start; }

        /* ── Profile Card ── */
        .profile-wrap { width:100%; max-width:820px; margin-top: 10px; }

        .profile-hero {
            background: linear-gradient(135deg, #1b3e48, #2b6777);
            border-radius: 16px 16px 0 0;
            padding: 36px 36px 60px;
            position: relative;
            overflow: hidden;
        }
        .profile-hero::before {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -80px; right: -60px;
        }
        .profile-hero-content { display: flex; align-items: center; gap: 22px; position: relative; z-index: 1; }
        .avatar-circle {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: white;
            flex-shrink: 0;
        }
        .hero-name { color: white; }
        .hero-name h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .hero-name p { font-size: 0.88rem; opacity: 0.75; }
        .edit-btn {
            margin-left: auto;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex; align-items: center; gap: 7px;
            transition: background 0.2s;
        }
        .edit-btn:hover { background: rgba(255,255,255,0.25); }

        .profile-body {
            background: var(--card-bg);
            border-radius: 0 0 16px 16px;
            padding: 0 36px 36px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
            border-top: none;
            transition: background 0.3s;
        }
        [data-theme="dark"] .profile-body { box-shadow: 0 8px 30px rgba(0,0,0,0.3); }

        .section-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            color: var(--primary-light);
            padding: 28px 0 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
            gap: 14px;
        }
        .profile-detail {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: var(--bg);
            border-radius: 10px;
            border: 1px solid var(--border);
            transition: background 0.3s;
        }
        .detail-icon {
            width: 38px; height: 38px;
            background: var(--card-bg);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            flex-shrink: 0;
        }
        [data-theme="dark"] .detail-icon { box-shadow: none; background: rgba(255,255,255,0.06); }
        .detail-text label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: var(--text-light); }
        .detail-text p { margin: 4px 0 0; font-size: 0.95rem; font-weight: 500; color: var(--text); }
        .full-width { grid-column: 1 / -1; }

        @media (max-width: 600px) {
            .profile-hero { padding: 24px 20px 50px; }
            .profile-body { padding: 0 20px 24px; }
            .edit-btn span { display: none; }
            .hero-name h2 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>

    <!-- Mobile Topbar -->
    <div class="mobile-topbar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu"><i data-lucide="menu" width="24" height="24"></i></button>
        <img src="logo.png" alt="Logo" class="mobile-logo">
        <span class="mobile-app-title">Expense Tracker</span>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1><img src="logo.png" alt="Logo" class="sidebar-logo"><span>Expense Tracker</span></h1>
        </div>
        <nav class="nav-links">
            <a href="dailyexpense.php"   class="nav-link"><i data-lucide="layout-dashboard"></i> <span>Dashboard</span></a>
            <a href="profile.php"        class="nav-link active"><i data-lucide="user"></i>      <span>My Profile</span></a>
            <a href="profile_update.php" class="nav-link"><i data-lucide="edit"></i>             <span>Update Profile</span></a>
            <a href="expensehistory.html" class="nav-link"><i data-lucide="history"></i>         <span>Expense History</span></a>
            <a href="settings.php"       class="nav-link"><i data-lucide="settings"></i>         <span>Settings</span></a>
            <a href="logout.php"         class="nav-link logout-link"><i data-lucide="log-out"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <div class="profile-wrap">

            <!-- Hero -->
            <div class="profile-hero">
                <div class="profile-hero-content">
                    <div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="hero-name">
                        <h2><?php echo htmlspecialchars($display_name); ?></h2>
                        <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                    <a href="profile_update.php" class="edit-btn">
                        <i data-lucide="edit-3" width="15" height="15"></i>
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>

            <!-- Body -->
            <div class="profile-body">
                <div class="section-title">Personal Information</div>

                <div class="profile-grid">
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="user" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>First Name</label>
                            <p><?php echo htmlspecialchars($user_data['first_name']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="users" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Last Name</label>
                            <p><?php echo htmlspecialchars($user_data['last_name']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="mail" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Email Address</label>
                            <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="phone" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Phone Number</label>
                            <p><?php echo htmlspecialchars($user_data['phone']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="calendar" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Date of Birth</label>
                            <p><?php echo htmlspecialchars($user_data['dob']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail">
                        <div class="detail-icon"><i data-lucide="users-2" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Gender</label>
                            <p><?php echo htmlspecialchars($user_data['gender']); ?></p>
                        </div>
                    </div>
                    <div class="profile-detail full-width">
                        <div class="detail-icon"><i data-lucide="map-pin" width="18" height="18"></i></div>
                        <div class="detail-text">
                            <label>Home Address</label>
                            <p><?php echo htmlspecialchars($user_data['address']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        lucide.createIcons();
        document.getElementById('hamburgerBtn')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
    </script>
</body>
</html>