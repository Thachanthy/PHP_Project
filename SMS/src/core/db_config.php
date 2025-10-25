<?php
// src/core/db_config.php
// This file is now in a secure location.

// Database credentials
$host = '127.0.0.1'; // or 'localhost'
$db   = 'school_management_system';
$user = 'root'; // Your database username
$pass = '';     // Your database password
$charset = 'utf8mb4';

// PDO connection string (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create the PDO instance
// No try/catch here, we let the index.php handle it
$pdo = new PDO($dsn, $user, $pass, $options);

// Start the session for all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
