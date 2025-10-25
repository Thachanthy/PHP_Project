<?php
// src/templates/header.php
// This is the top half of your master page.

// Run the security check. If not logged in, this will redirect.
require_once __DIR__ . '/../core/session_check.php';

// Get the current page from the router ($page is set in public/index.php)
$current_page = $page ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title can be dynamic -->
    <title>Admin Panel - <?php echo htmlspecialchars(ucfirst($current_page)); ?></title>
    <!-- The CSS path is now relative to the public folder -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Main Navigation Sidebar (UPDATED with all new links) -->
        <nav class="sidebar">
            <h3>SMS Admin</h3>
            <ul class="sidebar-nav">
                <!-- We use PHP to make the current page 'active' -->
                <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <a href="index.php?page=dashboard">Dashboard</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_students') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_students">Students</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_parents') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_parents">Parents</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_teachers') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_teachers">Teachers</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_classes') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_classes">Classes</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_subjects') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_subjects">Subjects</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_exams') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_exams">Exams</a>
                </li>
                <li class="<?php echo ($current_page == 'manage_results') ? 'active' : ''; ?>">
                    <a href="index.php?page=manage_results">Results</a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <!-- Logout link -->
                <a href="index.php?page=logout" class="btn btn-danger">Logout</a>
            </div>
        </nav>

        <!-- Main Content Area (this DIV is closed in footer.php) -->
        <main class="main-content">
            <header class="main-header">
                <h2><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $current_page))); ?></h2>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?>
                </div>
            </header>

