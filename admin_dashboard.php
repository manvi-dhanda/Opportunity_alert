<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Add Job Opportunity
if (isset($_POST['add_job'])) {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $company = htmlspecialchars($_POST['company']);
    $location = htmlspecialchars($_POST['location']);
    $field = htmlspecialchars($_POST['field']);
    $interest = htmlspecialchars($_POST['interest']);
    $expiration_date = $_POST['expiration_date'];

    $sql = "INSERT INTO jobs (title, description, company, location, field, interest, expiration_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $title, $description, $company, $location, $field, $interest, $expiration_date);
    $stmt->execute();
    $job_message = $stmt->affected_rows > 0 ? "Job added successfully!" : "Failed to add job.";
}

// Edit Job Opportunity
if (isset($_POST['edit_job'])) {
    $job_id = $_POST['job_id'];
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $company = htmlspecialchars($_POST['company']);
    $location = htmlspecialchars($_POST['location']);
    $field = htmlspecialchars($_POST['field']);
    $interest = htmlspecialchars($_POST['interest']);
    $expiration_date = $_POST['expiration_date'];

    $sql = "UPDATE jobs SET title = ?, description = ?, company = ?, location = ?, field = ?, interest = ?, expiration_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $title, $description, $company, $location, $field, $interest, $expiration_date, $job_id);
    $stmt->execute();
    $job_message = $stmt->affected_rows > 0 ? "Job updated successfully!" : "Failed to update job.";
}

// Delete Job Opportunity
if (isset($_POST['delete_job'])) {
    $job_id = $_POST['job_id'];
    $sql = "DELETE FROM jobs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job_message = $stmt->affected_rows > 0 ? "Job deleted successfully!" : "Failed to delete job.";
}

// Send Notification
if (isset($_POST['send_notification'])) {
    $message = htmlspecialchars($_POST['notification_message']);
    $sql = "INSERT INTO notifications (message, sent_by) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $message, $admin_id);
    $stmt->execute();
    $notif_message = $stmt->affected_rows > 0 ? "Notification sent successfully!" : "Failed to send notification.";
}

// Fetch Jobs
$jobs_sql = "SELECT * FROM jobs";
$jobs_result = $conn->query($jobs_sql);

// Fetch Users
$users_sql = "SELECT * FROM users WHERE role = 'user'";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Opportunity Alert</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(99, 102, 241, 0.8); }
        .input-field { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(99, 102, 241, 0.5); padding: 8px; border-radius: 5px; color: white; }
        .submit-btn { background: linear-gradient(90deg, #6366f1, #9333ea); padding: 10px; border-radius: 5px; }
    </style>
</head>
<body class="text-white min-h-screen">
    <nav class="glass p-4 shadow-lg fixed w-full top-0">
        <div class="container mx-auto flex justify-between">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent">Admin Dashboard</h1>
            <div class="space-x-4 flex items-center">
                <a href="index.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Home</a>
                <a href="admin-register.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">Add Jobs/Courses</a>
                <a href="admin_dashboard.php" class="neon-hover px-3 py-1 rounded-full hover:bg-indigo-500/20 transition">View Jobs</a>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
            </div>
        </div>
    </nav>

    <section class="container mx-auto py-20 pt-24">
        <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-8">Manage Opportunities</h2>

        <!-- Add/Edit Job Form -->
        <div class="glass p-6 rounded-xl mb-8">
            <h3 class="text-xl font-bold mb-4"><?php echo isset($_GET['edit']) ? 'Edit Job' : 'Add New Job'; ?></h3>
            <?php if (isset($job_message)) echo "<p class='text-indigo-400 mb-4'>$job_message</p>"; ?>
            <form method="POST">
                <?php if (isset($_GET['edit']) && $edit_job = $jobs_result->fetch_assoc()): ?>
                    <input type="hidden" name="job_id" value="<?php echo $edit_job['id']; ?>">
                <?php endif; ?>
                <input type="text" name="title" placeholder="Job Title" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['title'] : ''; ?>" required>
                <textarea name="description" placeholder="Description" class="input-field w-full mb-4" required><?php echo isset($edit_job) ? $edit_job['description'] : ''; ?></textarea>
                <input type="text" name="company" placeholder="Company" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['company'] : ''; ?>" required>
                <input type="text" name="location" placeholder="Location" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['location'] : ''; ?>">
                <input type="text" name="field" placeholder="Field (e.g., IT)" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['field'] : ''; ?>" required>
                <input type="text" name="interest" placeholder="Interest (e.g., Full-time)" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['interest'] : ''; ?>" required>
                <input type="date" name="expiration_date" class="input-field w-full mb-4" value="<?php echo isset($edit_job) ? $edit_job['expiration_date'] : ''; ?>" required>
                <button type="submit" name="<?php echo isset($_GET['edit']) ? 'edit_job' : 'add_job'; ?>" class="submit-btn w-full text-white">Save Job</button>
            </form>
        </div>

        <!-- Job Listings -->
        <div class="glass p-6 rounded-xl mb-8">
            <h3 class="text-xl font-bold mb-4">Job Opportunities</h3>
            <?php while ($job = $jobs_result->fetch_assoc()): ?>
                <div class="bg-gray-800 p-4 rounded-lg mb-4">
                    <h4 class="text-lg font-bold text-white"><?php echo htmlspecialchars($job['title']); ?></h4>
                    <p class="text-gray-200"><?php echo htmlspecialchars($job['description']); ?></p>
                    <p class="text-gray-300 mt-2">
                        <i class="fas fa-building mr-1 text-indigo-400"></i> <?php echo htmlspecialchars($job['company']); ?> | 
                        <i class="fas fa-calendar-alt mr-1 text-indigo-400"></i> Expires: <?php echo $job['expiration_date']; ?>
                    </p>
                    <div class="mt-3 flex space-x-2">
                        <form method="POST" class="inline">
                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                            <button type="submit" name="delete_job" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-white transition">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </button>
                        </form>
                        <a href="admin_dashboard.php?edit=<?php echo $job['id']; ?>" class="bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded text-white transition inline-block">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <a href="jobs.php?id=<?php echo $job['id']; ?>" class="bg-green-500 hover:bg-green-600 px-3 py-1 rounded text-white transition inline-block">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Registered Users -->
        <div class="glass p-6 rounded-xl mb-8">
            <h3 class="text-xl font-bold mb-4">Registered Users</h3>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <p class="text-gray-300"><?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['email']; ?>)</p>
            <?php endwhile; ?>
        </div>

        <!-- Push Notifications -->
        <div class="glass p-6 rounded-xl">
            <h3 class="text-xl font-bold mb-4">Send Notification</h3>
            <?php if (isset($notif_message)) echo "<p class='text-indigo-400 mb-4'>$notif_message</p>"; ?>
            <form method="POST">
                <textarea name="notification_message" placeholder="Notification Message" class="input-field w-full mb-4" required></textarea>
                <button type="submit" name="send_notification" class="submit-btn w-full text-white">Send</button>
            </form>
        </div>
    </section>
</body>
</html>

<?php $conn->close(); ?>