<?php
// add_expense.php — called via AJAX from dailyexpense.php

session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// ── 1. Auth guard ────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit;
}

// ── 2. Only accept POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// ── 3. Collect & validate inputs ──────────────────────────────────────────────
$user_id  = $_SESSION['user_id'];
$date     = trim($_POST['date']     ?? '');
$category = trim($_POST['category'] ?? '');
$amount   = trim($_POST['amount']   ?? '');
$notes    = trim($_POST['notes']    ?? '');

if (empty($date) || empty($category) || $amount === '') {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Validate date format YYYY-MM-DD
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate(
    (int)substr($date, 5, 2),
    (int)substr($date, 8, 2),
    (int)substr($date, 0, 4)
)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date.']);
    exit;
}

// Validate amount
$amount = (float) $amount;
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than zero.']);
    exit;
}

// Whitelist categories
$allowed_categories = ['Food', 'Beverages', 'Transport', 'Education', 'Utilities', 'Other'];
if (!in_array($category, $allowed_categories, true)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid category.']);
    exit;
}

// ── 4. Insert into DB ─────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        "INSERT INTO expenses (user_id, expense_date, category, amount, notes)
         VALUES (:user_id, :expense_date, :category, :amount, :notes)"
    );
    $stmt->execute([
        'user_id'      => $user_id,
        'expense_date' => $date,
        'category'     => $category,
        'amount'       => $amount,
        'notes'        => $notes,
    ]);

    // ── 5. Return the new row so JS can prepend it to the table ───────────────
    echo json_encode([
        'status'  => 'success',
        'expense' => [
            'id'       => $pdo->lastInsertId(),
            'date'     => $date,
            'category' => $category,
            'amount'   => '₹' . number_format($amount, 2),
            'notes'    => htmlspecialchars($notes),
        ]
    ]);

} catch (PDOException $e) {
    error_log("add_expense failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Could not save expense. Please try again.']);
}
?>