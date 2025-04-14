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
$username = "";
$role = "user";

// Fetch user details
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

// Fetch notifications
$sql = "SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(99, 102, 241, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .card-hover:hover { transform: translateY(-5px); transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5); }
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
                <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, 
                    <a href="dashboard.php" class="hover:underline"><?php echo $username; ?></a> 
                    (<?php echo $role; ?>)
                </span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="pt-24 pb-12 container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-8 text-center animate__animated animate__fadeIn">
            Welcome to Your Dashboard, <?php echo $username; ?>!
        </h1>

        <!-- Notifications Section -->
        <div class="glass rounded-xl p-6 mb-8 animate__animated animate__fadeIn">
            <h2 class="text-2xl font-bold mb-4 text-white">Notifications</h2>
            <?php if ($notifications->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <div class="glass rounded-lg p-4 card-hover">
                            <p class="text-gray-300"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <p class="text-sm text-indigo-400 mt-2">
                                <?php echo date('F j, Y, g:i a', strtotime($notification['created_at'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300">No notifications yet.</p>
            <?php endif; ?>
        </div>

        <!-- Placeholder for Other Sections -->
        <div class="glass rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-4 text-white">Your Activity</h2>
            <p class="text-gray-300">Explore more <a href="courses.php" class="text-indigo-400 hover:underline">courses</a> or <a href="jobs.php" class="text-indigo-400 hover:underline">jobs</a>!</p>
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