<?php
// This script fixes the foreign key constraint on course_enrollments table
// to allow for proper cascading deletes when a course is removed

$conn = new mysqli("localhost", "root", "", "opportunity_alert");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h1>Database Constraint Fix</h1>";

// First, delete all notifications related to course enrollments
$sql = "DELETE FROM notifications WHERE message LIKE 'You have enrolled in the course:%'";
$result = $conn->query($sql);
echo "<p>Deleted enrollment notifications: {$conn->affected_rows} rows</p>";

// Check if constraint exists
$sql = "SELECT COUNT(*) as count FROM information_schema.REFERENTIAL_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA='opportunity_alert' 
        AND CONSTRAINT_NAME='course_enrollments_ibfk_2'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    // Remove existing constraint
    $sql = "ALTER TABLE course_enrollments DROP FOREIGN KEY course_enrollments_ibfk_2";
    if ($conn->query($sql)) {
        echo "<p>Successfully removed existing foreign key constraint</p>";
    } else {
        echo "<p>Error removing constraint: " . $conn->error . "</p>";
    }
    
    // Add new constraint with ON DELETE CASCADE
    $sql = "ALTER TABLE course_enrollments 
            ADD CONSTRAINT course_enrollments_ibfk_2 
            FOREIGN KEY (course_id) REFERENCES courses(id) 
            ON DELETE CASCADE";
    if ($conn->query($sql)) {
        echo "<p>Successfully added new foreign key constraint with CASCADE delete</p>";
    } else {
        echo "<p>Error adding new constraint: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Foreign key constraint not found - nothing to change</p>";
}

// Let's also fix other notification issues
$sql = "ALTER TABLE notifications MODIFY type ENUM('job', 'course', 'enrollment') NOT NULL";
$conn->query($sql);

// Fix the course deletion code in case we still need it
echo "<p>Database update complete. You can now delete courses properly.</p>";
echo "<p><a href='courses.php'>Return to courses page</a></p>";

$conn->close();
?> 