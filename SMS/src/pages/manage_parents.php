<?php
// src/pages/manage_parents.php
$action = $_GET['action'] ?? 'list';
$message = '';

// Handle POST request to add a new parent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO Parents (first_name, last_name, email, phone_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone_number']
        ]);
        $parent_id = $pdo->lastInsertId();
        $student_id = $_POST['student_id'];

        // Link parent to student in the junction table
        $stmt = $pdo->prepare("INSERT INTO Student_Parent (student_id, parent_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $parent_id]);
        
        $pdo->commit();
        $message = "Parent added and linked successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = "Error adding parent: " . $e->getMessage();
    }
}

// Fetch all parents for the list view
if ($action == 'list') {
    // This query also shows which student they are linked to
    $stmt = $pdo->query("
        SELECT p.*, s.first_name as student_first, s.last_name as student_last
        FROM Parents p
        LEFT JOIN Student_Parent sp ON p.parent_id = sp.parent_id
        LEFT JOIN Students s ON sp.student_id = s.student_id
        ORDER BY p.last_name
    ");
    $parents = $stmt->fetchAll();
}
// Fetch students for the dropdown
$students = $pdo->query("SELECT student_id, first_name, last_name FROM Students ORDER BY last_name")->fetchAll();
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Add New Parent</h3>
        <form method="POST" action="index.php?page=manage_parents&action=add" class="data-form">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email"></div>
            <div class="form-group"><label>Phone Number</label><input type="text" name="phone_number" required></div>
            <div class="form-group">
                <label>Link to Student</label>
                <select name="student_id" required>
                    <option value="">Select a Student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['student_id']; ?>">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Parent</button>
                <a href="index.php?page=manage_parents" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_parents&action=add" class="btn">Add New Parent</a></div>
        <h3>All Parents</h3>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Linked Student</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($parents as $parent): ?>
                <tr>
                    <td><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($parent['email']); ?></td>
                    <td><?php echo htmlspecialchars($parent['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($parent['student_first'] . ' ' . $parent['student_last']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
