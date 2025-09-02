<?php
include 'config.php';

function registerUser($username, $email, $password, $confirm_password) {
    global $conn;
    
    if (empty($username) || empty($email) || empty($password)) {
        return 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        return 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        return 'Password must be at least 6 characters';
    }
    
    $check_user = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $check_user);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        return 'Username or email already exists';
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_user = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_user);
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = 'Registration successful! Please log in.';
        return true;
    } else {
        return 'Registration failed. Please try again.';
    }
}

function loginUser($username, $password) {
    global $conn;
    
    if (empty($username) || empty($password)) {
        return 'Please enter both username and password';
    }
    
    $get_user = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($conn, $get_user);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            return true;
        } else {
            return 'Invalid password';
        }
    } else {
        return 'User not found';
    }
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: notes.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
