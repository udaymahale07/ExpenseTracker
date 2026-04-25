<?php
// registration.php — processes the registration form via AJAX (fetch from registration.js)

session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// ── Helper ────────────────────────────────────────────────────────────────────
function send_json($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// ── 1. Only accept POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json('error', 'Invalid request method.');
}

// ── 2. Collect & trim inputs ──────────────────────────────────────────────────
$email           = trim($_POST['email']           ?? '');
$phone           = trim($_POST['phone']           ?? '');
$password        = trim($_POST['password']        ?? '');
$confirm_password = trim($_POST['confirmPassword']?? '');

// ── 3. Server-side validation ─────────────────────────────────────────────────
if (empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    send_json('error', 'All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json('error', 'Please enter a valid email address.');
}

if (!preg_match('/^\d{10}$/', $phone)) {
    send_json('error', 'Phone number must be exactly 10 digits.');
}

if (strlen($password) < 6) {
    send_json('error', 'Password must be at least 6 characters.');
}

if ($password !== $confirm_password) {
    send_json('error', 'Passwords do not match.');
}

// ── 4. Check if email already exists ─────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        send_json('error', 'An account with this email already exists.');
    }
} catch (PDOException $e) {
    error_log("Registration check failed: " . $e->getMessage());
    send_json('error', 'A server error occurred. Please try again.');
}

// ── 5. Hash password & insert user ───────────────────────────────────────────
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (email, phone, password)
         VALUES (:email, :phone, :password)"
    );
    $stmt->execute([
        'email'    => $email,
        'phone'    => $phone,
        'password' => $hashed_password,
    ]);

    send_json('success', 'Account created successfully! Please log in.');

} catch (PDOException $e) {
    error_log("Registration insert failed: " . $e->getMessage());
    send_json('error', 'Could not create your account. Please try again.');
}
?>