<?php
include 'config.php'; // Include database connection file

// Fetch total count of registrations
$total_reg_query = "SELECT COUNT(*) as total FROM registrations";
$total_reg_result = mysqli_query($conn, $total_reg_query);
$total_reg = mysqli_fetch_assoc($total_reg_result)['total'];

// Fetch total count of approved registrations/attendances
$approved_query = "SELECT COUNT(*) as approved FROM registrations WHERE status = 'Approved'";
$approved_result = mysqli_query($conn, $approved_query);
$approved = mysqli_fetch_assoc($approved_result)['approved'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Reports</title>
</head>
<body>
    <h2>Event Metrics & Reports</h2>
    <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ccc; width: 300px;">
        <p><strong>Total Registrations:</strong> <?php echo $total_reg; ?></p>
        <p><strong>Approved / Attendance:</strong> <?php echo $approved; ?></p>
    </div>
    <br>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>