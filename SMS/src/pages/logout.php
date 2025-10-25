<?php
// src/pages/logout.php
// This is included by index.php

// $_SESSION is already started by db_config.php
$_SESSION = array();
session_destroy();

// Redirect to the login page
header('Location: index.php?page=login');
exit;
?>