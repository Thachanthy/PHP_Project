<?php
// src/pages/manage_exams.php
$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        $stmt = $pdo->prepare("INSERT INTO Exam_Schedule (exam_name, class_id, subject_id, exam_date, max_marks) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['exam_name'],
            $_POST['class_id'],
            $_POST['subject_id'],
            $_POST['exam_date'],
            $_POST['max_marks']
        ]);
        $message = "Exam scheduled successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $message = "Error scheduling exam: " . $e->getMessage();
    }
}

if ($action == 'list') {
    $stmt = $pdo->query("
        SELECT e.*, c.class_name, s.subject_name 
        FROM Exam_Schedule e
        JOIN Classes c ON e.class_id = c.class_id
        JOIN Subjects s ON e.subject_id = s.subject_id
        ORDER BY e.exam_date DESC
    ");
    $exams = $stmt->fetchAll();
}
// Fetch data for dropdowns
$classes = $pdo->query("SELECT * FROM Classes ORDER BY class_name")->fetchAll();
$subjects = $pdo->query("SELECT * FROM Subjects ORDER BY subject_name")->fetchAll();
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Schedule New Exam</h3>
        <form method="POST" action="index.php?page=manage_exams&action=add" class="data-form">
            <div class="form-group"><label>Exam Name</label><input type="text" name="exam_name" required placeholder="e.g., Mid-Term Exam"></div>
            <div class="form-group">
                <label>Class</label>
                <select name="class_id" required>
                    <option value="">Select a Class</option>
                    <?php foreach ($classes as $class): ?><option value="<?php echo $class['class_id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <select name="subject_id" required>
                    <option value="">Select a Subject</option>
                    <?php foreach ($subjects as $subject): ?><option value="<?php echo $subject['subject_id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Exam Date</label><input type="date" name="exam_date" required></div>
            <div class="form-group"><label>Max Marks</label><input type="number" name="max_marks" required value="100"></div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Exam</button>
                <a href="index.php?page=manage_exams" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_exams&action=add" class="btn">Schedule New Exam</a></div>
        <h3>All Exams</h3>
        <table class="data-table">
            <thead><tr><th>Exam</th><th>Class</th><th>Subject</th><th>Date</th><th>Max Marks</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                <tr>
                    <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                    <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($exam['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                    <td><?php echo htmlspecialchars($exam['max_marks']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
