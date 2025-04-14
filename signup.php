<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $interests = isset($_POST['interests']) ? implode(',', $_POST['interests']) : '';

    try {
        $stmt = $conn->prepare("INSERT INTO users (email, password, interests) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $interests);
        $stmt->execute();
        $_SESSION['user_id'] = $conn->insert_id;
        header("Location: index.php");
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); 
            font-family: 'Poppins', sans-serif; 
        }
        .glass { 
            background: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
        }
        .neon-glow { 
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.8), 0 0 50px rgba(99, 102, 241, 0.4); 
        }
    </style>
</head>
<body class="text-white">
    <section class="min-h-screen flex items-center justify-center">
        <div class="glass p-8 rounded-xl w-full max-w-md">
            <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6">Create Your Account</h2>
            <?php if (isset($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" id="email" class="w-full p-3 rounded-lg bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" id="password" class="w-full p-3 rounded-lg bg-gray-800 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Interests</label>
                    <div class="space-y-2">
                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" name="interests[]" value="jobs" class="mr-2"> Jobs
                        </label>
                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" name="interests[]" value="courses" class="mr-2"> Courses
                        </label>
                        <label class="flex items-center text-gray-300">
                            <input type="checkbox" name="interests[]" value="internships" class="mr-2"> Internships
                        </label>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-400 to-purple-500 text-white p-3 rounded-lg neon-glow hover:bg-gradient-to-r hover:from-purple-500 hover:to-indigo-600">Signup</button>
            </form>
            <p class="mt-4 text-center text-gray-300">Already have an account? <a href="login.php" class="text-indigo-400 hover:underline">Login</a></p>
        </div>
    </section>
</body>
</html>