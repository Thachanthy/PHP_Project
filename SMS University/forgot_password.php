<?php
require_once 'auth_config.php';

// Redirect if already logged in
redirectIfLoggedIn();

$message = '';
$error = '';

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token and expiration
                $token = generateResetToken();
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                $stmt->execute([$token, $expires, $email]);
                
                // In a real application, you would send an email here
                // For demo purposes, we'll just show the token
                $message = "Password reset instructions have been sent to your email. For demo purposes, your reset token is: $token";
            } else {
                // Don't reveal if email exists for security
                $message = 'If an account exists with that email, password reset instructions have been sent.';
            }
        } catch(PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-key text-blue-600 text-4xl mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900">Forgot Password</h1>
            <p class="text-gray-600">Enter your email to reset your password</p>
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
        
        <form method="POST" action="">
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" id="email" name="email" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-2">We'll send password reset instructions to this email</p>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Send Reset Instructions
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Remember your password? 
                <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">
                    Sign in
                </a>
            </p>
        </div>
    </div>
</body>
</html>
