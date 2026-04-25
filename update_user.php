<?php
require 'db_connect.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $userId = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Prepare the UPDATE statement
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind parameters (s = string, i = integer)
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $address, $userId);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the dashboard on success
            header("Location: manage_users.php?status=updated");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>