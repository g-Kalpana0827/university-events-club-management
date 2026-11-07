<?php
include 'config.php'; // Include database connection file

// Check if request parameters are present
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    // Handle approval or deletion actions
    if ($action == 'approve') {
        $query = "UPDATE registrations SET status = 'Approved' WHERE id = $id";
        mysqli_query($conn, $query);
    } elseif ($action == 'delete') {
        $query = "DELETE FROM registrations WHERE id = $id";
        mysqli_query($conn, $query);
    }

    // Redirect back to the admin dashboard after processing
    header("Location: admin_dashboard.php");
    exit();
}
?>