<?php
// get_expenses.php - API endpoint to retrieve up to 60 days of expense history
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, expense_date, category, amount, notes 
         FROM expenses 
         WHERE user_id = :user_id 
           AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) 
         ORDER BY expense_date DESC, id DESC"
    );
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $expenses]);
} catch (PDOException $e) {
    error_log("Failed to fetch history: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
