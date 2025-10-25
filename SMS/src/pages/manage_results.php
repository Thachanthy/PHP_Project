<?php

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    try {
        // --- FIX 1: Changed 'exam_id' to 'exam_schedule_id' ---
        $stmt = $pdo->prepare("INSERT INTO Results (student_id, exam_schedule_id, score) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['student_id'],
            $_POST['exam_id'], // This is correct, it comes from the form
            $_POST['score']
        ]);
        $message = "Result added successfully!";
        $action = 'list';
    } catch (PDOException $e) {
        $message = "Error adding result: ". $e->getMessage();
    }
}

if ($action == 'list') {
    // This is Line 22 (where the error was)
    $stmt = $pdo->query("
        SELECT r.*, s.first_name, s.last_name, e.exam_name, e.max_marks, sub.subject_name
        FROM Results r
        JOIN Students s ON r.student_id = s.student_id
        -- --- FIX 2: Changed 'r.exam_id' to 'r.exam_schedule_id' ---
        JOIN Exam_Schedule e ON r.exam_schedule_id = e.exam_id
        JOIN Subjects sub ON e.subject_id = sub.subject_id
        ORDER BY s.last_name, e.exam_date DESC
    ");
    $results = $stmt->fetchAll();
}
// Fetch data for dropdowns
// This query is correct and does not need changes
$students = $pdo->query("SELECT student_id, first_name, last_name FROM Students ORDER BY last_name")->fetchAll();
$exams = $pdo->query("SELECT e.exam_id, e.exam_name, s.subject_name FROM Exam_Schedule e JOIN Subjects s ON e.subject_id = s.subject_id ORDER BY e.exam_date DESC")->fetchAll();
?>
<div class="content-body">
    <?php if (!empty($message)): ?>
        <div class="success-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'add'): ?>
        <h3>Add Student Result</h3>
        <form method="POST" action="index.php?page=manage_results&action=add" class="data-form">
            <div class="form-group">
                <label>Student</label>
                <select name="student_id" required>
                    <option value="">Select a Student</option>
                    <?php foreach ($students as $student): ?><option value="<?php echo $student['student_id']; ?>"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Exam</label>
                <select name="exam_id" required>
                    <option value="">Select an Exam</option>
                    <!-- This line is correct -->
                    <?php foreach ($exams as $exam): ?><option value="<?php echo $exam['exam_id']; ?>"><?php echo htmlspecialchars($exam['exam_name'] . ' - ' . $exam['subject_name']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Marks Obtained</label><input type="number" name="score" required></div>
            <div class="form-actions">
                <button type="submit" class="btn">Save Result</button>
                <a href="index.php?page=manage_results" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="page-actions"><a href="index.php?page=manage_results&action=add" class="btn">Add New Result</a></div>
        <h3>All Student Results</h3>
        <table class="data-table">
            <thead><tr><th>Student</th><th>Exam</th><th>Subject</th><th>Marks</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                    <!-- This line is correct -->
                    <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                    <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                    <td><?php echo htmlspecialchars($result['score']); ?> / <?php echo htmlspecialchars($result['max_marks']); ?></td>
                    <td><a href="#" class="btn-sm">Edit</a><a href="#" class="btn-sm btn-danger">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

