<?php
require_once 'config.php';

// Handle GET requests for faculty data
if (isset($_GET['get_faculty'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM faculty WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $faculty = $stmt->fetch();
        if ($faculty) {
            header('Content-Type: application/json');
            echo json_encode($faculty);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Faculty member not found']);
        }
        exit;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle faculty operations (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action === 'add_faculty') {
            $stmt = $pdo->prepare("INSERT INTO faculty (name, department, position, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['department'],
                $_POST['position'],
                $_POST['email']
            ]);
            $message = 'Faculty member added successfully!';
        } elseif ($action === 'edit_faculty') {
            $stmt = $pdo->prepare("UPDATE faculty SET name = ?, department = ?, position = ?, email = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['department'],
                $_POST['position'],
                $_POST['email'],
                $_POST['id']
            ]);
            $message = 'Faculty member updated successfully!';
        } elseif ($action === 'delete_faculty') {
            $stmt = $pdo->prepare("DELETE FROM faculty WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Faculty member deleted successfully!';
        }
        
        // Redirect back to the faculty page with success message
        header("Location: index.php?tab=faculty&message=" . urlencode($message));
        exit;
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
        // Redirect back with error message
        header("Location: index.php?tab=faculty&error=" . urlencode($error));
        exit;
    }
}
?>
