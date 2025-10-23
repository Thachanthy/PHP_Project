<?php
require_once 'auth_config.php';

// Destroy session and redirect to login
destroySession();
header('Location: login.php');
exit();
?>
