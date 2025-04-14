<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($job_id == 0) {
    header("Location: jobs.php");
    exit();
}

// Fetch job details
$sql = "SELECT title, company FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$job) {
    header("Location: jobs.php");
    exit();
}

// Fetch username for navbar
$username = "";
$role = "user";
$sql = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $username = htmlspecialchars($row['username']);
    $role = $row['role'];
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = htmlspecialchars($_POST['cover_letter']);
    
    // Handle resume upload
    $resume = $_FILES['resume'];
    $target_dir = "Uploads/Resumes/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $resume_name = time() . "_" . basename($resume['name']);
    $target_file = $target_dir . $resume_name;

    if ($resume['type'] !== 'application/pdf') {
        $error = "Only PDF files are allowed.";
    } elseif ($resume['size'] > 5 * 1024 * 1024) { // 5MB limit
        $error = "File size exceeds 5MB.";
    } elseif (move_uploaded_file($resume['tmp_name'], $target_file)) {
        // Save application to database
        $sql = "INSERT INTO applications (user_id, job_id, resume_path, cover_letter) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $job_id, $target_file, $cover_letter);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $message = "Application submitted successfully for {$job['title']} at {$job['company']}!";
        } else {
            $error = "Failed to save application.";
            unlink($target_file); // Remove uploaded file if DB fails
        }
        $stmt->close();
    } else {
        $error = "Failed to upload resume.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(99, 102, 241, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .card-hover:hover { transform: translateY(-5px); transition: all 0.3s ease; }
        .input-field { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(99, 102, 241, 0.5); padding: 8px; border-radius: 5px; color: white; }
        .input-field:disabled { background: rgba(255, 255, 255, 0.05); color: #a1a1aa; }
        .submit-btn { background: linear-gradient(90deg, #6366f1, #9333ea); padding: 10px; border-radius: 5px; }
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
                <?php if (empty($username)): ?>
                    <a href="login.php" class="bg-indigo-500 px-4 py-2 rounded-full neon-glow">Login</a>
                    <a href="register.php" class="bg-gradient-to-r from-purple-500 to-indigo-500 px-4 py-2 rounded-full neon-glow">Register</a>
                <?php else: ?>
                    <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, <a href="<?php echo $role === 'admin' ? 'admin-register.php' : 'dashboard.php'; ?>" class="hover:underline"><?php echo $username; ?></a> (<?php echo $role; ?>)</span>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-900 via-purple-900 to-black pt-24 pb-8 relative shadow-md">
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6 animate__animated animate__fadeIn">Apply for <?php echo htmlspecialchars($job['title']); ?></h1>
            <div class="flex flex-col md:flex-row gap-8 mb-5">
                <div class="md:w-2/3">
                    <p class="text-xl mb-6 animate__animated animate__fadeIn animate__delay-1s text-gray-300">Submit your application for <?php echo htmlspecialchars($job['company']); ?> and take the next step in your career!</p>
                </div>
                <div class="md:w-1/3 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1593642532973-d31b6557fa68?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDJ8fGpvYnxlbnwwfHx8fDE2ODQ4NzY0NzA&ixlib=rb-1.2.1&q=80&w=1080" alt="Job Application" class="max-h-48 object-contain hidden md:block animate__animated animate__fadeIn animate__delay-2s">
                </div>
            </div>
        </div>
    </header>

    <!-- Apply Form Section -->
    <section class="py-12 container mx-auto px-4">
        <div class="glass p-8 rounded-xl max-w-lg mx-auto card-hover animate__animated animate__fadeIn">
            <?php if (isset($message)): ?>
                <p class="text-indigo-400 mb-4 text-center"><?php echo $message; ?></p>
            <?php elseif (isset($error)): ?>
                <p class="text-red-400 mb-4 text-center"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Job Title</label>
                    <input type="text" value="<?php echo htmlspecialchars($job['title']); ?>" class="input-field w-full" disabled>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Company</label>
                    <input type="text" value="<?php echo htmlspecialchars($job['company']); ?>" class="input-field w-full" disabled>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Upload Resume (PDF only, max 5MB)</label>
                    <input type="file" name="resume" class="input-field w-full" accept=".pdf" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Cover Letter</label>
                    <textarea name="cover_letter" class="input-field w-full" rows="5" placeholder="Why are you a good fit?"></textarea>
                </div>
                <button type="submit" class="submit-btn w-full text-white font-medium neon-hover">Submit Application</button>
            </form>
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