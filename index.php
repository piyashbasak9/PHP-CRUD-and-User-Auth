<?php
include 'config.php';
include 'auth.php';

redirectIfLoggedIn();

$register_error = '';
$login_error = '';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $result = registerUser($username, $email, $password, $confirm_password);
    if ($result !== true) {
        $register_error = $result;
    } else {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $result = loginUser($username, $password);
    if ($result !== true) {
        $login_error = $result;
    } else {
        header('Location: notes.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iNotes - Login & Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f5f7ff;
            --accent-color: #ff6584;
            --dark-color: #2a2a72;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 900px;
            max-width: 95%;
        }
        
        .auth-left {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .auth-right {
            padding: 40px;
        }
        
        .brand-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .auth-form-toggle {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .auth-form-toggle button {
            flex: 1;
            padding: 12px;
            background: none;
            border: none;
            font-weight: 600;
            color: #777;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .auth-form-toggle button.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-primary:hover {
            background-color: #554fd8;
            border-color: #554fd8;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25);
        }
        
        .alert {
            padding: 10px 15px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .auth-left {
                display: none;
            }
            
            .auth-container {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="row g-0">
            <div class="col-md-6 auth-left">
                <i class="fas fa-sticky-note brand-icon"></i>
                <h2>Welcome to iNotes</h2>
                <p>Your personal note-taking application. Organize your thoughts and ideas in one place.</p>
            </div>
            <div class="col-md-6 auth-right">
                <div class="auth-form-toggle">
                    <button id="login-toggle" class="active">Login</button>
                    <button id="register-toggle">Register</button>
                </div>
                
                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success_msg']; 
                        unset($_SESSION['success_msg']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form id="login-form" class="auth-form active" method="POST" action="">
                    <h3 class="mb-4">Sign In</h3>
                    
                    <?php if (!empty($login_error)): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="login-username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="login-username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="login-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="login-password" name="password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
                
                <form id="register-form" class="auth-form" method="POST" action="">
                    <h3 class="mb-4">Create Account</h3>
                    
                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-danger"><?php echo $register_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="register-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="register-username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="register-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="register-email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="register-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="register-password" name="password" required>
                        <div class="form-text">Must be at least 6 characters long</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm-password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginToggle = document.getElementById('login-toggle');
            const registerToggle = document.getElementById('register-toggle');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            loginToggle.addEventListener('click', function() {
                loginToggle.classList.add('active');
                registerToggle.classList.remove('active');
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
            });
            
            registerToggle.addEventListener('click', function() {
                registerToggle.classList.add('active');
                loginToggle.classList.remove('active');
                registerForm.classList.add('active');
                loginForm.classList.remove('active');
            });
            
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const inputs = this.querySelectorAll('input[required]');
                    let valid = true;
                    
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            valid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Please fill in all required fields');
                    }
                });
            });
        });
    </script>
</body>
</html>
