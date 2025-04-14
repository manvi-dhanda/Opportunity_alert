<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert"); // Updated DB name
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Role form se aayega
    
    // Query to fetch user details based on username, password, and role
    $sql = "SELECT id, password, role FROM users WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $role); // Username aur role bind karo
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Password verify karo (plain text abhi, production mein hash karna)
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            
            // Role ke basis pe redirect
            if ($row['role'] === 'admin') {
                header("Location: admin-register.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid username or role!";
    }
    $stmt->close();
}

// Check if user is already logged in
$logged_in = isset($_SESSION['user_id']);
$username = '';
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); } /* Matching index.php */
        .neon-hover:hover { box-shadow: 0 0 30px rgba(99, 102, 241, 0.9); transform: scale(1.05); transition: all 0.4s ease; } /* Matching index.php */
        .login-box { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(15px); 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); /* Updated to indigo */
            transition: transform 0.3s ease; 
        }
        .login-box:hover { transform: scale(1.02); }
        .input-field { 
            background: rgba(255, 255, 255, 0.15); 
            border: 1px solid rgba(99, 102, 241, 0.5); /* Updated to indigo */
            padding: 12px; 
            border-radius: 10px; 
            color: white; 
            width: 100%; 
            transition: border-color 0.3s ease, box-shadow 0.3s ease; 
        }
        .input-field:focus { 
            border-color: #6366f1; /* Indigo shade */
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.8); /* Matching index.php */
            outline: none; 
            background: rgba(255, 255, 255, 0.2); 
        }
        .login-btn { 
            background: linear-gradient(90deg, #6366f1, #9333ea); /* Indigo to purple */
            padding: 12px; 
            border-radius: 10px; 
            font-weight: bold; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .login-btn:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(99, 102, 241, 0.8); } /* Matching index.php */
        .nav-btn { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .nav-btn:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.8); } /* Matching index.php */
        select.input-field option { color: #000000; }
    </style>
</head>
<body class="text-white relative min-h-screen">
    <!-- 1. Header (Navigation Bar) -->
    <nav class="relative z-20 glass p-4 md:p-6 shadow-lg fixed w-full top-0 transition-all duration-300">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Opportunity Alert</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg">
                <a href="index.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="jobs.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Jobs</a>
                <a href="courses.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Courses</a>
                <a href="about.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">About Us</a>
                <a href="contact.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Contact</a>
                <?php if ($logged_in): ?>
                    <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, <a href="dashboard.php" class="hover:underline"><?php echo $username; ?></a> (user)</span>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-indigo-500 px-4 py-2 rounded-full neon-glow nav-btn">Login</a>
                    <a href="register.php" class="bg-gradient-to-r from-purple-500 to-indigo-500 px-4 py-2 rounded-full neon-glow nav-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 2. Login Section -->
    <section class="relative z-20 min-h-screen flex items-center justify-center pt-24">
        <div class="login-box animate__animated animate__fadeIn w-full max-w-md">
            <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-8">Welcome Back</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-400 text-center mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Username</label>
                    <input type="text" name="username" placeholder="Enter your username" class="input-field" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" placeholder="Enter your password" class="input-field" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Role</label>
                    <select name="role" class="input-field" required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="login-btn w-full text-white">Login</button>
            </form>
            <p class="text-gray-300 text-center mt-6">Don't have an account? <a href="register.php" class="text-indigo-400 hover:underline">Register here</a></p>
        </div>
    </section>

    <!-- 3. Footer -->
    <footer class="relative z-20 glass py-16">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
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
                <h3 class="text-xl font-bold mb-4">Contact Info</h3>
                <p class="text-gray-300">Email: info@opportunityalert.com</p>
                <p class="text-gray-300">Phone: +91 123-456-7890</p>
                <p class="text-gray-300">Address: Career Hub, Tech City</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Legal</h3>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Privacy Policy</a>
                <a href="#" class="block text-gray-300 hover:text-indigo-400">Terms & Conditions</a>
            </div>
        </div>
        <p class="text-center text-gray-300 mt-8">© 2025 | Made with ❤️ for Your Career</p>
    </footer>

    <!-- JavaScript -->
    <script>
        // No additional JS needed here
    </script>
</body>
</html>

<?php $conn->close(); ?>