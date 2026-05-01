<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'connect.php';

// Debug
error_log("auth.php called - " . date('Y-m-d H:i:s'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$formType = $_POST['form_type'] ?? '';

if ($formType === 'signup') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        header("Location: index.html?message=" . urlencode("All fields are required.") . "&type=error");
        exit();
    }

    // Check if email exists
    $checkStmt = $link->prepare("SELECT id FROM gs_details WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        header("Location: index.html?message=" . urlencode("This email is already registered.") . "&type=error");
        exit();
    }
    $checkStmt->close();

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $link->prepare("INSERT INTO gs_details (name, email, password) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $name, $email, $hashedPassword);
    
    if ($insertStmt->execute()) {
        $insertStmt->close();
        $link->close();
        header("Location: index.html?message=" . urlencode("Account created successfully!") . "&type=success");
        exit();
    } else {
        $error = $insertStmt->error;
        $insertStmt->close();
        $link->close();
        header("Location: index.html?message=" . urlencode("Registration failed: " . $error) . "&type=error");
        exit();
    }
}

// Sign In handling
if ($formType === 'signin') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: index.html?message=" . urlencode("Please enter email and password.") . "&type=error");
        exit();
    }

    $stmt = $link->prepare("SELECT id, name, email, password FROM gs_details WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $stmt->close();
            $link->close();
            header("Location: residents.php");
            exit();
        }
    }
    
    $stmt->close();
    $link->close();
    header("Location: index.html?message=" . urlencode("Invalid email or password.") . "&type=error");
    exit();
}

$link->close();
header("Location: index.html");
exit();
?>