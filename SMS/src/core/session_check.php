<?php
// src/core/session_check.php
// This script is included by header.php to protect all admin pages.

// If the user is NOT logged in (session variable not set)
// redirect them to the login page.
if (!isset($_SESSION['teacher_id'])) {
    // We are in a subdirectory, so we need to point to the root index.php
    header('Location: index.php?page=login');
    exit;
}
?>
