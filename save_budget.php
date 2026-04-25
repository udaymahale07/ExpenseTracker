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

$budget = trim($_POST['monthly_budget'] ?? '');
$budget = $budget === '' ? null : (float)$budget;

if ($budget !== null && $budget < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Budget cannot be negative.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET monthly_budget = :budget WHERE id = :id");
    $stmt->execute([
        'budget' => $budget,
        'id' => $_SESSION['user_id']
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Budget saved successfully.']);
} catch (PDOException $e) {
    error_log("Failed to save budget: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
