<?php
require_once 'database/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Error message placeholder
$errorMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $errorMessage = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome Back</h1>
            <p>Log in to access your account</p>
        </div>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Log In</button>
        </form>
        
        <div class="links">
            <a href="signup.php">Don't have an account? Sign Up</a>
            <br>
            <a href="#">Forgot Password?</a>
        </div>
    </div>
</body>
</html>