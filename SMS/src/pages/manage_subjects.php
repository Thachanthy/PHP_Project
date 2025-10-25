<?php
// src/pages/manage_subjects.php
$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO Subjects (subject_name) VALUES (?)");
        $stmt->execute([$_POST['subject_name']]);
        $message = "Subject added successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $message = "Error adding subject: " . $e->getMessage();
    }
}

if ($action == 'list') {
    $stmt = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name");
    $subjects = $stmt->fetchAll();
}
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Add New Subject</h3>
        <form method="POST" action="index.php?page=manage_subjects&action=add" class="data-form">
            <div class="form-group"><label>Subject Name</label><input type="text" name="subject_name" required placeholder="e.g., Mathematics 101"></div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Subject</button>
                <a href="index.php?page=manage_subjects" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_subjects&action=add" class="btn">Add New Subject</a></div>
        <h3>All Subjects</h3>
        <table class="data-table">
            <thead><tr><th>Subject Name</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
