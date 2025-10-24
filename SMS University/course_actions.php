<?php
require_once 'config.php';

// Handle GET requests for course data
if (isset($_GET['get_course'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $course = $stmt->fetch();
        if ($course) {
            header('Content-Type: application/json');
            echo json_encode($course);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Course not found']);
        }
        exit;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle course operations (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action === 'add_course') {
            $stmt = $pdo->prepare("INSERT INTO courses (code, name, instructor, credits, enrolled) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['code'],
                $_POST['name'],
                $_POST['instructor'],
                $_POST['credits'],
                $_POST['enrolled']
            ]);
            $message = 'Course added successfully!';
        } elseif ($action === 'edit_course') {
            $stmt = $pdo->prepare("UPDATE courses SET code = ?, name = ?, instructor = ?, credits = ?, enrolled = ? WHERE id = ?");
            $stmt->execute([
                $_POST['code'],
                $_POST['name'],
                $_POST['instructor'],
                $_POST['credits'],
                $_POST['enrolled'],
                $_POST['id']
            ]);
            $message = 'Course updated successfully!';
        } elseif ($action === 'delete_course') {
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Course deleted successfully!';
        }
        
        // Redirect back to the courses page with success message
        header("Location: index.php?tab=courses&message=" . urlencode($message));
        exit;
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
        // Redirect back with error message
        header("Location: index.php?tab=courses&error=" . urlencode($error));
        exit;
    }
}
?>
