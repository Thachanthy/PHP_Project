<?php
// src/pages/manage_students.php
// This is the content-only file for managing students.

$action = $_GET['action'] ?? 'list';
$message = '';

// Handle POST request to add a new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO Students (first_name, last_name, date_of_birth, enrollment_date, address, class_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['date_of_birth'],
            $_POST['enrollment_date'],
            $_POST['address'],
            $_POST['class_id']
        ]);
        $message = "Student added successfully!";
        $action = 'list'; // Go back to list view
    } catch (PDOException $e) {
        $message = "Error adding student: " . $e->getMessage();
    }
}

// Fetch all students for the list view
if ($action == 'list') {
    $stmt = $pdo->query("SELECT s.*, c.class_name FROM Students s LEFT JOIN Classes c ON s.class_id = c.class_id ORDER BY s.last_name, s.first_name");
    $students = $stmt->fetchAll();
}

// Fetch all classes for the dropdown
$class_stmt = $pdo->query("SELECT * FROM Classes ORDER BY class_name");
$classes = $class_stmt->fetchAll();

?>

<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <!-- "Create" Form -->
        <h3>Add New Student</h3>
        <form method="POST" action="index.php?page=manage_students&action=add" class="data-form">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="enrollment_date">Enrollment Date</label>
                <input type="date" name="enrollment_date" required>
            </div>
            <div class="form-group">
                <label for="class_id">Class</label>
                <select name="class_id" required>
                    <option value="">Select a Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['class_id']; ?>">
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Student</button>
                <a href="index.php?page=manage_students" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

    <?php else: ?>
        <!-- "Read" List -->
        <div class="page-actions">
            <a href="index.php?page=manage_students&action=add" class="btn">Add New Student</a>
        </div>
        <h3>All Students</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Enrollment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($student['enrollment_date']); ?></td>
                    <td>
                        <a href="#" class="btn-sm">Edit</a>
                        <a href="#" class="btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                <tr>
                    <td colspan="4">No students found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
