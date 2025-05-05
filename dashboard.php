<?php

require_once 'database/auth.php';

// Set page title
$pageTitle = 'Dashboard';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user
$currentUser = getCurrentUser();

// Get dashboard data
$dashboardData = getDashboardData();
$counts = $dashboardData['counts'];
$recentActivities = $dashboardData['recent_activities'];

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutUser();
    header('Location: login.php');
    exit;
}

// Format activity icons and text
$activityIcons = [
    'project_launch' => '🚀',
    'task_completion' => '✅',
    'team_update' => '👥',
    'report_submission' => '📄',
    'account_creation' => '🔑',
    'login' => '🔒',
    'logout' => '🚪'
];

// Format activity des
function formatActivityDescription($activity) {
    switch ($activity['activity_type']) {
        case 'project_launch':
            return $activity['description'];
        case 'task_completion':
            return $activity['description'];
        case 'team_update':
            return $activity['description'];
        case 'report_submission':
            return $activity['description'];
        case 'account_creation':
            return 'Account was created';
        case 'login':
            return 'Successful login';
        case 'logout':
            return 'User logged out';
        default:
            return $activity['description'];
    }
}

// Format timestamps
function timeAgo($timestamp) {
    $timestamp = strtotime($timestamp);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 172800) {
        return "Yesterday";
    } else {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}

// Get user initials for avatar
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    
    return $initials;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <nav class="navbar">
        <a href="#" class="navbar-brand">Task Manager</a>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="#">Projects</a></li>
            <li><a href="#">Tasks</a></li>
            <li><a href="#">Reports</a></li>
        </ul>
        <div class="user-info dropdown">
            <div class="user-avatar"><?php echo getInitials($currentUser['full_name']); ?></div>
            <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
            <div class="dropdown-content">
                <a href="#">Profile</a>
                <a href="#">Settings</a>
                <a href="dashboard.php?action=logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?></p>
            </div>
            <div class="dashboard-actions">
                <a href="#" class="btn">+ New Project</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Projects</h3>
                <p><?php echo $counts['projects']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Tasks</h3>
                <p><?php echo $counts['active_tasks']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <p><?php echo $counts['completed_tasks']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Team Members</h3>
                <p><?php echo $counts['team_members']; ?></p>
            </div>
        </div>

        <div class="recent-activity">
            <h2 class="section-title">Recent Activity</h2>
            
            <?php if (empty($recentActivities)): ?>
                <p>No recent activities found.</p>
            <?php else: ?>
                <?php foreach ($recentActivities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php echo $activityIcons[$activity['activity_type']] ?? '📌'; ?>
                        </div>
                        <div class="activity-info">
                            <h4><?php echo htmlspecialchars(formatActivityDescription($activity)); ?></h4>
                            <p><?php echo timeAgo($activity['created_at']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>