<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to reconnect if MySQL connection drops
function reconnect_db() {
    // Always create a fresh connection
    $conn = new mysqli("localhost", "root", "", "opportunity_alert");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    
    // Set longer timeout and other parameters to avoid "server has gone away" errors
    $conn->query("SET SESSION wait_timeout=600"); // 10 minutes timeout
    $conn->query("SET SESSION interactive_timeout=600");
    $conn->query("SET SESSION net_read_timeout=600");
    
    return $conn;
}

// Ensure we have a fresh connection at the beginning
$conn = reconnect_db();

// Add Job
if (isset($_POST['add_job'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $company = htmlspecialchars($_POST['company']);
    $location = htmlspecialchars($_POST['location']);
    $field = htmlspecialchars($_POST['field']);
    $interest = htmlspecialchars($_POST['interest']);
    $expiration_date = $_POST['expiration_date'];

    // Handle image upload
    $bg_image = $_FILES['bg_image'];
    $image_error = false;

    // Validate image type
    if (!in_array($bg_image['type'], ['image/png', 'image/jpeg'])) {
        $error = "Only PNG and JPG images are allowed.";
        $image_error = true;
    } elseif ($bg_image['error'] !== UPLOAD_ERR_OK) {
        $error = "Failed to upload image.";
        $image_error = true;
    } else {
        // Read image data
        $image_data = file_get_contents($bg_image['tmp_name']);
        if ($image_data === false) {
            $error = "Failed to read image data.";
            $image_error = true;
        }
    }

    if (!$image_error) {
        // Get a fresh connection for job insertion
        $conn = reconnect_db();
        
        // Insert job using a completely new approach to avoid "MySQL server has gone away" error
        try {
            // Step 1: Insert job data without the image first
            $sql1 = "INSERT INTO jobs (title, description, company, location, field, interest, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("sssssss", $title, $description, $company, $location, $field, $interest, $expiration_date);
            $stmt1->execute();
            $job_id = $stmt1->insert_id;
            $stmt1->close();
            
            if ($job_id) {
                // Step 2: Now update the job with the image in a separate query
                $conn = reconnect_db(); // Get a fresh connection
                $sql2 = "UPDATE jobs SET bg_image = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $null = NULL; // Placeholder for BLOB
                $stmt2->bind_param("bi", $null, $job_id);
                $stmt2->send_long_data(0, $image_data);
                $stmt2->execute();
                $stmt2->close();
                
                $message = "Job Created!";
                
                // Create a fresh connection for notification
                $conn = reconnect_db();
                
                // Notify all users
                $notify_sql = "INSERT INTO notifications (user_id, type, item_id, message) 
                            SELECT id, 'job', ?, ? FROM users WHERE role = 'user'";
                $notify_stmt = $conn->prepare($notify_sql);
                $notify_message = "New job: " . $title;
                $notify_stmt->bind_param("is", $job_id, $notify_message);
                $notify_stmt->execute();
                $notify_stmt->close();
            } else {
                $error = "Failed to add job: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Add Course
if (isset($_POST['add_course'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $provider = htmlspecialchars($_POST['provider']);
    $duration = htmlspecialchars($_POST['duration']);
    $field = htmlspecialchars($_POST['field']);

    // Handle image upload
    $bg_image = $_FILES['bg_image'];
    $image_error = false;

    // Validate image type
    if (!in_array($bg_image['type'], ['image/png', 'image/jpeg'])) {
        $error = "Only PNG and JPG images are allowed.";
        $image_error = true;
    } elseif ($bg_image['error'] !== UPLOAD_ERR_OK) {
        $error = "Failed to upload image.";
        $image_error = true;
    } else {
        // Read image data
        $image_data = file_get_contents($bg_image['tmp_name']);
        if ($image_data === false) {
            $error = "Failed to read image data.";
            $image_error = true;
        }
    }

    if (!$image_error) {
        // Get a fresh connection for course insertion
        $conn = reconnect_db();
        
        // Insert course using a completely new approach to avoid "MySQL server has gone away" error
        try {
            // Step 1: Insert course data without the image first
            $sql1 = "INSERT INTO courses (title, description, provider, duration, field) VALUES (?, ?, ?, ?, ?)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("sssss", $title, $description, $provider, $duration, $field);
            $stmt1->execute();
            $course_id = $stmt1->insert_id;
            $stmt1->close();
            
            if ($course_id) {
                // Step 2: Now update the course with the image in a separate query
                $conn = reconnect_db(); // Get a fresh connection
                $sql2 = "UPDATE courses SET bg_image = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $null = NULL; // Placeholder for BLOB
                $stmt2->bind_param("bi", $null, $course_id);
                $stmt2->send_long_data(0, $image_data);
                $stmt2->execute();
                $stmt2->close();
                
                $message = "Course Created!";
                
                // Create a fresh connection for notification
                $conn = reconnect_db();
                
                // Notify all users
                $notify_sql = "INSERT INTO notifications (user_id, type, item_id, message) 
                              SELECT id, 'course', ?, ? FROM users WHERE role = 'user'";
                $notify_stmt = $conn->prepare($notify_sql);
                $notify_message = "New course: " . $title;
                $notify_stmt->bind_param("is", $course_id, $notify_message);
                $notify_stmt->execute();
                $notify_stmt->close();
            } else {
                $error = "Failed to add course: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle application status
if (isset($_POST['update_application'])) {
    $application_id = intval($_POST['application_id']);
    $job_id = intval($_POST['job_id']);
    $user_id = intval($_POST['user_id']);
    $status = $_POST['status'];

    // Get a fresh connection
    $conn = reconnect_db();
    
    $sql = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $application_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $message = "Application $status!";
        if ($status === 'approved') {
            // Create a fresh connection for notification
            $conn = reconnect_db();
            
            // Notify user
            $notify_sql = "INSERT INTO notifications (user_id, type, item_id, message) VALUES (?, 'job', ?, ?)";
            $notify_stmt = $conn->prepare($notify_sql);
            $notify_message = "The job you applied for has been approved!";
            $notify_stmt->bind_param("iis", $user_id, $job_id, $notify_message);
            $notify_stmt->execute();
            $notify_stmt->close();
        }
    } else {
        $error = "Failed to update application status.";
    }
    $stmt->close();
}

// Fetch pending applications with a fresh connection
$conn = reconnect_db();
$apps_sql = "SELECT a.id AS app_id, a.job_id, a.user_id, a.status, j.title, j.company, j.field, j.expiration_date, j.bg_image, u.username
             FROM applications a
             JOIN jobs j ON a.job_id = j.id
             JOIN users u ON a.user_id = u.id
             WHERE a.status = 'pending'
             ORDER BY a.applied_at DESC";
$apps_result = $conn->query($apps_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Opportunity Alert</title>
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
        .input-field:focus { border-color: #6366f1; outline: none; }
        .submit-btn { background: linear-gradient(90deg, #6366f1, #9333ea); padding: 12px; border-radius: 8px; font-weight: 600; transition: transform 0.3s ease; }
        .submit-btn:hover { transform: translateY(-2px); }
        .section-title { background: linear-gradient(90deg, #a855f7, #3b82f6); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
        .status-approved { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .status-disapproved { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .action-btn { transition: transform 0.2s ease; }
        .action-btn:hover:not(:disabled) { transform: translateY(-2px); }
        .nav-btn { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .nav-btn:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.8); }
    </style>
</head>
<body class="text-white min-h-screen">
    <!-- Navigation -->
    <nav class="glass p-4 md:p-6 shadow-lg fixed w-full top-0 z-50">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Opportunity Alert</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg">
                <a href="index.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="jobs.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Jobs</a>
                <a href="courses.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Courses</a>
                <a href="about.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">About Us</a>
                <a href="contact.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Contact</a>
                <?php
                // Fetch username from session with a fresh connection
                $username = '';
                $role = 'admin';
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $conn_local = reconnect_db(); // Use a separate connection for this query
                    $user_sql = "SELECT username FROM users WHERE id = ?";
                    $stmt = $conn_local->prepare($user_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $username = htmlspecialchars($row['username']);
                    }
                    $stmt->close();
                    $conn_local->close(); // Close this connection when done
                }
                ?>
                <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, 
                    <a href="admin-register.php" class="hover:underline">
                        <?php echo $username; ?>
                    </a> 
                    (<?php echo $role; ?>)
                </span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-900 via-purple-900 to-black pt-24 pb-12 relative shadow-md">
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold section-title mb-6 animate__animated animate__fadeIn">Admin Control Center</h1>
            <p class="text-xl text-gray-300 animate__animated animate__fadeIn animate__delay-1s">Curate job opportunities and courses with ease.</p>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
    </header>

    <!-- Main Section -->
    <section class="container mx-auto py-12 px-4">
        <?php if (isset($message)): ?>
            <p class="text-indigo-400 mb-6 text-center font-medium animate__animated animate__fadeIn"><?php echo $message; ?></p>
        <?php elseif (isset($error)): ?>
            <p class="text-red-400 mb-6 text-center font-medium animate__animated animate__fadeIn"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Add Job -->
        <div class="glass p-8 rounded-xl mb-12 card-hover animate__animated animate__fadeIn">
            <h3 class="text-2xl font-bold section-title mb-6">Add Job Opportunity</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Job Title</label>
                        <input type="text" name="title" placeholder="e.g., Software Engineer" class="input-field w-full" required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Company</label>
                        <input type="text" name="company" placeholder="e.g., TechCorp" class="input-field w-full" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-300 mb-2">Description</label>
                        <textarea name="description" placeholder="Describe the job..." class="input-field w-full" rows="4" required></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Location</label>
                        <input type="text" name="location" placeholder="e.g., Remote" class="input-field w-full">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Field</label>
                        <input type="text" name="field" placeholder="e.g., IT" class="input-field w-full" required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Interest</label>
                        <input type="text" name="interest" placeholder="e.g., Full-time" class="input-field w-full" required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Expiration Date</label>
                        <input type="date" name="expiration_date" class="input-field w-full" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-300 mb-2">Background Image (PNG/JPG)</label>
                        <input type="file" name="bg_image" class="input-field w-full" accept="image/png,image/jpeg" required>
                    </div>
                </div>
                <button type="submit" name="add_job" class="submit-btn w-full text-white mt-6 neon-hover">Add Job</button>
            </form>
        </div>

        <!-- Add Course -->
        <div class="glass p-8 rounded-xl mb-12 card-hover animate__animated animate__fadeIn animate__delay-1s">
            <h3 class="text-2xl font-bold section-title mb-6">Add Course</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-300 mb-2">Course Title</label>
                        <input type="text" name="title" placeholder="e.g., Web Development" class="input-field w-full" required>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Provider</label>
                        <input type="text" name="provider" placeholder="e.g., Coursera" class="input-field w-full" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-300 mb-2">Description</label>
                        <textarea name="description" placeholder="Describe the course..." class="input-field w-full" rows="4" required></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Duration</label>
                        <input type="text" name="duration" placeholder="e.g., 3 months" class="input-field w-full">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-2">Field</label>
                        <input type="text" name="field" placeholder="e.g., IT" class="input-field w-full" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-300 mb-2">Background Image (PNG/JPG)</label>
                        <input type="file" name="bg_image" class="input-field w-full" accept="image/png,image/jpeg" required>
                    </div>
                </div>
                <button type="submit" name="add_course" class="submit-btn w-full text-white mt-6 neon-hover">Add Course</button>
            </form>
        </div>

        <!-- Manage Jobs -->
        <div class="glass p-8 rounded-xl card-hover animate__animated animate__fadeIn animate__delay-2s">
            <h3 class="text-2xl font-bold section-title mb-6">Manage Job Applications</h3>
            <?php if ($apps_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($app = $apps_result->fetch_assoc()): ?>
                        <div class="glass rounded-xl overflow-hidden relative card-hover shadow-lg border border-indigo-500/20">
                            <div class="absolute inset-0">
                                <?php if (!empty($app['bg_image'])): ?>
                                    <img src="data:image/<?php echo ($app['bg_image'][0] == 0xFF) ? 'jpeg' : 'png'; ?>;base64,<?php echo base64_encode($app['bg_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($app['title']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?q=80&w=900" 
                                         alt="<?php echo htmlspecialchars($app['title']); ?>" class="w-full h-full object-cover">
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-black/50"></div>
                            </div>
                            <div class="relative p-6 flex flex-col justify-between h-96 text-white">
                                <div class="bg-black/60 p-4 rounded-lg backdrop-blur-sm">
                                    <span class="inline-block px-3 py-1 rounded-full text-sm status-pending font-medium">Pending</span>
                                    <h4 class="text-xl font-bold mt-3 mb-2 text-white"><?php echo htmlspecialchars($app['title']); ?></h4>
                                    <p class="text-sm text-gray-200"><i class="fas fa-building mr-1 text-indigo-300"></i> <?php echo htmlspecialchars($app['company']); ?></p>
                                    <p class="text-sm text-gray-200"><i class="fas fa-user mr-1 text-indigo-300"></i> Applicant: <?php echo htmlspecialchars($app['username']); ?></p>
                                    <p class="text-sm text-gray-200"><i class="fas fa-tag mr-1 text-indigo-300"></i> <?php echo htmlspecialchars($app['field']); ?></p>
                                    <p class="text-sm text-gray-200"><i class="fas fa-calendar-alt mr-1 text-indigo-300"></i> Expires <?php echo htmlspecialchars($app['expiration_date']); ?></p>
                                </div>
                                <div class="flex flex-col space-y-3">
                                    <form method="POST" class="flex space-x-3">
                                        <input type="hidden" name="application_id" value="<?php echo $app['app_id']; ?>">
                                        <input type="hidden" name="job_id" value="<?php echo $app['job_id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $app['user_id']; ?>">
                                        <button type="submit" name="update_application" value="approved" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-sm font-medium action-btn flex-1 flex items-center justify-center">
                                            <i class="fas fa-check mr-2"></i> Approve
                                        </button>
                                        <button type="submit" name="update_application" value="disapproved" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm font-medium action-btn flex-1 flex items-center justify-center">
                                            <i class="fas fa-times mr-2"></i> Disapprove
                                        </button>
                                    </form>
                                    <a href="view-applications.php?job_id=<?php echo $app['job_id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 rounded-lg text-sm font-medium text-center action-btn neon-hover flex items-center justify-center">
                                        <i class="fas fa-eye mr-2"></i> View Applications
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300 text-center">No jobs to approve.</p>
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