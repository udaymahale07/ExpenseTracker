<?php
session_start();
require 'db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id_to_delete = $_SESSION['user_id'];

try {
    $pdo->beginTransaction(); // Start a transaction

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id_to_delete]);
    
    $pdo->commit(); 

} catch (PDOException $e) {
    $pdo->rollBack(); 
    error_log("Account deletion failed: " . $e->getMessage());
    header("Location: profile.php?error=deletion_failed");
    exit();
}

$_SESSION = array();

session_destroy();

header("Location: registrationform.html?success=Your account has been successfully deleted.");
exit();
?>

