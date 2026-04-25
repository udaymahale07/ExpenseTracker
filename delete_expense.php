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

$id = trim($_POST['id'] ?? '');

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Expense ID missing.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'id'      => $id,
        'user_id' => $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found.']);
    }
} catch (PDOException $e) {
    error_log("Failed to delete expense: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>
