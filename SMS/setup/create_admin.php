<?php
// setup/create_admin.php
// RUN THIS FILE *ONE TIME* in your browser.
// After you run it, DELETE THIS ENTIRE 'setup' FOLDER!

// The path to db_config.php is now different
require '../src/core/db_config.php';

echo "Attempting to create admin user...<br>";

// --- Step 1: Add the 'password' column if it doesn't exist ---
try {
    $pdo->exec("ALTER TABLE Teachers ADD COLUMN password VARCHAR(255) NOT NULL AFTER email;");
    echo "SUCCESS: Added 'password' column to 'Teachers' table.<br>";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // SQLSTATE[42S21]: Column already exists
        echo "INFO: 'password' column already exists. Skipping.<br>";
    } else {
        echo "ERROR adding column: " . $e->getMessage() . "<br>";
    }
}

// --- Step 2: Create the admin user ---
$admin_email = 'admin@school.com';
$admin_pass = 'admin123'; // The password you will use to log in
$hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
$admin_first = 'Admin';
$admin_last = 'User';
$hire_date = date('Y-m-d');

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT * FROM Teachers WHERE email = ?");
    $stmt->execute([$admin_email]);
    
    if ($stmt->fetch()) {
        echo "INFO: Admin user 'admin@school.com' already exists.<br>";
    } else {
        // Insert the new admin user
        $stmt = $pdo->prepare(
            "INSERT INTO Teachers (first_name, last_name, email, password, hire_date) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$admin_first, $admin_last, $admin_email, $hashed_password, $hire_date]);
        echo "SUCCESS: Created admin user!<br>";
    }

    echo "<hr>";
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now log in at <strong>public/index.php</strong> with:</p>";
    echo "<p><strong>Email:</strong> " . $admin_email . "</p>";
    echo "<p><strong>Password:</strong> " . $admin_pass . "</p>";
    echo "<h2 style='color: red;'>IMPORTANT: DELETE THIS 'setup' FOLDER NOW!</h2>";

} catch (PDOException $e) {
    echo "ERROR creating admin user: " . $e->getMessage() . "<br>";
}

?>
