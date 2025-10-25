<?php
// src/pages/login.php
// This page is loaded by index.php if the user is not logged in.

$login_error = '';

// Check if the user is *already* logged in
if (isset($_SESSION['teacher_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $login_error = 'Email and password are required.';
    } else {
        try {
            // Find the teacher/admin by email
            $stmt = $pdo->prepare("SELECT * FROM Teachers WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify the user and password
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct! Store user info in session
                $_SESSION['teacher_id'] = $user['teacher_id'];
                $_SESSION['teacher_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Redirect to the dashboard
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                $login_error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $login_error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management - Admin Login</title>
    <!-- CSS path is relative to the root -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST" action="index.php?page=login">
            <h2>Admin Login</h2>
            <p>School Management System</p>

            <?php if (!empty($login_error)): ?>
                <div class="error-message"><?php echo $login_error; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
