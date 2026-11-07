<?php
include 'config.php'; // Include database connection file

// Fetch all registrations in descending order by ID
$query = "SELECT * FROM registrations ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Vanni Campus Connect</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Admin Dashboard - Registrations & Attendance</h2>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Event</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['event_name']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td>
                <!-- Action links with JavaScript confirmation for deletion -->
                <a href="moderate.php?id=<?php echo $row['id']; ?>&action=approve">Approve</a> | 
                <a href="moderate.php?id=<?php echo $row['id']; ?>&action=delete" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>