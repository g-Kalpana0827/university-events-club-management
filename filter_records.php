<?php
include 'config.php'; // Include database connection file

// Initialize search variables
$search_keyword = "";
$status_filter = "";

// Build the base query
$query = "SELECT * FROM registrations WHERE 1=1";

// Check if search keyword is provided
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_keyword = mysqli_real_escape_string($conn, $_GET['search']);
    $query .= " AND (name LIKE '%$search_keyword%' OR email LIKE '%$search_keyword%')";
}

// Check if status filter is provided
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status']);
    $query .= " AND status = '$status_filter'";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filter Records - Admin</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .filter-form { margin-bottom: 20px; background: #f9f9f9; padding: 15px; }
    </style>
</head>
<body>
    <h2>Filter Registrations</h2>
    
    <!-- Filter and Search Form -->
    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_keyword); ?>">
        
        <select name="status">
            <option value="">All Statuses</option>
            <option value="Approved" <?php if($status_filter == 'Approved') echo 'selected'; ?>>Approved</option>
            <option value="Pending" <?php if($status_filter == 'Pending') echo 'selected'; ?>>Pending</option>
        </select>
        
        <button type="submit">Filter</button>
        <a href="filter_records.php">Reset</a>
    </form>

    <!-- Results Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Event</th>
            <th>Status</th>
        </tr>
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['event_name']; ?></td>
                <td><?php echo $row['status']; ?></td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="5">No records found.</td></tr>
        <?php } ?>
    </table>
</body>
</html>