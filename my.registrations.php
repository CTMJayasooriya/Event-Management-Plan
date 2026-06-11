<?php
require_once 'config/database.php';
requireLogin();

$message = '';

// Handle cancellation
if (isset($_GET['cancel']) && isset($_GET['reg_id'])) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            UPDATE registrations 
            SET status = 'cancelled' 
            WHERE reg_id = ? AND user_id = ?
        ");
        $stmt->execute([$_GET['reg_id'], $_SESSION['user_id']]);
        $message = 'Registration cancelled successfully';
    } catch (PDOException $e) {
        $message = 'Failed to cancel registration';
    }
}

// Get user's registrations
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT r.*, e.title, e.description, e.date, e.time, e.venue, e.organizer
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.user_id = ?
        ORDER BY e.date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to load registrations';
    $registrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Student Event Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">🎓 Event Manager</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="my_registrations.php">My Registrations</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 style="text-align: center; margin: 2rem 0;">My Event Registrations</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($registrations)): ?>
            <div class="alert alert-info">
                <p style="text-align: center;">You haven't registered for any events yet.</p>
                <p style="text-align: center;"><a href="events.php" class="btn btn-primary">Browse Events</a></p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Student ID</th>
                            <th>Registered On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['title']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars(substr($reg['description'], 0, 50)) . '...'; ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($reg['date'])); ?>
                                    <br>
                                    <?php echo date('g:i A', strtotime($reg['time'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($reg['venue']); ?></td>
                                <td><?php echo htmlspecialchars($reg['student_id']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($reg['registration_date'])); ?></td>
                                <td>
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <span style="color: #28a745; font-weight: bold;">✓ Confirmed</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545; font-weight: bold;">✗ Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reg['status'] === 'confirmed' && strtotime($reg['date']) >= strtotime(date('Y-m-d'))): ?>
                                        <a href="?cancel=1&reg_id=<?php echo $reg['reg_id']; ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to cancel this registration?')">
                                            Cancel
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>