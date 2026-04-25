<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit(); }

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

$user_data = ['email'=>$_SESSION['email']??'','phone'=>'','first_name'=>'','last_name'=>'','dob'=>'','gender'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['phone','first_name','last_name','dob','gender','address'];
    $new = [];
    foreach ($fields as $f) $new[$f] = htmlspecialchars(trim($_POST[$f] ?? ''));

    if (!empty($new['phone']) && !preg_match('/^[0-9]{10}$/', $new['phone'])) {
        $message   = 'Phone number must be exactly 10 digits.';
        $msg_type  = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET phone=:phone, first_name=:first_name, last_name=:last_name, dob=:dob, gender=:gender, address=:address WHERE id=:id");
            $stmt->execute(array_merge($new, ['id' => $user_id]));
            $message  = 'Profile updated successfully!';
            $msg_type = 'success';
        } catch (PDOException $e) {
            $message  = 'Error updating profile. Please try again.';
            $msg_type = 'error';
            error_log($e->getMessage());
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT email, phone, first_name, last_name, dob, gender, address FROM users WHERE id=:id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) foreach ($user_data as $k => $_) $user_data[$k] = $row[$k] ?? '';
} catch (PDOException $e) { error_log($e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile — Expense Tracker</title>
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
            --input-bg: #f8fafc;
            --danger: #e53935;
        }
        [data-theme="dark"] {
            --bg: #0d1520;
            --card-bg: #19263a;
            --text: #dce8f0;
            --text-light: #7a93a8;
            --border: #243447;
            --input-bg: #1a2d41;
        }
        *, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--text); display:flex; min-height:100vh; transition:background 0.3s,color 0.3s; }

        .sidebar { width:256px; background:linear-gradient(180deg,#1b3e48 0%,#132e36 100%); box-shadow:2px 0 20px rgba(0,0,0,0.25); display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:150; transition:transform 0.3s ease; }
        .sidebar-header { padding:22px 18px; border-bottom:1px solid rgba(255,255,255,0.07); }
        .sidebar-header h1 { color:#fff; margin:0; font-size:1rem; font-weight:600; display:flex; align-items:center; justify-content:center; gap:10px; }
        .sidebar-logo { width:32px; height:32px; object-fit:contain; border-radius:6px; }
        .nav-links { padding:10px 0; display:flex; flex-direction:column; flex:1; overflow-y:auto; }
        .nav-link { padding:13px 22px; display:flex; align-items:center; gap:12px; color:rgba(255,255,255,0.62); text-decoration:none; font-weight:500; font-size:0.9rem; transition:all 0.2s; position:relative; }
        .nav-link:hover { background:rgba(82,171,152,0.14); color:rgba(255,255,255,0.92); }
        .nav-link.active { background:rgba(82,171,152,0.22); color:#fff; font-weight:600; }
        .nav-link.active::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:#52ab98; border-radius:0 2px 2px 0; }
        .logout-link { margin-top:auto; border-top:1px solid rgba(255,255,255,0.07); color:rgba(255,130,120,0.8) !important; }
        .logout-link:hover { background:rgba(229,57,53,0.14) !important; color:#ff8a80 !important; }

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

        .main-content { flex:1; margin-left:256px; padding:36px 40px; display:flex; flex-direction:column; align-items:center; }

        /* ── Alert Banner ── */
        .alert-banner {
            width: 100%;
            max-width: 720px;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .alert-success { background:#d1e7dd; border:1px solid #badbcc; color:#0f5132; }
        .alert-error   { background:#f8d7da; border:1px solid #f5c2c7; color:#842029; }
        [data-theme="dark"] .alert-success { background:rgba(82,171,152,0.15); border-color:rgba(82,171,152,0.3); color:#52ab98; }
        [data-theme="dark"] .alert-error   { background:rgba(220,53,69,0.12);  border-color:rgba(220,53,69,0.3);  color:#ff8a9a; }

        /* ── Form Card ── */
        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
            width: 100%;
            max-width: 720px;
            overflow: hidden;
            transition: background 0.3s;
        }
        [data-theme="dark"] .form-card { box-shadow: 0 4px 20px rgba(0,0,0,0.3); }

        .form-card-header {
            background: linear-gradient(135deg, #1b3e48, #2b6777);
            padding: 28px 36px;
            color: white;
        }
        .form-card-header h2 { font-size: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .form-card-header p { opacity: 0.75; font-size: 0.85rem; margin-top: 4px; }

        .form-body { padding: 32px 36px; }

        .readonly-field {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 12px 14px;
            margin-bottom: 24px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        label { display:block; font-size:0.82rem; font-weight:500; color:var(--text-light); margin-bottom:6px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 22px; }
        .full-width { grid-column: 1 / -1; }
        .form-group { display:flex; flex-direction:column; }
        input, select, textarea {
            width:100%; padding:11px 14px; border:1.5px solid var(--border);
            border-radius:9px; font-family:'Poppins',sans-serif; font-size:0.95rem;
            background:var(--input-bg); color:var(--text); transition:all 0.25s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline:none; border-color:var(--primary-light);
            box-shadow:0 0 0 3px rgba(82,171,152,0.18);
            background:var(--card-bg);
        }
        textarea { resize:vertical; min-height:80px; }

        .btn-group { display:flex; gap:14px; margin-top:28px; flex-wrap:wrap; }
        .btn { flex:1; padding:13px; border:none; border-radius:9px; cursor:pointer; font-size:0.95rem; font-weight:600; font-family:'Poppins',sans-serif; transition:all 0.3s; display:flex; align-items:center; justify-content:center; gap:8px; min-width:140px; }
        .btn-save { background:linear-gradient(135deg,#2b6777,#3a8a7a); color:white; box-shadow:0 4px 12px rgba(43,103,119,0.25); }
        .btn-save:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(43,103,119,0.35); }
        .btn-danger { background:var(--danger); color:white; box-shadow:0 4px 12px rgba(229,57,53,0.2); }
        .btn-danger:hover { background:#c62828; transform:translateY(-2px); }

        @media (max-width:600px) { .form-grid { grid-template-columns:1fr; } .form-body { padding:24px 20px; } .form-card-header { padding:22px 20px; } }
    </style>
</head>
<body>

    <div class="mobile-topbar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu"><i data-lucide="menu" width="24" height="24"></i></button>
        <img src="logo.png" alt="Logo" class="mobile-logo">
        <span class="mobile-app-title">Expense Tracker</span>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h1><img src="logo.png" alt="Logo" class="sidebar-logo"><span>Expense Tracker</span></h1>
        </div>
        <nav class="nav-links">
            <a href="dailyexpense.php"   class="nav-link"><i data-lucide="layout-dashboard"></i> <span>Dashboard</span></a>
            <a href="profile.php"        class="nav-link"><i data-lucide="user"></i>             <span>My Profile</span></a>
            <a href="profile_update.php" class="nav-link active"><i data-lucide="edit"></i>      <span>Update Profile</span></a>
            <a href="expensehistory.html" class="nav-link"><i data-lucide="history"></i>         <span>Expense History</span></a>
            <a href="settings.php"       class="nav-link"><i data-lucide="settings"></i>         <span>Settings</span></a>
            <a href="logout.php"         class="nav-link logout-link"><i data-lucide="log-out"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">

        <?php if ($message): ?>
        <div class="alert-banner alert-<?php echo $msg_type; ?>">
            <i data-lucide="<?php echo $msg_type === 'success' ? 'check-circle' : 'alert-circle'; ?>" width="18" height="18"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-card-header">
                <h2><i data-lucide="edit-3" width="22" height="22"></i> Update Your Profile</h2>
                <p>Edit your personal information below and click Save Changes.</p>
            </div>

            <div class="form-body">
                <div class="readonly-field">
                    <i data-lucide="mail" width="16" height="16"></i>
                    <span>Account Email (read-only):</span>
                    <strong><?php echo htmlspecialchars($user_data['email']); ?></strong>
                </div>

                <form action="profile_update.php" method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" placeholder="John" value="<?php echo htmlspecialchars($user_data['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($user_data['last_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="10 digits" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user_data['dob']); ?>">
                        </div>
                        <div class="form-group full-width">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">— Select —</option>
                                <?php foreach (['Male','Female','Other','Prefer not to say'] as $g): ?>
                                <option value="<?=$g?>" <?=$user_data['gender']===$g?'selected':''?>><?=$g?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="address">Home Address</label>
                            <textarea id="address" name="address" placeholder="Enter your full address..."><?php echo htmlspecialchars($user_data['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-save"><i data-lucide="save" width="17" height="17"></i> Save Changes</button>
                        <button type="button" id="deleteAccountButton" class="btn btn-danger"><i data-lucide="trash-2" width="17" height="17"></i> Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="toast.js"></script>
    <script src="profile.js"></script>
    <script>
        lucide.createIcons();
        document.getElementById('hamburgerBtn')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
    </script>
</body>
</html>
