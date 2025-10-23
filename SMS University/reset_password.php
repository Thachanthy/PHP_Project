<?php
require_once 'auth_config.php';

// Redirect if already logged in
redirectIfLoggedIn();

$message = '';
$error = '';

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    // Verify token
    try {
        $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = 'Invalid reset token.';
        } elseif (strtotime($user['reset_expires']) < time()) {
            $error = 'Reset token has expired.';
        } else {
            // Handle password reset form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($password) || empty($confirm_password)) {
                    $error = 'Please fill in all fields.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Passwords do not match.';
                } elseif (!validatePassword($password)) {
                    $error = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.';
                } else {
                    try {
                        // Update password and clear token
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
                        $stmt->execute([$password_hash, $token]);
                        
                        $message = 'Your password has been reset successfully. You can now log in with your new password.';
                    } catch(PDOException $e) {
                        $error = 'An error occurred while resetting your password. Please try again.';
                    }
                }
            }
        }
    } catch(PDOException $e) {
        $error = 'An error occurred. Please try again later.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-lock-open text-blue-600 text-4xl mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
            <p class="text-gray-600">Create a new password for your account</p>
        </div>
        
        <?php if (!empty($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($error) && empty($message)): ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input type="password" id="password" name="password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">At least 8 characters with uppercase, lowercase, and numbers</p>
            </div>
            
            <div class="mb-6">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Reset Password
            </button>
        </form>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
        <div class="mt-6 text-center">
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                Go to Login
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>