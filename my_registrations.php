<?php
require_once 'config/database.php';
requireLogin();

$message = '';
$messageType = 'success';

// Handle registration cancellation
if (isset($_GET['cancel']) && isset($_GET['reg_id'])) {
    $regId = (int)$_GET['reg_id'];
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            UPDATE registrations 
            SET status = 'cancelled' 
            WHERE reg_id = ? AND user_id = ?
        ");
        $stmt->execute([$regId, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $message = 'Registration cancelled successfully.';
            $messageType = 'success';
        } else {
            $message = 'Registration could not be cancelled or was not found.';
            $messageType = 'warning';
        }
    } catch (PDOException $e) {
        $message = 'Failed to cancel registration: ' . $e->getMessage();
        $messageType = 'danger';
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
        ORDER BY e.date DESC, e.time DESC
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
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body style="background: url('back.png') no-repeat center center fixed; background-size: cover; background-color: #eaf3f9;">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="itumlogo.png" alt="ITUM Logo" onerror="this.style.display='none'">
                <span>Event Management</span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                <?php else: ?>
                    <li><a href="my_registrations.php" class="active">My Registrations</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>My Event Registrations</h1>
            <p>Track your registered university events and their current status.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($registrations)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎟️</div>
                <h3>No Registrations Yet</h3>
                <p>You haven't registered for any events yet. Explore upcoming university workshops, seminars, and hackathons!</p>
                <a href="events.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Events</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Date & Time</th>
                            <th>Venue & Organizer</th>
                            <th>Student ID</th>
                            <th>Registered On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                            <?php 
                                $isUpcoming = strtotime($reg['date']) >= strtotime(date('Y-m-d'));
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['title']); ?></strong>
                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($reg['description'], 0, 70)) . (strlen($reg['description']) > 70 ? '...' : ''); ?></div>
                                </td>
                                <td>
                                    <div>📅 <?php echo date('M d, Y', strtotime($reg['date'])); ?></div>
                                    <div class="text-muted small">🕒 <?php echo date('g:i A', strtotime($reg['time'])); ?></div>
                                </td>
                                <td>
                                    <div>📍 <?php echo htmlspecialchars($reg['venue']); ?></div>
                                    <div class="text-muted small">👤 <?php echo htmlspecialchars($reg['organizer']); ?></div>
                                </td>
                                <td>
                                    <code><?php echo htmlspecialchars($reg['student_id']); ?></code>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($reg['registration_date'])); ?></td>
                                <td>
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <span class="badge badge-success">✓ Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">✗ Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reg['status'] === 'confirmed' && $isUpcoming): ?>
                                        <a href="?cancel=1&reg_id=<?php echo $reg['reg_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to cancel this registration?');">
                                            Cancel
                                        </a>
                                    <?php elseif (!$isUpcoming): ?>
                                        <span class="text-muted small">Event Ended</span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="footer">
            <footer>
                <p>Institute of Technology, University of Moratuwa.</p>
                <p>Email: itum@mrt.ac.lk</p>
            </footer>
        </div>
    </div>
</body>
</html>
