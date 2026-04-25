<?php
// delete_user.php — Admin-only: deletes a user by ID

session_start();
require 'db_connect.php';

// ── Admin auth guard ──────────────────────────────────────────────────────────
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

// ── Validate ID param ─────────────────────────────────────────────────────────
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: manage_users.php?status=error");
    exit();
}

$user_id_to_delete = (int) $_GET['id'];

// Prevent admin from deleting themselves
if ($user_id_to_delete === (int) $_SESSION['user_id']) {
    header("Location: manage_users.php?status=error");
    exit();
}

// ── Delete via PDO ────────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id_to_delete]);
    header("Location: manage_users.php?status=deleted");
    exit();
} catch (PDOException $e) {
    error_log("delete_user failed: " . $e->getMessage());
    header("Location: manage_users.php?status=error");
    exit();
}
?>