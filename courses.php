<?php
session_start();
$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch username if user is logged in
$username = "";
$role = "user";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
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
}

// Handle course deletion (admin only)
if ($role === 'admin' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
    
    // Delete any notifications related to this course
    $delete_notifications_sql = "DELETE FROM notifications WHERE (type = 'course' OR type = 'enrollment') AND item_id = ?";
    $stmt = $conn->prepare($delete_notifications_sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
    
    // Then delete the course (enrollments will be deleted automatically due to ON DELETE CASCADE)
    $sql = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    if ($stmt->execute()) {
        // Redirect to refresh page
        header("Location: courses.php?deleted=success");
        exit();
    } else {
        if ($conn->errno === 1451) { // Foreign key constraint error
            $error = "Cannot delete course: It is referenced by other data. Please run the fix_db_constraint.php script first.";
        } else {
            $error = "Failed to delete course: " . $conn->error;
        }
    }
    $stmt->close();
}

// Fetch all courses
$sql = "SELECT id, title, description, provider, duration, field, bg_image FROM courses ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - Opportunity Alert</title>
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
        .input-field { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(99, 102, 241, 0.5); padding: 8px; border-radius: 5px; color: #ffffff; }
        .submit-btn { background: linear-gradient(90deg, #6366f1, #9333ea); padding: 10px; border-radius: 5px; color: #ffffff; }
        .category-badge { color: #ffffff; }
        .text-indigo-400 { color: #ffffff; }
        .course-title {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
            letter-spacing: 0.5px;
        }
        .course-description {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
            line-height: 1.4;
        }
        .course-detail-panel {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .category-it { background: rgba(59, 130, 246, 0.6); color: #ffffff; border: 1px solid rgba(59, 130, 246, 0.8); }
        .category-design { background: rgba(168, 85, 247, 0.6); color: #ffffff; border: 1px solid rgba(168, 85, 247, 0.8); }
        .category-finance { background: rgba(34, 197, 94, 0.6); color: #ffffff; border: 1px solid rgba(34, 197, 94, 0.8); }
        .category-marketing { background: rgba(249, 115, 22, 0.6); color: #ffffff; border: 1px solid rgba(249, 115, 22, 0.8); }
        .category-education { background: rgba(14, 165, 233, 0.6); color: #ffffff; border: 1px solid rgba(14, 165, 233, 0.8); }
        .category-healthcare { background: rgba(236, 72, 153, 0.6); color: #ffffff; border: 1px solid rgba(236, 72, 153, 0.8); }
        .category-engineering { background: rgba(139, 92, 246, 0.6); color: #ffffff; border: 1px solid rgba(139, 92, 246, 0.8); }
        .category-hospitality { background: rgba(249, 168, 37, 0.6); color: #ffffff; border: 1px solid rgba(249, 168, 37, 0.8); }
        .category-retail { background: rgba(234, 88, 12, 0.6); color: #ffffff; border: 1px solid rgba(234, 88, 12, 0.8); }
        .category-construction { background: rgba(202, 138, 4, 0.6); color: #ffffff; border: 1px solid rgba(202, 138, 4, 0.8); }
        .category-media { background: rgba(219, 39, 119, 0.6); color: #ffffff; border: 1px solid rgba(219, 39, 119, 0.8); }
        .category-legal { background: rgba(71, 85, 105, 0.6); color: #ffffff; border: 1px solid rgba(71, 85, 105, 0.8); }
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
                <?php if (empty($username)): ?>
                    <a href="login.php" class="bg-indigo-500 px-4 py-2 rounded-full neon-glow">Login</a>
                    <a href="register.php" class="bg-gradient-to-r from-purple-500 to-indigo-500 px-4 py-2 rounded-full neon-glow">Register</a>
                <?php else: ?>
                    <span class="px-4 py-2 text-indigo-400 hover:bg-indigo-500/20 rounded-full transition">Welcome, 
                        <a href="<?php echo $role === 'admin' ? 'admin-register.php' : 'dashboard.php'; ?>" class="hover:underline">
                            <?php echo $username; ?>
                        </a> 
                        (<?php echo $role; ?>)
                    </span>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="bg-gradient-to-r from-indigo-900 via-purple-900 to-black pt-24 pb-8 relative shadow-md">
        <div class="container mx-auto px-4 relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-indigo-400 to-purple-500 bg-clip-text text-transparent mb-6 animate__animated animate__fadeIn">Browse Courses</h1>
            <div class="flex flex-col md:flex-row gap-8 mb-5">
                <div class="md:w-2/3">
                    <p class="text-xl mb-6 animate__animated animate__fadeIn animate__delay-1s text-gray-300">Discover top courses to boost your skills and advance your career!</p>
                    
                    <!-- Search Box -->
                    <div class="glass rounded-lg p-4 mt-8 shadow-md animate__animated animate__fadeIn animate__delay-1s">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-grow">
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search text-indigo-400 group-hover:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" id="search-input" class="input-field w-full pl-10 p-2" placeholder="Search for courses...">
                                </div>
                            </div>
                            <div>
                                <select id="category-filter" class="input-field w-full p-2">
                                    <option selected>All Fields</option>
                                    <option value="it">IT</option>
                                    <option value="design">Design</option>
                                    <option value="finance">Finance</option>
                                    <option value="marketing">Marketing</option>
                                    <option value="education">Education</option>
                                    <option value="healthcare">Healthcare</option>
                                    <option value="engineering">Engineering</option>
                                    <option value="hospitality">Hospitality</option>
                                    <option value="retail">Retail</option>
                                    <option value="construction">Construction</option>
                                    <option value="media">Media</option>
                                    <option value="legal">Legal</option>
                                </select>
                            </div>
                            <div>
                                <select id="sort-filter" class="input-field w-full p-2">
                                    <option selected>Sort By</option>
                                    <option value="newest">Newest</option>
                                    <option value="duration">Duration</option>
                                </select>
                            </div>
                            <button id="apply-filters" class="submit-btn text-white font-medium py-2 px-4">
                                <i class="fas fa-filter mr-1"></i>Apply
                            </button>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/3 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=900" alt="Course Search" class="max-h-48 object-contain hidden md:block animate__animated animate__fadeIn animate__delay-2s">
                </div>
            </div>
        </div>
    </header>

    <!-- Courses Section -->
    <section class="py-12 container mx-auto px-4">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
            <div class="glass rounded-lg p-4 mb-6 text-center text-green-400 animate__animated animate__fadeIn">
                Course deleted successfully!
            </div>
        <?php elseif (isset($error)): ?>
            <div class="glass rounded-lg p-4 mb-6 text-center text-red-400 animate__animated animate__fadeIn">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <div id="courses-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $course_id = $row['id'];
                    $title = htmlspecialchars($row['title']);
                    $description = htmlspecialchars($row['description'] ?: 'No description available.');
                    $provider = htmlspecialchars($row['provider']);
                    $field = htmlspecialchars($row['field']);
                    $duration = htmlspecialchars($row['duration'] ?: 'N/A');
                    $category_class = [
                        'it' => 'category-it',
                        'design' => 'category-design',
                        'finance' => 'category-finance',
                        'marketing' => 'category-marketing',
                        'education' => 'category-education',
                        'healthcare' => 'category-healthcare',
                        'engineering' => 'category-engineering',
                        'hospitality' => 'category-hospitality',
                        'retail' => 'category-retail',
                        'construction' => 'category-construction',
                        'media' => 'category-media',
                        'legal' => 'category-legal'
                    ][strtolower($field)] ?? 'category-it';
                    ?>
                    <div class="glass rounded-xl shadow-md overflow-hidden card-hover animate__animated animate__fadeIn h-96 relative transform transition-all duration-300">
                        <div class="absolute inset-0">
                            <img src="<?php 
                                if ($row['bg_image']) {
                                    echo 'data:image/' . ($row['bg_image'][0] == 0xFF ? 'jpeg' : 'png') . ';base64,' . base64_encode($row['bg_image']);
                                } else {
                                    $field_images = [
                                        'it' => 'https://images.unsplash.com/photo-1550439062-609e1531270e?q=80&w=900',
                                        'design' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?q=80&w=900',
                                        'finance' => 'https://images.unsplash.com/photo-1591696205602-2f950c417cb9?q=80&w=900',
                                        'marketing' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=900',
                                        'education' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=900',
                                        'healthcare' => 'https://images.unsplash.com/photo-1631815588090-d1bcbe9d4b38?q=80&w=900',
                                        'engineering' => 'https://images.unsplash.com/photo-1581094296106-9fefa9d0f8bc?q=80&w=900',
                                        'hospitality' => 'https://images.unsplash.com/photo-1551882853-5ac90a9560a4?q=80&w=900',
                                        'retail' => 'https://images.unsplash.com/photo-1542744173-05336fcc7ad4?q=80&w=900',
                                        'construction' => 'https://images.unsplash.com/photo-1503387837-b154d5074bd2?q=80&w=900',
                                        'media' => 'https://images.unsplash.com/photo-1532649538693-f3a2ec1bf8bd?q=80&w=900',
                                        'legal' => 'https://images.unsplash.com/photo-1575505586569-646b2ca898fc?q=80&w=900'
                                    ];
                                    $field_lower = strtolower($field);
                                    echo isset($field_images[$field_lower]) ? $field_images[$field_lower] : 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=900';
                                }
                            ?>" alt="<?php echo $field; ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/90 to-black/70"></div>
                        </div>
                        <div class="absolute inset-0 p-6 flex flex-col justify-between">
                            <div>
                                <span class="category-badge <?php echo $category_class; ?> font-semibold text-white"><?php echo $field; ?></span>
                                <div class="course-detail-panel px-3 py-2 mt-2">
                                    <h3 class="text-2xl font-bold mb-3 text-white course-title"><?php echo $title; ?></h3>
                                    <p class="text-white text-sm line-clamp-3 course-description"><?php echo $description; ?></p>
                                </div>
                            </div>
                            <div>
                                <div class="course-detail-panel p-2 mb-4 flex justify-between font-medium text-sm">
                                    <span class="text-white"><i class="fas fa-university mr-1 text-indigo-300"></i> <?php echo $provider; ?></span>
                                    <span class="text-white"><i class="fas fa-clock mr-1 text-indigo-300"></i> <?php echo $duration; ?></span>
                                </div>
                                <?php if ($role === 'admin'): ?>
                                    <a href="courses.php?action=delete&id=<?php echo $course_id; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this course?');"
                                       class="block text-center bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-bold w-full transition duration-300 neon-glow">
                                        Delete Course
                                    </a>
                                <?php else: ?>
                                    <a href="enroll-course.php?id=<?php echo $course_id; ?>" 
                                       class="block text-center bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-purple-600 hover:to-indigo-700 text-white py-3 rounded-lg transition duration-300 neon-glow font-bold shadow-lg">
                                        Enroll Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-center text-gray-300 col-span-3">No courses available yet. Check back soon!</p>';
            }
            ?>
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

    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const coursesGrid = document.getElementById('courses-grid');
        const courseCards = Array.from(coursesGrid.querySelectorAll('.card-hover'));
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const sortFilter = document.getElementById('sort-filter');
        const applyFiltersBtn = document.getElementById('apply-filters');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value.toLowerCase();
            const selectedSort = sortFilter.value;

            let filteredCards = courseCards.filter(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const provider = card.querySelector('.fa-university').nextSibling.textContent.toLowerCase();
                const category = card.querySelector('span').textContent.toLowerCase();
                const matchesSearch = title.includes(searchTerm) || provider.includes(searchTerm);
                const matchesCategory = selectedCategory === 'all fields' || category === selectedCategory;
                return matchesSearch && matchesCategory;
            });

            if (selectedSort === 'newest') {
                filteredCards.sort((a, b) => {
                    const idA = parseInt(a.querySelector('a')?.getAttribute('href')?.split('=')[1] || 0);
                    const idB = parseInt(b.querySelector('a')?.getAttribute('href')?.split('=')[1] || 0);
                    return idB - idA;
                });
            } else if (selectedSort === 'duration') {
                filteredCards.sort((a, b) => {
                    const durationA = a.querySelector('.fa-clock').nextSibling.textContent.toLowerCase();
                    const durationB = b.querySelector('.fa-clock').nextSibling.textContent.toLowerCase();
                    return durationA.localeCompare(durationB);
                });
            }

            coursesGrid.innerHTML = '';
            if (filteredCards.length > 0) {
                filteredCards.forEach(card => coursesGrid.appendChild(card));
            } else {
                coursesGrid.innerHTML = '<p class="text-center text-gray-300 col-span-3">No matching courses found.</p>';
            }
        }

        applyFiltersBtn.addEventListener('click', applyFilters);
        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);
        sortFilter.addEventListener('change', applyFilters);
    });
    
    function showAdminCourseAlert() {
        alert("As an admin, you cannot enroll in courses. You can only manage and view course listings.");
    }
    </script>
</body>
</html>

<?php $conn->close(); ?>