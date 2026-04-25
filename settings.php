<?php
session_start();
require 'db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit(); }
$user_id = $_SESSION['user_id'];
$email   = $_SESSION['email'] ?? 'User';

$stmt = $pdo->prepare("SELECT monthly_budget FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$currentBudget = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Expense Tracker</title>
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
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Poppins',sans-serif; background:var(--bg); color:var(--text); display:flex; min-height:100vh; transition:background 0.3s,color 0.3s; }

        /* ── Dark Sidebar ── */
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
            .main-content { margin-left:0 !important; padding:80px 18px 40px !important; }
            body.sidebar-open .sidebar { transform:translateX(0); }
            body.sidebar-open .sidebar-overlay { display:block; }
        }

        /* ── Main ── */
        .main-content { flex:1; margin-left:256px; padding:36px 40px; }
        .page-header { margin-bottom:28px; }
        .page-header h2 { font-size:1.5rem; font-weight:700; }
        .page-header p { font-size:0.85rem; color:var(--text-light); margin-top:4px; }

        /* ── Settings Cards ── */
        .settings-grid { display:flex; flex-direction:column; gap:20px; max-width:720px; }

        .setting-card {
            background:var(--card-bg);
            border:1px solid var(--border);
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 2px 12px rgba(0,0,0,0.04);
            transition:background 0.3s;
        }
        [data-theme="dark"] .setting-card { box-shadow:0 2px 12px rgba(0,0,0,0.3); }

        .setting-card-header {
            display:flex;
            align-items:center;
            gap:13px;
            padding:18px 24px;
            border-bottom:1px solid var(--border);
            background:var(--bg);
            transition:background 0.3s;
        }
        [data-theme="dark"] .setting-card-header { background:rgba(255,255,255,0.02); }
        .setting-card-icon {
            width:38px; height:38px;
            background:linear-gradient(135deg,#e8f4f1,#cfeae3);
            border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            color:var(--primary);
        }
        [data-theme="dark"] .setting-card-icon { background:rgba(82,171,152,0.15); }
        .setting-card-header h3 { font-size:0.95rem; font-weight:600; color:var(--text); }
        .setting-card-header p  { font-size:0.78rem; color:var(--text-light); margin-top:2px; }

        .setting-card-body { padding:22px 24px; display:flex; flex-direction:column; gap:18px; }

        /* ── Theme Picker ── */
        .theme-picker { display:flex; gap:10px; flex-wrap:wrap; }
        .theme-btn {
            flex:1;
            min-width:90px;
            padding:12px 10px;
            background:var(--bg);
            border:2px solid var(--border);
            border-radius:10px;
            cursor:pointer;
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:7px;
            font-family:'Poppins',sans-serif;
            font-size:0.82rem;
            font-weight:500;
            color:var(--text-light);
            transition:all 0.2s;
        }
        .theme-btn:hover { border-color:var(--primary-light); color:var(--primary); }
        .theme-btn.active {
            border-color:var(--primary);
            background:rgba(43,103,119,0.05);
            color:var(--primary);
            font-weight:600;
        }
        [data-theme="dark"] .theme-btn.active { background:rgba(82,171,152,0.12); }
        .theme-icon {
            width:32px; height:32px;
            border-radius:8px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem;
        }
        .theme-icon.light-icon { background:#fff8e1; }
        .theme-icon.dark-icon  { background:#1a2535; }
        .theme-icon.sys-icon   { background:linear-gradient(135deg,#fff8e1 50%,#1a2535 50%); }

        /* ── Setting Row ── */
        .setting-row {
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:14px 0;
            border-bottom:1px solid var(--border);
            gap:20px;
        }
        .setting-row:last-child { border-bottom:none; padding-bottom:0; }
        .setting-row:first-child { padding-top:0; }
        .setting-info { flex:1; min-width:0; }
        .setting-info strong { display:block; font-size:0.9rem; font-weight:600; color:var(--text); }
        .setting-info span   { display:block; font-size:0.78rem; color:var(--text-light); margin-top:3px; }

        /* Toggle Switch */
        .toggle-wrap { flex-shrink:0; }
        .toggle { position:relative; display:inline-block; width:46px; height:26px; }
        .toggle input { opacity:0; width:0; height:0; }
        .toggle-slider {
            position:absolute; inset:0;
            background:#cdd5de;
            border-radius:26px;
            cursor:pointer;
            transition:0.3s;
        }
        .toggle-slider::before {
            content:'';
            position:absolute;
            width:20px; height:20px;
            left:3px; top:3px;
            background:white;
            border-radius:50%;
            transition:0.3s;
            box-shadow:0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle input:checked + .toggle-slider { background:var(--primary-light); }
        .toggle input:checked + .toggle-slider::before { transform:translateX(20px); }

        /* Select */
        .setting-select {
            padding:8px 12px;
            border:1.5px solid var(--border);
            border-radius:8px;
            background:var(--bg);
            color:var(--text);
            font-family:'Poppins',sans-serif;
            font-size:0.85rem;
            cursor:pointer;
            outline:none;
            transition:border-color 0.2s;
        }
        .setting-select:focus { border-color:var(--primary-light); }

        /* Link rows */
        .setting-link {
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:13px 0;
            border-bottom:1px solid var(--border);
            text-decoration:none;
            color:var(--text);
            transition:color 0.2s;
        }
        .setting-link:last-child { border-bottom:none; padding-bottom:0; }
        .setting-link:hover { color:var(--primary); }
        .setting-link-left { display:flex; align-items:center; gap:12px; }
        .setting-link-left i { color:var(--primary-light); }
        .setting-link strong { font-size:0.9rem; font-weight:500; }
        .setting-link span { font-size:0.78rem; color:var(--text-light); display:block; margin-top:2px; }
        .chevron { color:var(--text-light); }

        /* Danger Zone */
        .danger-zone .setting-card-icon { background:rgba(229,57,53,0.1); color:#e53935; }
        .danger-zone .setting-card-header { border-color:rgba(229,57,53,0.15); }
        .danger-btn {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:10px 18px;
            background:#e53935;
            color:white;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-family:'Poppins',sans-serif;
            font-size:0.85rem;
            font-weight:600;
            transition:all 0.2s;
        }
        .danger-btn:hover { background:#c62828; transform:translateY(-1px); }

        .save-notice {
            font-size:0.78rem;
            color:var(--primary-light);
            display:flex;
            align-items:center;
            gap:5px;
            margin-top:4px;
        }
    </style>
</head>
<body>

    <div class="mobile-topbar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Menu"><i data-lucide="menu" width="24" height="24"></i></button>
        <img src="logo.png" alt="Logo" class="mobile-logo">
        <span class="mobile-app-title">Settings</span>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h1><img src="logo.png" alt="Logo" class="sidebar-logo"><span>Expense Tracker</span></h1>
        </div>
        <nav class="nav-links">
            <a href="dailyexpense.php"   class="nav-link"><i data-lucide="layout-dashboard"></i> <span>Dashboard</span></a>
            <a href="profile.php"        class="nav-link"><i data-lucide="user"></i>             <span>My Profile</span></a>
            <a href="profile_update.php" class="nav-link"><i data-lucide="edit"></i>             <span>Update Profile</span></a>
            <a href="expensehistory.html" class="nav-link"><i data-lucide="history"></i>         <span>Expense History</span></a>
            <a href="settings.php"       class="nav-link active"><i data-lucide="settings"></i>  <span>Settings</span></a>
            <a href="logout.php"         class="nav-link logout-link"><i data-lucide="log-out"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h2>Settings</h2>
            <p>Customise your Expense Tracker experience</p>
        </div>

        <div class="settings-grid">

            <!-- ── Appearance ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="palette" width="20" height="20"></i></div>
                    <div>
                        <h3>Appearance</h3>
                        <p>Choose how Expense Tracker looks on your device</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <div>
                        <strong style="font-size:0.85rem;color:var(--text-light);text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">Theme</strong>
                        <div class="theme-picker" style="margin-top:12px;" id="themePicker">
                            <button class="theme-btn" data-theme="light" onclick="pickTheme('light')">
                                <div class="theme-icon light-icon">☀️</div>
                                Light
                            </button>
                            <button class="theme-btn" data-theme="dark" onclick="pickTheme('dark')">
                                <div class="theme-icon dark-icon">🌙</div>
                                Dark
                            </button>
                            <button class="theme-btn" data-theme="system" onclick="pickTheme('system')">
                                <div class="theme-icon sys-icon">💻</div>
                                System
                            </button>
                        </div>
                        <p class="save-notice" id="themeNotice" style="margin-top:10px;display:none;">
                            <i data-lucide="check-circle" width="13" height="13"></i> Theme saved!
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Currency & Display ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="indian-rupee" width="20" height="20"></i></div>
                    <div>
                        <h3>Currency & Display</h3>
                        <p>Regional and formatting preferences</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Currency Symbol</strong>
                            <span>Displayed next to all expense amounts</span>
                        </div>
                        <select class="setting-select" id="currencySelect" onchange="saveSetting('et_currency',this.value)">
                            <option value="₹">₹ — Indian Rupee (INR)</option>
                            <option value="$">$ — US Dollar (USD)</option>
                            <option value="€">€ — Euro (EUR)</option>
                            <option value="£">£ — British Pound (GBP)</option>
                        </select>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Date Format</strong>
                            <span>How dates are displayed throughout the app</span>
                        </div>
                        <select class="setting-select" id="dateFormatSelect" onchange="saveSetting('et_dateformat',this.value)">
                            <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Dashboard Preferences ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="layout-dashboard" width="20" height="20"></i></div>
                    <div>
                        <h3>Dashboard</h3>
                        <p>Control what's displayed on your main dashboard</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Show KPI Summary Cards</strong>
                            <span>Display Today's Spending, Monthly Total and Top Category cards</span>
                        </div>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox" id="kpiToggle" onchange="saveBoolSetting('et_kpi_cards',this.checked)" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Show Charts on History Page</strong>
                            <span>Display bar charts and category doughnut charts</span>
                        </div>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox" id="chartsToggle" onchange="saveBoolSetting('et_charts',this.checked)" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Monthly Budget Limit</strong>
                            <span>Set a budget to track progress on the dashboard</span>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <input type="number" id="budgetInput" placeholder="e.g. 10000" class="setting-select" style="width:120px;" value="<?php echo htmlspecialchars($currentBudget ?? ''); ?>">
                            <button onclick="saveBudget()" style="padding:8px 12px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-family:'Poppins',sans-serif; font-size:0.85rem;">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Notifications ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="bell" width="20" height="20"></i></div>
                    <div>
                        <h3>Notifications</h3>
                        <p>Manage app notifications and alerts</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Toast Notifications</strong>
                            <span>Show slide-in confirmations for actions (recommended)</span>
                        </div>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox" id="toastToggle" onchange="saveBoolSetting('et_toasts',this.checked)" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Monthly Summary Reminder</strong>
                            <span>Browser notification at month end — <em>Coming soon</em></span>
                        </div>
                        <div class="toggle-wrap">
                            <label class="toggle">
                                <input type="checkbox" disabled>
                                <span class="toggle-slider" style="opacity:0.4"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Account & Security ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="shield" width="20" height="20"></i></div>
                    <div>
                        <h3>Account &amp; Security</h3>
                        <p>Manage your account and security settings</p>
                    </div>
                </div>
                <div class="setting-card-body" style="padding:10px 24px 18px;">
                    <a href="profile.php" class="setting-link">
                        <div class="setting-link-left">
                            <i data-lucide="user" width="18" height="18"></i>
                            <div><strong>View My Profile</strong><span>See your name, email and details</span></div>
                        </div>
                        <i data-lucide="chevron-right" width="18" height="18" class="chevron"></i>
                    </a>
                    <a href="profile_update.php" class="setting-link">
                        <div class="setting-link-left">
                            <i data-lucide="edit-3" width="18" height="18"></i>
                            <div><strong>Update Profile</strong><span>Edit personal information</span></div>
                        </div>
                        <i data-lucide="chevron-right" width="18" height="18" class="chevron"></i>
                    </a>
                    <a href="#" class="setting-link" onclick="Toast.info('Change Password — coming soon!'); return false;">
                        <div class="setting-link-left">
                            <i data-lucide="lock" width="18" height="18"></i>
                            <div><strong>Change Password</strong><span>Update your account password — Coming soon</span></div>
                        </div>
                        <i data-lucide="chevron-right" width="18" height="18" class="chevron"></i>
                    </a>
                    <a href="#" class="setting-link" onclick="Toast.info('Two-Factor Authentication — coming soon!'); return false;">
                        <div class="setting-link-left">
                            <i data-lucide="smartphone" width="18" height="18"></i>
                            <div><strong>Two-Factor Authentication</strong><span>Add extra security — Coming soon</span></div>
                        </div>
                        <i data-lucide="chevron-right" width="18" height="18" class="chevron"></i>
                    </a>
                </div>
            </div>

            <!-- ── Privacy & Legal ── -->
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="file-text" width="20" height="20"></i></div>
                    <div>
                        <h3>Privacy &amp; Legal</h3>
                        <p>Review our privacy practices and legal agreements</p>
                    </div>
                </div>
                <div class="setting-card-body" style="padding:10px 24px 18px;">
                    <a href="privacy_policy.html" target="_blank" class="setting-link">
                        <div class="setting-link-left">
                            <i data-lucide="shield-check" width="18" height="18"></i>
                            <div><strong>Privacy Policy</strong><span>How we collect and use your data</span></div>
                        </div>
                        <i data-lucide="external-link" width="15" height="15" class="chevron"></i>
                    </a>
                    <a href="terms.html" target="_blank" class="setting-link">
                        <div class="setting-link-left">
                            <i data-lucide="scroll" width="18" height="18"></i>
                            <div><strong>Terms of Service</strong><span>The rules for using Expense Tracker</span></div>
                        </div>
                        <i data-lucide="external-link" width="15" height="15" class="chevron"></i>
                    </a>
                    <a href="expensehistory.html" class="setting-link">
                        <div class="setting-link-left">
                            <i data-lucide="download" width="18" height="18"></i>
                            <div><strong>Export My Data</strong><span>Download all expense records as CSV or PDF</span></div>
                        </div>
                        <i data-lucide="chevron-right" width="18" height="18" class="chevron"></i>
                    </a>
                </div>
            </div>

            <!-- ── Danger Zone ── -->
            <div class="setting-card danger-zone">
                <div class="setting-card-header">
                    <div class="setting-card-icon"><i data-lucide="alert-triangle" width="20" height="20"></i></div>
                    <div>
                        <h3>Danger Zone</h3>
                        <p>Irreversible actions — proceed with caution</p>
                    </div>
                </div>
                <div class="setting-card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Delete My Account</strong>
                            <span>Permanently deletes your account and all expense data. This cannot be undone.</span>
                        </div>
                        <button class="danger-btn" id="deleteAccountButton">
                            <i data-lucide="trash-2" width="15" height="15"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer Copyright -->
        <footer style="max-width:720px;margin-top:36px;padding-top:24px;border-top:1px solid var(--border);text-align:center;font-size:0.78rem;color:var(--text-light);">
            <p>&copy; <?php echo date('Y'); ?> Expense Tracker. All rights reserved.</p>
            <p style="margin-top:6px;">
                <a href="privacy_policy.html" style="color:var(--primary-light);text-decoration:none;">Privacy Policy</a>
                &nbsp;·&nbsp;
                <a href="terms.html" style="color:var(--primary-light);text-decoration:none;">Terms of Service</a>
                &nbsp;·&nbsp;
                <a href="ContactUs.html" style="color:var(--primary-light);text-decoration:none;">Contact Us</a>
            </p>
        </footer>
    </main>

    <script src="toast.js"></script>
    <script src="profile.js"></script>
    <script>
        lucide.createIcons();

        // Hamburger
        document.getElementById('hamburgerBtn')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

        // ── Theme Picker ──
        function pickTheme(t) {
            ThemeManager.set(t);
            highlightThemeBtn(t);
            lucide.createIcons();
            const notice = document.getElementById('themeNotice');
            notice.style.display = 'flex';
            setTimeout(() => notice.style.display = 'none', 2000);
            Toast.success('Theme set to ' + t.charAt(0).toUpperCase() + t.slice(1) + '!');
        }
        function highlightThemeBtn(t) {
            document.querySelectorAll('.theme-btn').forEach(b => {
                b.classList.toggle('active', b.dataset.theme === t);
            });
        }
        highlightThemeBtn(ThemeManager.get());

        // ── Save Helpers ──
        function saveSetting(key, val) {
            localStorage.setItem(key, val);
            Toast.success('Setting saved!');
        }
        function saveBoolSetting(key, val) {
            localStorage.setItem(key, val ? '1' : '0');
        }

        function saveBudget() {
            const val = document.getElementById('budgetInput').value;
            fetch('save_budget.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'monthly_budget=' + encodeURIComponent(val)
            }).then(r => r.json()).then(data => {
                if (data.status === 'success') {
                    Toast.success('Budget saved successfully!');
                } else {
                    Toast.error(data.message || 'Error saving budget.');
                }
            }).catch(e => Toast.error('Network error.'));
        }

        // ── Load Saved Settings ──
        (function loadSettings() {
            const cur = localStorage.getItem('et_currency');
            if (cur) { const s = document.getElementById('currencySelect'); if(s) s.value = cur; }

            const df = localStorage.getItem('et_dateformat');
            if (df) { const s = document.getElementById('dateFormatSelect'); if(s) s.value = df; }

            const kpi = localStorage.getItem('et_kpi_cards');
            if (kpi === '0') { const t = document.getElementById('kpiToggle'); if(t) t.checked = false; }

            const charts = localStorage.getItem('et_charts');
            if (charts === '0') { const t = document.getElementById('chartsToggle'); if(t) t.checked = false; }

            const toasts = localStorage.getItem('et_toasts');
            if (toasts === '0') { const t = document.getElementById('toastToggle'); if(t) t.checked = false; }
        })();
    </script>
</body>
</html>
