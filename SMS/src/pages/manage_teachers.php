<?php
// src/pages/manage_teachers.php
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle POST request to add a new teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO Teachers (first_name, last_name, email, password, phone_number, hire_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $password, // Store hashed password
            $_POST['phone_number'],
            $_POST['hire_date']
        ]);
        $message = "Teacher added successfully! They can now log in with the password you set.";
        $action = 'list'; // Go back to list view
    } catch (PDOException $e) {
        $message = "Error adding teacher: " . $e->getMessage();
    }
}

// Fetch all teachers for the list view
if ($action == 'list') {
    $stmt = $pdo->query("SELECT * FROM Teachers ORDER BY last_name, first_name");
    $teachers = $stmt->fetchAll();
}
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Add New Teacher</h3>
        <form method="POST" action="index.php?page=manage_teachers&action=add" class="data-form">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Phone Number</label><input type="text" name="phone_number"></div>
            <div class="form-group"><label>Hire Date</label><input type="date" name="hire_date" required></div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Teacher</button>
                <a href="index.php?page=manage_teachers" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_teachers&action=add" class="btn">Add New Teacher</a></div>
        <h3>All Teachers</h3>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Hire Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                    <td><?php echo htmlspecialchars($teacher['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($teacher['hire_date']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
