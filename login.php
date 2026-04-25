<?php
// login.php

session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Helper function to send JSON response and exit
function send_json_response($status, $message) {
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response('error', 'Invalid request method. Use POST.');
}

// Get and sanitize input
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    send_json_response('error', 'Email and password are required.');
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id, password, failed_attempts, lockout_time FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    send_json_response('error', 'Invalid email or password.');
}

// Check if account is currently locked
if (!empty($user['lockout_time'])) {
    $lockout_time = new DateTime($user['lockout_time']);
    $current_time = new DateTime();
    
    if ($current_time < $lockout_time) {
        $interval = $current_time->diff($lockout_time);
        $hours = $interval->h + ($interval->days * 24);
        $minutes = $interval->i;
        
        $timeLeft = '';
        if ($hours > 0) $timeLeft .= $hours . " hour(s) and ";
        $timeLeft .= $minutes . " minute(s)";
        
        send_json_response('error', "Account locked due to 5 failed attempts. Please try again in $timeLeft.");
    } else {
        // Lockout expired, reset strikes to allow new attempts
        $resetStmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
        $resetStmt->execute([$user['id']]);
        $user['failed_attempts'] = 0;
    }
}

// Verify password
if (!password_verify($password, $user['password'])) {
    $new_attempts = $user['failed_attempts'] + 1;
    
    if ($new_attempts >= 5) {
        // Trigger 3 hour ban
        $lockout_stamp = date("Y-m-d H:i:s", strtotime("+3 hours"));
        $updateStmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, lockout_time = ? WHERE id = ?");
        $updateStmt->execute([$new_attempts, $lockout_stamp, $user['id']]);
        
        send_json_response('error', "Security Alert: You have failed 5 times. Account locked for 3 hours.");
    } else {
        // Increment strike count
        $updateStmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
        $updateStmt->execute([$new_attempts, $user['id']]);
        
        $attemptsLeft = 5 - $new_attempts;
        send_json_response('error', "Invalid email or password. You have $attemptsLeft attempt(s) left.");
    }
}

// Login successful! Purge all strikes and lockout timers.
$clearStmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
$clearStmt->execute([$user['id']]);

$_SESSION['email']   = $email;
$_SESSION['user_id'] = $user['id'];

send_json_response('success', 'Login successful');