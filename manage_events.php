<?php
include 'config.php'; // Include database connection file

// Handle new event creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $insert_query = "INSERT INTO events (event_name, event_date, description) VALUES ('$event_name', '$event_date', '$description')";
    mysqli_query($conn, $insert_query);
    header("Location: manage_events.php");
    exit();
}

// Fetch all existing events
$events_query = "SELECT * FROM events ORDER BY id DESC";
$events_result = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - Admin</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .form-container { background: #f9f9f9; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Manage Events</h2>

    <!-- Add New Event Form -->
    <div class="form-container">
        <h3>Add New Event</h3>
        <form method="POST">
            <input type="text" name="event_name" placeholder="Event Name" required>
            <input type="date" name="event_date" required>
            <textarea name="description" placeholder="Event Description"></textarea>
            <button type="submit" name="add_event">Create Event</button>
        </form>
    </div>

    <!-- Events List Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Event Name</th>
            <th>Date</th>
            <th>Description</th>
        </tr>
        <?php if ($events_result && mysqli_num_rows($events_result) > 0) { ?>
            <?php while($row = mysqli_fetch_assoc($events_result)) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['event_name']; ?></td>
                <td><?php echo $row['event_date']; ?></td>
                <td><?php echo $row['description']; ?></td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="4">No events found.</td></tr>
        <?php } ?>
    </table>
</body>
</html>