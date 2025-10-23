<?php
require_once 'config.php';

// Handle student operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO students (name, email, program, year, gpa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['program'],
                $_POST['year'],
                $_POST['gpa']
            ]);
            echo json_encode(['success' => true, 'message' => 'Student added successfully']);
        }
        
        elseif ($action === 'update') {
            $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, program = ?, year = ?, gpa = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['program'],
                $_POST['year'],
                $_POST['gpa'],
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        }
        
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Handle GET requests for student data
if (isset($_GET['get_student'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch();
    echo json_encode($student);
    exit;
}
?>
