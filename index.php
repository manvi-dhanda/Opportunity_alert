<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$username = '';
$role = ''; // Added role variable
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username, role FROM users WHERE id = ?"; // Added role to query
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']);
        $role = $row['role']; // Store role
    } else {
        $username = "User not found"; // Debug fallback
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); 
            font-family: 'Poppins', sans-serif; 
            overflow-x: hidden; 
        }
        .glass { 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
        }
        .neon-glow { 
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); 
        }
        .neon-hover:hover { 
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.9); 
            transform: scale(1.05); 
            transition: all 0.4s ease; 
        }
        .hero-bg { 
            background: url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center/cover;
        }
        .scroll-section { 
            opacity: 0; 
            transform: translateY(60px); 
            transition: opacity 0.8s ease, transform 0.8s ease; 
        }
        .scroll-section.visible { 
            opacity: 1; 
            transform: translateY(0); 
        }
        .nav-btn { 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .nav-btn:hover { 
            transform: scale(1.1); 
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.8); 
        }
    </style>
</head>
<body class="text-white relative min-h-screen">
    <!-- 1. Navbar -->
    <nav id="navbar" class="relative z-20 glass p-4 md:p-6 shadow-lg fixed w-full top-0 transition-all duration-300">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <div class="logo">
                <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Opportunity Alert</h1>
            </div>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg flex items-center">
                <a href="#home" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="jobs.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Jobs</a>
                <a href="courses.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Courses</a>
                <a href="about.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">About Us</a>
                <a href="contact.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Contact</a>
                <?php if ($logged_in): ?>
                    <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, 
                        <a href="<?php echo $role === 'admin' ? 'admin-register.php' : 'dashboard.php'; ?>" class="hover:underline">
                            <?php echo $username; ?>
                        </a> 
                        (<?php echo $role; ?>)
                    </span>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-indigo-500 px-4 py-2 rounded-full neon-glow nav-btn">Login</a>
                    <a href="register.php" class="bg-gradient-to-r from-purple-500 to-indigo-500 px-4 py-2 rounded-full neon-glow nav-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 2. Hero Section -->
    <section id="home" class="relative z-20 min-h-screen flex items-center justify-center pt-24 hero-bg">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
        <div class="text-center animate__animated animate__fadeIn relative z-10">
            <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6">Never Miss an Opportunity Again.</h1>
            <p class="text-lg md:text-2xl text-gray-300 mb-8">Get notified for the jobs and courses that truly matter to you.</p>
            <div class="space-x-4">
                <?php if ($logged_in): ?>
                    <a href="courses.php" class="bg-gradient-to-r from-indigo-400 to-purple-500 text-white px-6 py-3 rounded-full neon-glow text-lg hover:bg-gradient-to-r hover:from-purple-500 hover:to-indigo-600">Courses</a>
                    <a href="jobs.php" class="bg-transparent border-2 border-indigo-500 text-indigo-500 px-6 py-3 rounded-full neon-hover text-lg hover:bg-indigo-500 hover:text-white">Jobs</a>
                <?php else: ?>
                    <a href="register.php" class="bg-gradient-to-r from-indigo-400 to-purple-500 text-white px-6 py-3 rounded-full neon-glow text-lg hover:bg-gradient-to-r hover:from-purple-500 hover:to-indigo-600">Register</a>
                    <a href="login.php" class="bg-transparent border-2 border-indigo-500 text-indigo-500 px-6 py-3 rounded-full neon-hover text-lg hover:bg-indigo-500 hover:text-white">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- 3. Jobs Section -->
    <section id="jobs" class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-12">Explore Top Jobs</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">💼</span>
                <h3 class="text-2xl font-bold mt-4">Job Alerts</h3>
                <p class="text-gray-300 mt-2">Get instant notifications for new job openings.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🎯</span>
                <h3 class="text-2xl font-bold mt-4">Tailored Matches</h3>
                <p class="text-gray-300 mt-2">Jobs that align with your skills and goals.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">📩</span>
                <h3 class="text-2xl font-bold mt-4">Application Tracking</h3>
                <p class="text-gray-300 mt-2">Stay updated on your application status.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🌐</span>
                <h3 class="text-2xl font-bold mt-4">Global Opportunities</h3>
                <p class="text-gray-300 mt-2">Find jobs from around the world.</p>
            </div>
        </div>
    </section>

    <!-- 4. How It Works Section -->
    <section id="how-it-works" class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-12">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🔍</span>
                <h3 class="text-2xl font-bold mt-4">Discover Opportunities</h3>
                <p class="text-gray-300 mt-2">Find the best jobs and courses tailored to your interests.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">📈</span>
                <h3 class="text-2xl font-bold mt-4">Track Your Progress</h3>
                <p class="text-gray-300 mt-2">Monitor your applications and learning journey.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🚀</span>
                <h3 class="text-2xl font-bold mt-4">Achieve Your Goals</h3>
                <p class="text-gray-300 mt-2">Reach new heights in your career with our support.</p>
            </div>
        </div>
    </section>

    <!-- 5. Courses Section -->
    <section id="courses" class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-12">Upskill with Courses</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <a href="courses.php" class="glass p-6 rounded-xl neon-hover text-center block transform transition-all duration-300 hover:scale-105">
                <span class="text-5xl">1️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Discover Courses</h3>
                <p class="text-gray-300 mt-2">Find courses tailored to your interests.</p>
            </a>
            <a href="courses.php" class="glass p-6 rounded-xl neon-hover text-center block transform transition-all duration-300 hover:scale-105">
                <span class="text-5xl">2️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Set Goals</h3>
                <p class="text-gray-300 mt-2">Choose skill levels and learning paths.</p>
            </a>
            <a href="courses.php" class="glass p-6 rounded-xl neon-hover text-center block transform transition-all duration-300 hover:scale-105">
                <span class="text-5xl">3️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Get Certified</h3>
                <p class="text-gray-300 mt-2">Earn certifications to boost your career.</p>
            </a>
        </div>
        <div class="text-center mt-10">
            <a href="courses.php" class="bg-gradient-to-r from-indigo-400 to-purple-500 text-white px-6 py-3 rounded-full neon-glow text-lg hover:bg-gradient-to-r hover:from-purple-500 hover:to-indigo-600 inline-block">View All Courses</a>
        </div>
    </section>

    <!-- 6. Testimonials Section -->
    <section id="testimonials" class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-12">What Our Users Say</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-xl neon-hover">
                <p class="text-lg text-gray-300">"This platform changed how I find opportunities!"</p>
                <div class="flex items-center mt-4">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User" class="h-10 w-10 rounded-full mr-4">
                    <div>
                        <p class="font-bold text-indigo-400">Anjali Sharma</p>
                        <p class="text-gray-500">⭐⭐⭐⭐⭐</p>
                    </div>
                </div>
            </div>
            <div class="glass p-6 rounded-xl neon-hover">
                <p class="text-lg text-gray-300">"Timely alerts helped me land my dream job!"</p>
                <div class="flex items-center mt-4">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User" class="h-10 w-10 rounded-full mr-4">
                    <div>
                        <p class="font-bold text-indigo-400">Rohit Verma</p>
                        <p class="text-gray-500">⭐⭐⭐⭐⭐</p>
                    </div>
                </div>
            </div>
            <div class="glass p-6 rounded-xl neon-hover">
                <p class="text-lg text-gray-300">"The best tool for career growth!"</p>
                <div class="flex items-center mt-4">
                    <img src="https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="User" class="h-10 w-10 rounded-full mr-4">
                    <div>
                        <p class="font-bold text-indigo-400">Priya Singh</p>
                        <p class="text-gray-500">⭐⭐⭐⭐⭐</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Call to Action Section -->
    <section id="cta" class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-12">Ready to Catch Your Next Opportunity?</h2>
        <div class="text-center">
            <a href="register.php" class="bg-gradient-to-r from-indigo-400 to-purple-500 text-white px-6 py-3 rounded-full neon-glow text-lg hover:bg-gradient-to-r hover:from-purple-500 hover:to-indigo-600">Get Started</a>
        </div>
    </section>

    <!-- 8. Footer -->
    <footer class="relative z-20 glass py-16 scroll-section" id="footer">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
            <div>
                <h3 class="text-xl font-bold mb-4 bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Opportunity Alert</h3>
                <p class="text-gray-300">Empowering careers through smart alerts.</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <a href="#home" class="block text-gray-300 hover:text-indigo-400">Home</a>
                <a href="jobs.php" class="block text-gray-300 hover:text-indigo-400">Jobs</a>
                <a href="courses.php" class="block text-gray-300 hover:text-indigo-400">Courses</a>
                <a href="about.php" class="block text-gray-300 hover:text-indigo-400">About Us</a>
                <a href="contact.php" class="block text-gray-300 hover:text-indigo-400">Contact</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">LinkedIn</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Twitter</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Instagram</a>
            </div>
        </div>
        <p class="text-center text-gray-300 mt-8">© 2025 | Opportunity Alert</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>

<?php $conn->close(); ?>