<?php
/**
 * Authentication System
 * 
 * This file handles user authentication including login, registration,
 * and session management.
 */

session_start();

require_once 'database-connection.php';

/**
 * Register a new user
 * 
 * @param string $fullName User's full name
 * @param string $email User's email
 * @param string $password User's password 
 * @return array Status and message or user data
 */
function registerUser($fullName, $email, $password) {
    // Validate input
    if (empty($fullName) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }
    
    // Check if email already exists
    $existingUser = fetchRow("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        return ['success' => false, 'message' => 'Email already in use'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $userId = insert(
        "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)",
        [$fullName, $email, $hashedPassword]
    );
    
    if ($userId) {
        // Create a welcome activity
        insert(
            "INSERT INTO activities (user_id, activity_type, description) VALUES (?, ?, ?)",
            [$userId, 'account_creation', 'Account created']
        );
        
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'full_name' => $fullName,
                'email' => $email
            ]
        ];
    } else {
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Log in a user
 * 
 * @param string $email User's email
 * @param string $password User's password
 * @return array Status and message or user data
 */
function loginUser($email, $password) {
    // Validate input
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }
    
    // Get user from database
    $user = fetchRow("SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        // Add login activity
        insert(
            "INSERT INTO activities (user_id, activity_type, description) VALUES (?, ?, ?)",
            [$user['id'], 'login', 'User logged in']
        );
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email']
            ]
        ];
    } else {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email']
        ];
    }
    return null;
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logoutUser() {
    // Add logout activity if user is logged in
    if (isset($_SESSION['user_id'])) {
        insert(
            "INSERT INTO activities (user_id, activity_type, description) VALUES (?, ?, ?)",
            [$_SESSION['user_id'], 'logout', 'User logged out']
        );
    }
    
    // Clear session
    $_SESSION = [];
    
    // If using session cookies, delete the cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Get dashboard data for the current user
 * 
 * @return array Dashboard data including counts and recent activities
 */
function getDashboardData() {
    $userId = $_SESSION['user_id'];
    
    // Get counts
    $projectCount = fetchRow("SELECT COUNT(*) as count FROM projects WHERE user_id = ?", [$userId])['count'];
    $activeTasks = fetchRow("SELECT COUNT(*) as count FROM tasks t 
                            JOIN projects p ON t.project_id = p.id 
                            WHERE p.user_id = ? AND t.status = 'in_progress'", [$userId])['count'];
    $completedTasks = fetchRow("SELECT COUNT(*) as count FROM tasks t 
                              JOIN projects p ON t.project_id = p.id 
                              WHERE p.user_id = ? AND t.status = 'completed'", [$userId])['count'];
    
    
    $teamMembers = 8; 
    
    // Get recent activities
    $recentActivities = fetchAll("SELECT * FROM activities 
                                WHERE user_id = ? 
                                ORDER BY created_at DESC 
                                LIMIT 4", [$userId]);
    
    return [
        'counts' => [
            'projects' => $projectCount,
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'team_members' => $teamMembers
        ],
        'recent_activities' => $recentActivities
    ];
}

// Handle form submissions based on action parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = [];
    
    switch ($action) {
        case 'register':
            $fullName = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm-password'] ?? '';
            
            if ($password !== $confirmPassword) {
                $response = ['success' => false, 'message' => 'Passwords do not match'];
            } else {
                $response = registerUser($fullName, $email, $password);
                
                if ($response['success']) {
                    // Auto login after registration
                    loginUser($email, $password);
                    header('Location: dashboard.php');
                    exit;
                }
            }
            break;
            
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $response = loginUser($email, $password);
            
            if ($response['success']) {
                header('Location: dashboard.php');
                exit;
            }
            break;
            
        case 'logout':
            logoutUser();
            header('Location: login.php');
            exit;
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
    }
}
?>