<?php
require 'db_connect.php';

try {
    // Check and add monthly_budget to users
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'monthly_budget'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN monthly_budget DECIMAL(10,2) DEFAULT NULL AFTER password");
        echo "Added monthly_budget to users.\n";
    } else {
        echo "monthly_budget already exists in users.\n";
    }

    // Check and add notes to expenses
    $stmt = $pdo->query("SHOW COLUMNS FROM expenses LIKE 'notes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE expenses ADD COLUMN notes VARCHAR(255) DEFAULT NULL AFTER amount");
        echo "Added notes to expenses.\n";
    } else {
        echo "notes already exists in expenses.\n";
    }

    echo "Schema update completed successfully.\n";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>
