<?php
require_once 'config.php';

// Initialize all variables to avoid undefined variable warnings
$activeTab = 'dashboard'; // Default tab
$searchTerm = '';
$message = '';
$error = '';
$students = [];
$courses = [];
$faculty = [];
$totalStudents = 0;
$totalCourses = 0;
$totalFaculty = 0;
$activeEnrollments = 0;
$enrollmentData = [];
$performanceData = [];

// Get active tab from URL parameter
if (isset($_GET['tab'])) {
    $activeTab = $_GET['tab'];
    // Validate tab to prevent security issues
    $validTabs = ['dashboard', 'students', 'courses', 'faculty', 'reports', 'settings'];
    if (!in_array($activeTab, $validTabs)) {
        $activeTab = 'dashboard';
    }
}

// Get search term
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
}

// Get messages
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Handle form submissions for students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add_student') {
            $stmt = $pdo->prepare("INSERT INTO students (name, email, program, year, gpa) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['program'],
                $_POST['year'],
                $_POST['gpa']
            ]);
            $message = 'Student added successfully!';
            header("Location: index.php?tab=students&message=" . urlencode($message));
            exit;
        } elseif ($action === 'edit_student') {
            $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, program = ?, year = ?, gpa = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['email'],
                $_POST['program'],
                $_POST['year'],
                $_POST['gpa'],
                $_POST['id']
            ]);
            $message = 'Student updated successfully!';
            header("Location: index.php?tab=students&message=" . urlencode($message));
            exit;
        } elseif ($action === 'delete_student') {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Student deleted successfully!';
            header("Location: index.php?tab=students&message=" . urlencode($message));
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
        header("Location: index.php?tab=students&error=" . urlencode($error));
        exit;
    }
}

// Handle GET requests for student data
if (isset($_GET['get_student'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $student = $stmt->fetch();
        if ($student) {
            header('Content-Type: application/json');
            echo json_encode($student);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
        }
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Fetch data based on active tab
if ($activeTab === 'students' || $activeTab === 'dashboard') {
    $sql = "SELECT * FROM students";
    if (!empty($searchTerm)) {
        $sql .= " WHERE name LIKE ? OR email LIKE ? OR program LIKE ?";
        $stmt = $pdo->prepare($sql);
        $searchParam = "%$searchTerm%";
        $stmt->execute([$searchParam, $searchParam, $searchParam]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $students = $stmt->fetchAll();
    $totalStudents = count($students);
}

if ($activeTab === 'courses' || $activeTab === 'dashboard') {
    $sql = "SELECT * FROM courses";
    if (!empty($searchTerm)) {
        $sql .= " WHERE name LIKE ? OR code LIKE ? OR instructor LIKE ?";
        $stmt = $pdo->prepare($sql);
        $searchParam = "%$searchTerm%";
        $stmt->execute([$searchParam, $searchParam, $searchParam]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $courses = $stmt->fetchAll();
    $totalCourses = count($courses);
}

if ($activeTab === 'faculty' || $activeTab === 'dashboard') {
    $sql = "SELECT * FROM faculty";
    if (!empty($searchTerm)) {
        $sql .= " WHERE name LIKE ? OR department LIKE ? OR email LIKE ?";
        $stmt = $pdo->prepare($sql);
        $searchParam = "%$searchTerm%";
        $stmt->execute([$searchParam, $searchParam, $searchParam]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $faculty = $stmt->fetchAll();
    $totalFaculty = count($faculty);
}

// Calculate total enrollments
foreach ($courses as $course) {
    $activeEnrollments += $course['enrolled'];
}

// For Reports page - generate some sample analytics data
if ($activeTab === 'reports') {
    // Generate enrollment trends data (last 6 months)
    $enrollmentData = [
        ['month' => 'Jan', 'enrollments' => 120],
        ['month' => 'Feb', 'enrollments' => 145],
        ['month' => 'Mar', 'enrollments' => 160],
        ['month' => 'Apr', 'enrollments' => 185],
        ['month' => 'May', 'enrollments' => 200],
        ['month' => 'Jun', 'enrollments' => 220]
    ];

    // Generate performance metrics data (GPA distribution)
    $performanceData = [
        ['gpa_range' => '4.0', 'count' => 15],
        ['gpa_range' => '3.5-3.9', 'count' => 35],
        ['gpa_range' => '3.0-3.4', 'count' => 25],
        ['gpa_range' => '2.5-2.9', 'count' => 15],
        ['gpa_range' => '2.0-2.4', 'count' => 10]
    ];
}

// For Settings page - handle settings updates
if ($activeTab === 'settings' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // In a real application, you would save these settings to a database
    // For now, we'll just show a success message
    $message = 'Settings saved successfully!';
    header("Location: index.php?tab=settings&message=" . urlencode($message));
    exit;
}

// Dashboard statistics
$totalStudents = count($students);
$totalCourses = count($courses);
$totalFaculty = count($faculty);
$activeEnrollments = 0;
foreach ($courses as $course) {
    $activeEnrollments += $course['enrolled'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DUC Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="data:image/jpeg;base64,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" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-university text-blue-600 text-2xl"></i>
                        <h1 class="text-xl font-bold text-gray-900">DUC Management System</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <form method="GET" class="relative">
                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab); ?>">
                        <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>"
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </form>

                    <button class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>

                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">A</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="lg:w-64 flex-shrink-0">
                <nav class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="space-y-2">
                        
                        <a href="?tab=dashboard" class="<?php echo $activeTab === 'dashboard' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-home"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                        <a href="?tab=students" class="<?php echo $activeTab === 'students' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="font-medium">Students</span>
                        </a>
                        <a href="?tab=courses" class="<?php echo $activeTab === 'courses' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-book"></i>
                            <span class="font-medium">Courses</span>
                        </a>
                        <a href="?tab=faculty" class="<?php echo $activeTab === 'faculty' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-users"></i>
                            <span class="font-medium">Faculty</span>
                        </a>
                        <a href="?tab=reports" class="<?php echo $activeTab === 'reports' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-chart-bar"></i>
                            <span class="font-medium">Reports</span>
                        </a>
                        <a href="?tab=settings" class="<?php echo $activeTab === 'settings' ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?> w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-left transition-colors">
                            <i class="fas fa-cog"></i>
                            <span class="font-medium">Settings</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <?php if (!empty($message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <p><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'dashboard'): ?>
                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Overview</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Total Students</p>
                                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($totalStudents); ?></p>
                                        </div>
                                        <div class="p-3 rounded-lg bg-blue-500">
                                            <i class="fas fa-graduation-cap text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Active Courses</p>
                                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($totalCourses); ?></p>
                                        </div>
                                        <div class="p-3 rounded-lg bg-green-500">
                                            <i class="fas fa-book text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Faculty Members</p>
                                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($totalFaculty); ?></p>
                                        </div>
                                        <div class="p-3 rounded-lg bg-purple-500">
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Enrollments</p>
                                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo number_format($activeEnrollments); ?></p>
                                        </div>
                                        <div class="p-3 rounded-lg bg-orange-500">
                                            <i class="fas fa-calendar text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Students</h3>
                                    <div class="space-y-3">
                                        <?php foreach (array_slice($students, 0, 3) as $student): ?>
                                            <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-graduation-cap text-blue-600"></i>
                                                        </div>
                                                        <div>
                                                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($student['name']); ?></h3>
                                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                                                            <div class="flex items-center space-x-2 mt-1">
                                                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                                    <?php echo htmlspecialchars($student['program']); ?>
                                                                </span>
                                                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                                    <?php echo htmlspecialchars($student['year']); ?>
                                                                </span>
                                                                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                                                    GPA: <?php echo $student['gpa']; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button onclick="viewStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                            <i class="fas fa-eye text-sm"></i>
                                                        </button>
                                                        <button onclick="editStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-yellow-600 transition-colors">
                                                            <i class="fas fa-edit text-sm"></i>
                                                        </button>
                                                        <button onclick="deleteStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                            <i class="fas fa-trash text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Courses</h3>
                                    <div class="space-y-3">
                                        <?php foreach (array_slice($courses, 0, 3) as $course): ?>
                                            <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center space-x-2 mb-2">
                                                            <span class="font-mono font-bold text-blue-600"><?php echo htmlspecialchars($course['code']); ?></span>
                                                            <span class="text-sm text-gray-500">• <?php echo $course['credits']; ?> credits</span>
                                                        </div>
                                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($course['name']); ?></h3>
                                                        <p class="text-sm text-gray-600">Instructor: <?php echo htmlspecialchars($course['instructor']); ?></p>
                                                        <div class="mt-2">
                                                            <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                                                                Enrolled: <?php echo $course['enrolled']; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button onclick="viewCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                            <i class="fas fa-eye text-sm"></i>
                                                        </button>
                                                        <button onclick="editCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-yellow-600 transition-colors">
                                                            <i class="fas fa-edit text-sm"></i>
                                                        </button>
                                                        <button onclick="deleteCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                            <i class="fas fa-trash text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'students'): ?>
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-900">Students Management</h2>
                            <button onclick="showAddStudentForm()" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Add Student</span>
                            </button>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="space-y-4">
                                <?php foreach ($students as $student): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-graduation-cap text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($student['name']); ?></h3>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['email']); ?></p>
                                                    <div class="flex items-center space-x-2 mt-1">
                                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                                            <?php echo htmlspecialchars($student['program']); ?>
                                                        </span>
                                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                            <?php echo htmlspecialchars($student['year']); ?>
                                                        </span>
                                                        <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                                            GPA: <?php echo $student['gpa']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="viewStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <button onclick="editStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-yellow-600 transition-colors">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button onclick="deleteStudent(<?php echo $student['id']; ?>)" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($students)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        No students found matching your search.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'courses'): ?>
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-900">Courses Management</h2>
                            <button onclick="showAddCourseForm()" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Add Course</span>
                            </button>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="space-y-4">
                                <?php foreach ($courses as $course): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-2">
                                                    <span class="font-mono font-bold text-blue-600"><?php echo htmlspecialchars($course['code']); ?></span>
                                                    <span class="text-sm text-gray-500">• <?php echo $course['credits']; ?> credits</span>
                                                </div>
                                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($course['name']); ?></h3>
                                                <p class="text-sm text-gray-600">Instructor: <?php echo htmlspecialchars($course['instructor']); ?></p>
                                                <div class="mt-2">
                                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">
                                                        Enrolled: <?php echo $course['enrolled']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="viewCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <button onclick="editCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-yellow-600 transition-colors">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button onclick="deleteCourse(<?php echo $course['id']; ?>)" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($courses)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        No courses found matching your search.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'faculty'): ?>
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-900">Faculty Management</h2>
                            <button onclick="showAddFacultyForm()" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                <span>Add Faculty</span>
                            </button>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="space-y-4">
                                <?php foreach ($faculty as $facultyMember): ?>
                                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-users text-purple-600"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($facultyMember['name']); ?></h3>
                                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($facultyMember['email']); ?></p>
                                                    <div class="flex items-center space-x-2 mt-1">
                                                        <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                                                            <?php echo htmlspecialchars($facultyMember['department']); ?>
                                                        </span>
                                                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">
                                                            <?php echo htmlspecialchars($facultyMember['position']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <button onclick="viewFaculty(<?php echo $facultyMember['id']; ?>)" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <button onclick="editFaculty(<?php echo $facultyMember['id']; ?>)" class="p-2 text-gray-400 hover:text-yellow-600 transition-colors">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button onclick="deleteFaculty(<?php echo $facultyMember['id']; ?>)" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($faculty)): ?>
                                    <div class="text-center py-8 text-gray-500">
                                        No faculty members found matching your search.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'reports'): ?>
                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900">Reports & Analytics</h2>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enrollment Trends</h3>
                                    <div class="chart-container">
                                        <div class="text-center">
                                            <p class="text-gray-500">Enrollment analytics chart would appear here</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <h4 class="font-medium text-gray-700 mb-2">Key Insights:</h4>
                                        <ul class="list-disc list-inside text-sm text-gray-600">
                                            <li>Enrollment increased by 83% over the last 6 months</li>
                                            <li>Most popular program: Computer Science (35% of total)</li>
                                            <li>Average class size: 32 students</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
                                    <div class="chart-container">
                                        <div class="text-center">
                                            <p class="text-gray-500">Performance analytics chart would appear here</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <h4 class="font-medium text-gray-700 mb-2">Key Insights:</h4>
                                        <ul class="list-disc list-inside text-sm text-gray-600">
                                            <li>Average GPA across all programs: 3.5</li>
                                            <li>Top performing department: Engineering (avg GPA: 3.7)</li>
                                            <li>25% of students have a GPA above 3.8</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Course Performance</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Course Code
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Course Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Instructor
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Enrollment
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Average GPA
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($course['code']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($course['name']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($course['instructor']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?php echo $course['enrolled']; ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">3.6</div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'settings'): ?>
                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold text-gray-900">System Settings</h2>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="space-y-6">
                                <div class="border-b border-gray-200 pb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">General Settings</h3>
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="tab" value="settings">
                                        <input type="hidden" name="save_settings" value="1">

                                        <div class="setting-item">
                                            <span class="text-gray-600">Email Notifications</span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="email_notifications" checked>
                                                <span class="slider"></span>
                                            </label>
                                        </div>

                                        <div class="setting-item">
                                            <span class="text-gray-600">Auto Backup</span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="auto_backup" checked>
                                                <span class="slider"></span>
                                            </label>
                                        </div>

                                        <div class="setting-item">
                                            <span class="text-gray-600">Dark Mode</span>
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="dark_mode">
                                                <span class="slider"></span>
                                            </label>
                                        </div>

                                        <div class="pt-4">
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Settings</button>
                                        </div>
                                    </form>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Academic Settings</h3>
                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="tab" value="settings">
                                        <input type="hidden" name="save_academic_settings" value="1">

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Semester</label>
                                            <input type="text" name="current_semester" value="Summer 2024" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                                            <input type="text" name="academic_year" value="2024-2025" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Credits per Semester</label>
                                            <input type="number" name="max_credits" value="18" min="1" max="24" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>

                                        <div class="pt-4">
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Academic Settings</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Forms -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-lg font-semibold"></h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="crud-form" method="POST" action="">
                <input type="hidden" id="form-action" name="action">
                <input type="hidden" id="record-id" name="id">
                <div id="form-fields"></div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // AJAX function to fetch data
        async function fetchData(url) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Error fetching data:', error);
                Swal.fire('Error!', 'Failed to fetch data. Please try again.', 'error');
                return null;
            }
        }

        // Student functions
        function showAddStudentForm() {
            document.getElementById('modal-title').textContent = 'Add New Student';
            document.getElementById('form-action').value = 'add_student';
            document.getElementById('record-id').value = '';
            document.getElementById('crud-form').action = 'index.php';
            document.getElementById('form-fields').innerHTML = `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <input type="text" name="program" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="year" required class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="Freshman">Freshman</option>
                        <option value="Sophomore">Sophomore</option>
                        <option value="Junior">Junior</option>
                        <option value="Senior">Senior</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">GPA</label>
                    <input type="number" step="0.01" min="0" max="4" name="gpa" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            `;
            document.getElementById('modal').classList.remove('hidden');
        }

        async function editStudent(id) {
            const student = await fetchData(`index.php?get_student=1&id=${id}`);
            if (student) {
                document.getElementById('modal-title').textContent = 'Edit Student';
                document.getElementById('form-action').value = 'edit_student';
                document.getElementById('record-id').value = student.id;
                document.getElementById('crud-form').action = 'index.php';
                document.getElementById('form-fields').innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="${student.name}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="${student.email}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                        <input type="text" name="program" value="${student.program}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <select name="year" required class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="Freshman" ${student.year === 'Freshman' ? 'selected' : ''}>Freshman</option>
                            <option value="Sophomore" ${student.year === 'Sophomore' ? 'selected' : ''}>Sophomore</option>
                            <option value="Junior" ${student.year === 'Junior' ? 'selected' : ''}>Junior</option>
                            <option value="Senior" ${student.year === 'Senior' ? 'selected' : ''}>Senior</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">GPA</label>
                        <input type="number" step="0.01" min="0" max="4" name="gpa" value="${student.gpa}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                `;
                document.getElementById('modal').classList.remove('hidden');
            }
        }

        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Course functions
        function showAddCourseForm() {
            document.getElementById('modal-title').textContent = 'Add New Course';
            document.getElementById('form-action').value = 'add_course';
            document.getElementById('record-id').value = '';
            document.getElementById('crud-form').action = 'course_actions.php';
            document.getElementById('form-fields').innerHTML = `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Code</label>
                    <input type="text" name="code" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Name</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instructor</label>
                    <input type="text" name="instructor" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credits</label>
                    <input type="number" name="credits" min="1" max="10" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enrolled Students</label>
                    <input type="number" name="enrolled" min="0" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            `;
            document.getElementById('modal').classList.remove('hidden');
        }

        async function editCourse(id) {
            const course = await fetchData(`course_actions.php?get_course=1&id=${id}`);
            if (course) {
                document.getElementById('modal-title').textContent = 'Edit Course';
                document.getElementById('form-action').value = 'edit_course';
                document.getElementById('record-id').value = course.id;
                document.getElementById('crud-form').action = 'course_actions.php';
                document.getElementById('form-fields').innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course Code</label>
                        <input type="text" name="code" value="${course.code}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course Name</label>
                        <input type="text" name="name" value="${course.name}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instructor</label>
                        <input type="text" name="instructor" value="${course.instructor}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Credits</label>
                        <input type="number" name="credits" min="1" max="10" value="${course.credits}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enrolled Students</label>
                        <input type="number" name="enrolled" min="0" value="${course.enrolled}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                `;
                document.getElementById('modal').classList.remove('hidden');
            }
        }

        function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'course_actions.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Faculty functions
        function showAddFacultyForm() {
            document.getElementById('modal-title').textContent = 'Add New Faculty Member';
            document.getElementById('form-action').value = 'add_faculty';
            document.getElementById('record-id').value = '';
            document.getElementById('crud-form').action = 'faculty_actions.php';
            document.getElementById('form-fields').innerHTML = `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <input type="text" name="department" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" name="position" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            `;
            document.getElementById('modal').classList.remove('hidden');
        }

        async function editFaculty(id) {
            const faculty = await fetchData(`faculty_actions.php?get_faculty=1&id=${id}`);
            if (faculty) {
                document.getElementById('modal-title').textContent = 'Edit Faculty Member';
                document.getElementById('form-action').value = 'edit_faculty';
                document.getElementById('record-id').value = faculty.id;
                document.getElementById('crud-form').action = 'faculty_actions.php';
                document.getElementById('form-fields').innerHTML = `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="${faculty.name}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="${faculty.email}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" name="department" value="${faculty.department}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" name="position" value="${faculty.position}" required class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                `;
                document.getElementById('modal').classList.remove('hidden');
            }
        }

        function deleteFaculty(id) {
            if (confirm('Are you sure you want to delete this faculty member?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'faculty_actions.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_faculty">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // View functions (simple alerts for demo)
        function viewStudent(id) {
            alert('View Student functionality would show detailed student information');
        }

        function viewCourse(id) {
            alert('View Course functionality would show detailed course information');
        }

        function viewFaculty(id) {
            alert('View Faculty functionality would show detailed faculty information');
        }

        // Modal functions
        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Form submission handling
        document.getElementById('crud-form').addEventListener('submit', function(e) {
            // The form will submit normally to handle PHP processing
            // No need to prevent default since we want the POST request to go through
        });
    </script>
</body>

</html>