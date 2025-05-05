<?php
require_once 'database/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $fullName = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    
    if ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match';
    } else {
        $result = registerUser($fullName, $email, $password);
        
        if ($result['success']) {
            // Auto login after registration
            loginUser($email, $password);
            header('Location: dashboard.php');
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Account</h1>
            <p>Sign up to get started</p>
        </div>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <form action="signup.php" method="post">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Log In</a>
        </div>
    </div>
</body>
</html>