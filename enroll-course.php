<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$enrolled = false;
$error = "";
$course_title = "";

// Handle enrollment
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $course_id = intval($_GET['id']);
    
    // Fetch course details
    $sql = "SELECT title FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $course_title = htmlspecialchars($row['title']);
        
        // Check if already enrolled
        $sql = "SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Insert enrollment
            $sql = "INSERT INTO course_enrollments (user_id, course_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $course_id);
            
            if ($stmt->execute()) {
                $enrolled = true;
                
                // Insert notification
                $message = "You have enrolled in the course: $course_title";
                $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $user_id, $message);
                $stmt->execute();
            } else {
                $error = "Failed to enroll: " . $stmt->error;
            }
        } else {
            $error = "You are already enrolled in this course.";
        }
    } else {
        $error = "Course not found.";
    }
    $stmt->close();
} else {
    $error = "Invalid course ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Course - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(99, 102, 241, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .submit-btn { background: linear-gradient(90deg, #6366f1, #9333ea); padding: 10px; border-radius: 5px; }
        select.input-field option { color: #000000; }
    </style>
</head>
<body class="text-white min-h-screen">
    <!-- Navigation -->
    <nav class="glass p-4 md:p-6 shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent animate__animated animate__pulse">Opportunity Alert</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg flex items-center">
                <a href="index.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="jobs.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Jobs</a>
                <a href="courses.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Courses</a>
                <a href="about.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">About Us</a>
                <a href="contact.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Contact</a>
                <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, 
                    <a href="dashboard.php" class="hover:underline"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></a> 
                    (<?php echo $_SESSION['role'] ?? 'user'; ?>)
                </span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="pt-24 pb-12 container mx-auto px-4">
        <div class="glass rounded-xl p-8 max-w-md mx-auto animate__animated animate__fadeIn">
            <?php if ($enrolled): ?>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6 text-center">
                    You are enrolled!
                </h1>
                <p class="text-gray-300 text-center mb-6">
                    Successfully enrolled in <strong><?php echo $course_title; ?></strong>. 
                    Check your dashboard for updates.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="dashboard.php" class="submit-btn text-white font-medium py-2 px-6 neon-glow">
                        Go to Dashboard
                    </a>
                    <a href="courses.php" class="bg-gray-500 text-white font-medium py-2 px-6 rounded neon-glow">
                        Browse More Courses
                    </a>
                </div>
            <?php elseif ($error): ?>
                <h1 class="text-3xl font-bold text-red-500 mb-6 text-center">Error</h1>
                <p class="text-gray-300 text-center mb-6"><?php echo htmlspecialchars($error); ?></p>
                <div class="flex justify-center">
                    <a href="courses.php" class="submit-btn text-white font-medium py-2 px-6 neon-glow">
                        Back to Courses
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="glass py-16">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
            <div>
                <h3 class="text-xl font-bold mb-4 bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Opportunity Alert</h3>
                <p class="text-gray-300">Empowering careers through smart alerts.</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <a href="index.php" class="block text-gray-300 hover:text-indigo-400">Home</a>
                <a href="jobs.php" class="block text-gray-300 hover:text-indigo-400">Jobs</a>
                <a href="courses.php" class="block text-gray-300 hover:text-indigo-400">Courses</a>
                <a href="about.php" class="block text-gray-300 hover:text-indigo-400">About</a>
                <a href="contact.php" class="block text-gray-300 hover:text-indigo-400">Contact</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Social Media</h3>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Facebook</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Twitter</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Instagram</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Legal</h3>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Privacy Policy</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Terms & Conditions</a>
            </div>
        </div>
        <p class="text-center text-gray-300 mt-8">© 2025 | Opportunity Alert</p>
    </footer>
</body>
</html>

<?php $conn->close(); ?>