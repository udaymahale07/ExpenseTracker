<?php
// manage_users.php — Admin dashboard (ADMIN AUTH REQUIRED)

session_start();
require 'db_connect.php';

// ── Admin auth guard ──────────────────────────────────────────────────────────
// Only allow users with is_admin = 1. Redirect everyone else.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $admin_check = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin_check || empty($admin_check['is_admin'])) {
        header("Location: dailyexpense.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Admin check failed: " . $e->getMessage());
    header("Location: dailyexpense.php");
    exit();
}

// ── Fetch all users via PDO ───────────────────────────────────────────────────
$users = [];
try {
    $stmt = $pdo->query("SELECT id, email, phone, registered_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("manage_users fetch failed: " . $e->getMessage());
}

// ── Status messages from redirects ────────────────────────────────────────────
$status_msg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') $status_msg = 'User deleted successfully.';
    if ($_GET['status'] === 'error')   $status_msg = 'An error occurred.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin — Manage Users</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f2f5f7; margin: 0; padding: 30px; color: #333; }
        h2   { color: #2b6777; margin-bottom: 20px; }
        .status-msg { padding: 12px 16px; border-radius: 5px; margin-bottom: 20px; background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        th, td { border-bottom: 1px solid #eee; text-align: left; padding: 14px 16px; }
        th { background-color: #f9f9f9; color: #555; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f5f9ff; }
        a { text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; font-weight: 600; }
        .delete-btn { background-color: #f44336; color: white; }
        .delete-btn:hover { background-color: #c62828; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #2b6777; border: 1px solid #2b6777; padding: 8px 16px; border-radius: 5px; }
        .back-link:hover { background-color: #2b67771a; }
        .no-users { text-align: center; padding: 30px; color: #888; }
    </style>
</head>
<body>
    <a href="dailyexpense.php" class="back-link">← Back to Dashboard</a>
    <h2>User Management Dashboard</h2>

    <?php if ($status_msg): ?>
        <div class="status-msg"><?php echo htmlspecialchars($status_msg); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="5" class="no-users">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['registered_at'] ?? '—'); ?></td>
                    <td>
                        <a href="delete_user.php?id=<?php echo (int)$row['id']; ?>"
                           class="delete-btn"
                           onclick="return confirm('Are you sure you want to delete this user?');">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>