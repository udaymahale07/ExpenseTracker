<?php
/**
 * db_connect.php
 * Central PDO database connection used by all pages.
 * Both databases (user_portal & expense_tracker) are in MySQL,
 * so we connect to user_portal (users table) and assume expenses
 * are in the same database. Adjust $dbname if they differ.
 */

$host   = 'localhost';
$dbname = 'ExpenseTracker';   // ← updated to your new database name
$user   = 'root';
$pass   = '';              // ← change if you set a MySQL password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    // Return a safe JSON error if called from an API endpoint
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database unavailable. Try again later.']);
    } else {
        echo "<p style='color:red;text-align:center'>Database connection failed. Please contact support.</p>";
    }
    exit;
}
?>