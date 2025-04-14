<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
if ($job_id == 0) {
    header("Location: admin-register.php");
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
    header("Location: admin-register.php");
    exit();
}

// Fetch applications for this job
$applications_sql = "SELECT a.id, a.user_id, a.resume_path, a.cover_letter, a.status, a.applied_at, 
                    u.username 
                    FROM applications a 
                    JOIN users u ON a.user_id = u.id 
                    WHERE a.job_id = ? 
                    ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($applications_sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$applications_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Opportunity Alert</title>
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
        .input-field { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(99, 102, 241, 0.5); padding: 10px; border-radius: 8px; color: white; transition: border-color 0.3s ease; }
        .section-title { background: linear-gradient(90deg, #a855f7, #3b82f6); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
        .status-approved { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .status-disapproved { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .action-btn { transition: transform 0.2s ease; }
        .action-btn:hover:not(:disabled) { transform: translateY(-2px); }
    </style>
</head>
<body class="text-white min-h-screen">
    <!-- Navigation -->
    <nav class="glass p-4 md:p-6 shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent animate__animated animate__pulse">Admin Panel</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg flex items-center">
                <a href="index.php" class="neon-hover px-4 py-2 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="jobs.php" class="neon-hover px-4 py-2 rounded-full hover:bg-indigo-500/20 transition">View Jobs</a>
                <a href="courses.php" class="neon-hover px-4 py-2 rounded-full hover:bg-indigo-500/20 transition">View Courses</a>
                <a href="admin-register.php" class="neon-hover px-4 py-2 rounded-full hover:bg-indigo-500/20 transition">Manage</a>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-900 via-purple-900 to-black pt-24 pb-12 relative shadow-md">
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold section-title mb-6 animate__animated animate__fadeIn">Applications for <?php echo htmlspecialchars($job['title']); ?></h1>
            <p class="text-xl text-gray-300 animate__animated animate__fadeIn animate__delay-1s">Review applications submitted for <?php echo htmlspecialchars($job['company']); ?>.</p>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
    </header>

    <!-- Applications Section -->
    <section class="container mx-auto py-12 px-4">
        <div class="glass p-8 rounded-xl card-hover animate__animated animate__fadeIn">
            <h3 class="text-2xl font-bold section-title mb-6">Application Details</h3>
            <?php if ($applications_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($app = $applications_result->fetch_assoc()): ?>
                        <div class="bg-gray-800 rounded-lg p-6 card-hover">
                            <div>
                                <span class="inline-block px-3 py-1 rounded-full text-sm <?php
                                    echo $app['status'] === 'pending' ? 'status-pending' :
                                         ($app['status'] === 'approved' ? 'status-approved' : 'status-disapproved');
                                ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                                <h4 class="text-xl font-bold mt-3 mb-2"><?php echo htmlspecialchars($app['username']); ?></h4>
                                <p class="text-sm text-gray-300"><i class="fas fa-briefcase mr-1"></i> <?php echo htmlspecialchars($job['title']); ?></p>
                                <p class="text-sm text-gray-300"><i class="fas fa-building mr-1"></i> <?php echo htmlspecialchars($job['company']); ?></p>
                                <p class="text-sm text-gray-300 mt-2"><i class="fas fa-file-alt mr-1"></i> Cover Letter: <?php echo htmlspecialchars($app['cover_letter'] ?: 'None'); ?></p>
                                <p class="text-sm text-gray-300"><i class="fas fa-file-pdf mr-1"></i> Resume: <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" class="text-indigo-400 hover:underline" target="_blank">Download</a></p>
                                <p class="text-sm text-gray-300"><i class="fas fa-calendar-alt mr-1"></i> Applied: <?php echo date("M d, Y", strtotime($app['applied_at'])); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300 text-center">No applications for this job yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="glass py-16 mt-12">
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