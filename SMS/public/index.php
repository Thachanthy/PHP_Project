<?php
// public/index.php
// This is the single entry point for your entire application (Front Controller)

// 1. Start the session and include the database connection
try {
    require '../src/core/db_config.php';
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 2. Figure out which page to load
$page = $_GET['page'] ?? 'login';

// 3. Page Whitelist (UPDATED with all new pages)
// For security, we only allow loading pages that are in this list.
$allowed_pages = [
    'login',
    'logout',
    'dashboard',
    'manage_students',
    'manage_teachers',
    'manage_parents',
    'manage_classes',
    'manage_subjects',
    'manage_exams',
    'manage_results',
    'manage_noticeboard',
    'manage_notifications',
    'manage_promotions'
];

// 4. Handle Login/Logout pages (which don't need the template)
if ($page == 'login') {
    require '../src/pages/login.php';
    exit; // Stop here
}

if ($page == 'logout') {
    require '../src/pages/logout.php';
    exit; // Stop here
}

// 5. Check if the requested page is in our whitelist
if (in_array($page, $allowed_pages)) {
    
    // 6. Load the Master Page
    // The header includes the session check, so no one can access
    // these pages without being logged in.
    require '../src/templates/header.php';
    
    // Load the specific page content
    require '../src/pages/' . $page . '.php';
    
    // Load the footer
    require '../src/templates/footer.php';

} else {
    // Page not found or not allowed
    http_response_code(404);
    require '../src/templates/header.php'; // Load header
    echo "<div class='content-body'><h3>404 - Page Not Found</h3><p>The page you requested could not be found.</p></div>";
    require '../src/templates/footer.php'; // Load footer
}
?>

