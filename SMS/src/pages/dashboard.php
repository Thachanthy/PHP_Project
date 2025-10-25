<?php
// src/pages/dashboard.php
// This file is loaded by index.php and wrapped by the header/footer.
// Notice there is no HTML, HEAD, or BODY tag here.

// Fetch some quick stats for the dashboard
try {
    $student_count = $pdo->query("SELECT COUNT(*) FROM Students")->fetchColumn();
    $teacher_count = $pdo->query("SELECT COUNT(*) FROM Teachers")->fetchColumn();
    $class_count = $pdo->query("SELECT COUNT(*) FROM Classes")->fetchColumn();
} catch (PDOException $e) {
    // Handle error
    $student_count = 'N/A';
    $teacher_count = 'N/A';
    $class_count = 'N/A';
}
?>

<!-- This is the content for the page -->
<div class="content-body">
    <div class="stat-cards">
        <div class="stat-card">
            <h4>Total Students</h4>
            <p><?php echo $student_count; ?></p>
        </div>
        <div class="stat-card">
            <h4>Total Teachers</h4>
            <p><?php echo $teacher_count; ?></p>
        </div>
        <div class="stat-card">
            <h4>Total Classes</h4>
            <p><?php echo $class_count; ?></p>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <a href="index.php?page=manage_students&action=add" class="btn">Add New Student</a>
        <a href="index.php?page=manage_teachers&action=add" class="btn">Add New Teacher</a>
        <a href="index.php?page=manage_noticeboard&action=add" class="btn">Post a Notice</a>

        
    </div>
</div>
