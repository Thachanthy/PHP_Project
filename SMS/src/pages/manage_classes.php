<?php
// src/pages/manage_classes.php
$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO Classes (class_name, class_teacher_id) VALUES (?, ?)");
        $stmt->execute([$_POST['class_name'], $_POST['teacher_id']]);
        $message = "Class added successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $message = "Error adding class: " . $e->getMessage();
    }
}

if ($action == 'list') {
    $stmt = $pdo->query("
        SELECT c.*, t.first_name, t.last_name 
        FROM Classes c 
        LEFT JOIN Teachers t ON c.class_teacher_id = t.teacher_id 
        ORDER BY c.class_name
    ");
    $classes = $stmt->fetchAll();
}
// Fetch teachers for the dropdown
$teachers = $pdo->query("SELECT teacher_id, first_name, last_name FROM Teachers ORDER BY last_name")->fetchAll();
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Add New Class</h3>
        <form method="POST" action="index.php?page=manage_classes&action=add" class="data-form">
            <div class="form-group"><label>Class Name</label><input type="text" name="class_name" required placeholder="e.g., Grade 10-A"></div>
            <div class="form-group">
                <label>Class Teacher</label>
                <select name="teacher_id">
                    <option value="">Select a Teacher (Optional)</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['teacher_id']; ?>">
                            <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Class</button>
                <a href="index.php?page=manage_classes" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_classes&action=add" class="btn">Add New Class</a></div>
        <h3>All Classes</h3>
        <table class="data-table">
            <thead><tr><th>Class Name</th><th>Class Teacher</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($class['first_name'] . ' ' . $class['last_name']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

