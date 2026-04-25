<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── KPI Queries ──────────────────────────────────────────────────────────────
$todayTotal   = 0;
$monthTotal   = 0;
$topCategory  = 'None';
$totalEntries = 0;

try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=:u AND expense_date=CURDATE()");
    $stmt->execute(['u' => $user_id]);
    $todayTotal = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=:u AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())");
    $stmt->execute(['u' => $user_id]);
    $monthTotal = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT category FROM expenses WHERE user_id=:u AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE()) GROUP BY category ORDER BY SUM(amount) DESC LIMIT 1");
    $stmt->execute(['u' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) $topCategory = $row['category'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE user_id=:u");
    $stmt->execute(['u' => $user_id]);
    $totalEntries = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("KPI error: " . $e->getMessage());
}

// ── Fetch recent expenses ─────────────────────────────────────────────────────
$expenses = [];
try {
    $stmt = $pdo->prepare("SELECT expense_date, category, amount FROM expenses WHERE user_id=:u ORDER BY expense_date DESC, id DESC LIMIT 50");
    $stmt->execute(['u' => $user_id]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Expense fetch error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Expense Tracker</title>
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
        }
        [data-theme="dark"] {
            --bg: #0d1520;
            --card-bg: #19263a;
            --text: #dce8f0;
            --text-light: #7a93a8;
            --border: #243447;
            --input-bg: #1a2d41;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        /* ══════════════════════════════════
           DARK SIDEBAR
        ══════════════════════════════════ */
        .sidebar {
            width: 256px;
            background: linear-gradient(180deg, #1b3e48 0%, #132e36 100%);
            box-shadow: 2px 0 20px rgba(0,0,0,0.25);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 150;
            transition: transform 0.3s ease;
        }
        .sidebar-header {
            padding: 22px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-header h1 {
            color: #fff;
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .sidebar-logo { width: 32px; height: 32px; object-fit: contain; border-radius: 6px; }

        .nav-links {
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow-y: auto;
        }
        .nav-link {
            padding: 13px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.62);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        .nav-link:hover { background: rgba(82,171,152,0.14); color: rgba(255,255,255,0.92); }
        .nav-link.active { background: rgba(82,171,152,0.22); color: #fff; font-weight: 600; }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: #52ab98;
            border-radius: 0 2px 2px 0;
        }
        .logout-link { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.07); color: rgba(255,130,120,0.8) !important; }
        .logout-link:hover { background: rgba(229,57,53,0.14) !important; color: #ff8a80 !important; }

        /* Mobile topbar */
        .mobile-topbar {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 58px;
            background: linear-gradient(90deg, #1b3e48, #2b6777);
            z-index: 200;
            align-items: center;
            padding: 0 18px;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
        }
        .hamburger-btn {
            background: none; border: none; color: white; cursor: pointer;
            padding: 6px; display: flex; align-items: center;
            border-radius: 6px; transition: background 0.2s;
        }
        .hamburger-btn:hover { background: rgba(255,255,255,0.12); }
        .mobile-logo { width: 28px; height: 28px; object-fit: contain; border-radius: 5px; }
        .mobile-app-title { color: white; font-weight: 600; font-size: 0.95rem; }
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.52);
            z-index: 140;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 768px) {
            .mobile-topbar { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0 !important; padding: 80px 18px 30px !important; }
            body.sidebar-open .sidebar { transform: translateX(0); }
            body.sidebar-open .sidebar-overlay { display: block; }
        }

        /* ══════════════════════════════════
           MAIN CONTENT
        ══════════════════════════════════ */
        .main-content {
            flex: 1;
            margin-left: 256px;
            padding: 36px 40px;
            min-width: 0;
        }

        /* Page Header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .page-title h2 { font-size: 1.5rem; font-weight: 700; color: var(--text); }
        .page-title p { font-size: 0.85rem; color: var(--text-light); margin-top: 3px; }
        .date-badge {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.82rem;
            color: var(--text-light);
            font-weight: 500;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 900px) { .kpi-grid { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 580px)  { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 22px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.3s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 22px rgba(43,103,119,0.12); }
        [data-theme="dark"] .kpi-card { box-shadow: 0 2px 16px rgba(0,0,0,0.3); }

        .kpi-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .kpi-icon.today    { background: linear-gradient(135deg,#e8f4f1,#cfeae3); color: #2b6777; }
        .kpi-icon.month    { background: linear-gradient(135deg,#e3f0fb,#c8e1f7); color: #2563eb; }
        .kpi-icon.category { background: linear-gradient(135deg,#fef3e2,#fde2a3); color: #d97706; }

        [data-theme="dark"] .kpi-icon.today    { background: rgba(82,171,152,0.15); color: #52ab98; }
        [data-theme="dark"] .kpi-icon.month    { background: rgba(37,99,235,0.15);  color: #60a5fa; }
        [data-theme="dark"] .kpi-icon.category { background: rgba(217,119,6,0.15);  color: #fbbf24; }

        .kpi-info { min-width: 0; }
        .kpi-label { font-size: 0.75rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.6px; font-weight: 500; }
        .kpi-value { font-size: 1.35rem; font-weight: 700; color: var(--text); margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Cards */
        .card {
            background: var(--card-bg);
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
            margin-bottom: 24px;
            transition: background 0.3s;
        }
        [data-theme="dark"] .card { box-shadow: 0 2px 16px rgba(0,0,0,0.3); }

        .card-title {
            color: var(--primary);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 9px;
        }

        label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 7px;
        }
        input, select {
            width: 100%;
            padding: 11px 14px;
            margin-bottom: 18px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            background: var(--input-bg);
            color: var(--text);
            transition: all 0.25s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(82,171,152,0.18);
            background: var(--card-bg);
        }
        button[type="submit"] {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2b6777, #3a8a7a);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 9px;
            cursor: pointer;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(43,103,119,0.25);
        }
        button[type="submit"]:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(43,103,119,0.35); }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 18px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--bg); color: var(--text-light); font-weight: 500; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.6px; border-bottom: 2px solid var(--border); }
        [data-theme="dark"] th { background: rgba(255,255,255,0.03); }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(82,171,152,0.05); }
        .category-cell { display: flex; align-items: center; gap: 9px; font-weight: 500; }
        .amount { font-weight: 700; color: var(--primary); }
        [data-theme="dark"] .amount { color: #52ab98; }

        /* Success Popup */
        .success-popup-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000;
            opacity: 0; visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .success-popup-overlay.show { opacity: 1; visibility: visible; }
        .success-popup {
            background: var(--card-bg);
            padding: 32px 40px;
            border-radius: 18px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.15);
            display: flex; flex-direction: column; align-items: center; gap: 14px;
            transform: scale(0.85) translateY(16px);
            transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
        }
        .success-popup-overlay.show .success-popup { transform: scale(1) translateY(0); }
        .success-icon-wrap {
            width: 64px; height: 64px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #4caf50;
            animation: bounceIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275) 0.2s both;
        }
        @keyframes bounceIn { from { transform: scale(0); } to { transform: scale(1); } }
        .success-popup h3 { margin: 0; font-size: 1.1rem; color: var(--text); }

        .content-grid {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            align-items: flex-start;
        }
        .form-card { width: 360px; flex-shrink: 0; }
        .table-card { flex: 1; min-width: 300px; }

        @media (max-width: 700px) {
            .form-card { width: 100%; }
            .table-card { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Success Popup -->
    <div class="success-popup-overlay" id="successPopup">
        <div class="success-popup">
            <div class="success-icon-wrap">
                <i data-lucide="check-circle" width="36" height="36"></i>
            </div>
            <h3>Expense Added!</h3>
        </div>
    </div>

    <!-- Mobile Topbar -->
    <div class="mobile-topbar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
            <i data-lucide="menu" width="24" height="24"></i>
        </button>
        <img src="logo.png" alt="Logo" class="mobile-logo">
        <span class="mobile-app-title">Expense Tracker</span>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>
                <img src="logo.png" alt="Logo" class="sidebar-logo">
                <span>Expense Tracker</span>
            </h1>
        </div>
        <nav class="nav-links">
            <a href="dailyexpense.php" class="nav-link active"><i data-lucide="layout-dashboard"></i> <span>Dashboard</span></a>
            <a href="profile.php"      class="nav-link"><i data-lucide="user"></i>             <span>My Profile</span></a>
            <a href="profile_update.php" class="nav-link"><i data-lucide="edit"></i>           <span>Update Profile</span></a>
            <a href="expensehistory.html" class="nav-link"><i data-lucide="history"></i>       <span>Expense History</span></a>
            <a href="settings.php"     class="nav-link"><i data-lucide="settings"></i>         <span>Settings</span></a>
            <a href="logout.php"       class="nav-link logout-link"><i data-lucide="log-out"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h2>Dashboard</h2>
                <p>Track and manage your daily spending</p>
            </div>
            <div class="date-badge"><?php echo date('l, d F Y'); ?></div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon today"><i data-lucide="calendar-check" width="24" height="24"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Spent Today</span>
                    <span class="kpi-value">₹<?php echo number_format($todayTotal, 2); ?></span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon month"><i data-lucide="trending-up" width="24" height="24"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">This Month</span>
                    <span class="kpi-value">₹<?php echo number_format($monthTotal, 2); ?></span>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon category"><i data-lucide="award" width="24" height="24"></i></div>
                <div class="kpi-info">
                    <span class="kpi-label">Top Category</span>
                    <span class="kpi-value"><?php echo htmlspecialchars($topCategory); ?></span>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">

            <!-- ADD EXPENSE FORM -->
            <form id="expense-form" class="card form-card">
                <div class="card-title"><i data-lucide="plus-circle" width="20" height="20"></i> Add Expense</div>

                <label for="date">Date</label>
                <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">— Select Category —</option>
                    <option value="Food">🍽️ Food</option>
                    <option value="Beverages">☕ Beverages</option>
                    <option value="Transport">🚗 Transport</option>
                    <option value="Education">📚 Education</option>
                    <option value="Utilities">⚡ Utilities</option>
                    <option value="Other">🏷️ Others</option>
                </select>

                <label for="amount">Amount (₹)</label>
                <input type="number" id="amount" name="amount" required min="0.01" step="0.01" placeholder="0.00">

                <button type="submit"><i data-lucide="check" width="18" height="18"></i> Save Expense</button>
            </form>

            <!-- EXPENSE TABLE -->
            <div class="card table-card">
                <div class="card-title"><i data-lucide="list" width="20" height="20"></i> Recent Expenses</div>
                <table id="expense-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($expenses as $expense):
                        $cat  = strtolower($expense['category']);
                        $icon = 'tag';
                        if (strpos($cat,'food')      !== false) $icon = 'utensils';
                        elseif (strpos($cat,'bever') !== false) $icon = 'coffee';
                        elseif (strpos($cat,'trans') !== false) $icon = 'car';
                        elseif (strpos($cat,'edu')   !== false) $icon = 'book-open';
                        elseif (strpos($cat,'util')  !== false) $icon = 'zap';
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                            <td>
                                <div class="category-cell">
                                    <i data-lucide="<?php echo $icon; ?>" width="16" height="16" style="color:var(--primary-light)"></i>
                                    <?php echo htmlspecialchars($expense['category']); ?>
                                </div>
                            </td>
                            <td class="amount">₹<?php echo number_format($expense['amount'],2); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;padding:40px;color:var(--text-light)">
                                <i data-lucide="inbox" width="40" height="40" style="opacity:0.4;display:block;margin:0 auto 10px"></i>
                                No expenses logged yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script src="toast.js"></script>
    <script>
        lucide.createIcons();

        // ── Hamburger Menu ──
        document.getElementById('hamburgerBtn')?.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => document.body.classList.remove('sidebar-open'));

        // ── Add Expense ──
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('expense-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const form = this;

                fetch('add_expense.php', { method: 'POST', body: new FormData(form) })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const tbody = document.querySelector('#expense-table tbody');

                            // Remove empty state if present
                            const empty = tbody.querySelector('td[colspan="3"]');
                            if (empty) empty.closest('tr').remove();

                            const cat = data.expense.category.toLowerCase();
                            let iconName = 'tag';
                            if (cat.includes('food'))    iconName = 'utensils';
                            else if (cat.includes('bev')) iconName = 'coffee';
                            else if (cat.includes('tra')) iconName = 'car';
                            else if (cat.includes('edu')) iconName = 'book-open';
                            else if (cat.includes('uti')) iconName = 'zap';

                            const row = tbody.insertRow(0);
                            row.innerHTML = `
                                <td>${data.expense.date}</td>
                                <td><div class="category-cell"><i data-lucide="${iconName}" width="16" height="16" style="color:var(--primary-light)"></i> ${data.expense.category}</div></td>
                                <td class="amount">₹${parseFloat(data.expense.amount).toFixed(2)}</td>`;
                            lucide.createIcons();

                            form.reset();
                            document.getElementById('date').value = '<?php echo date('Y-m-d'); ?>';

                            // Success popup
                            const popup = document.getElementById('successPopup');
                            popup.classList.add('show');
                            setTimeout(() => popup.classList.remove('show'), 2500);

                            Toast.success('Expense saved successfully!');
                        } else {
                            Toast.error('Error: ' + (data.message || 'Could not save expense.'));
                        }
                    })
                    .catch(() => Toast.error('Network error. Please try again.'));
            });
        });
    </script>
</body>
</html>