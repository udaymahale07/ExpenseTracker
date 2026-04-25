<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$id       = trim($_POST['id'] ?? '');
$date     = trim($_POST['date'] ?? '');
$category = trim($_POST['category'] ?? '');
$amount   = trim($_POST['amount'] ?? '');
$notes    = trim($_POST['notes'] ?? '');

if (empty($id) || empty($date) || empty($category) || $amount === '') {
    echo json_encode(['status' => 'error', 'message' => 'Required fields missing.']);
    exit;
}

$amount = (float) $amount;
if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than zero.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE expenses SET expense_date = :date, category = :category, amount = :amount, notes = :notes 
         WHERE id = :id AND user_id = :user_id"
    );
    $stmt->execute([
        'date'     => $date,
        'category' => $category,
        'amount'   => $amount,
        'notes'    => $notes,
        'id'       => $id,
        'user_id'  => $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found or no changes made.']);
    }
} catch (PDOException $e) {
    error_log("Failed to edit expense: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
