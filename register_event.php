<?php
require_once 'config/database.php';
requireLogin();

$error = '';
$success = '';

// Get event details
if (!isset($_GET['event_id'])) {
    header('Location: events.php');
    exit();
}

$eventId = (int)$_GET['event_id'];

try {
    $conn = getDBConnection();
    
    // Get event details
    $stmt = $conn->prepare("
        SELECT e.*, 
               COUNT(r.reg_id) as registered_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'confirmed'
        WHERE e.event_id = ?
        GROUP BY e.event_id
    ");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: events.php');
        exit();
    }
    
    // Check if already registered
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$_SESSION['user_id'], $eventId]);
    $existingReg = $stmt->fetch();
    
    if ($existingReg && $existingReg['status'] === 'confirmed') {
        $error = 'You are already registered for this event.';
    }
    
    // Check if event is full
    $spotsLeft = $event['capacity'] - $event['registered_count'];
    if ($spotsLeft <= 0) {
        $error = 'Sorry, this event is currently full.';
    }
    
} catch (PDOException $e) {
    $error = 'Failed to load event details.';
}

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $studentId = trim($_POST['student_id'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    
    if (empty($studentId) || empty($contactNumber)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Check if previously registered and cancelled
            if ($existingReg && $existingReg['status'] === 'cancelled') {
                $stmt = $conn->prepare("
                    UPDATE registrations 
                    SET student_id = ?, contact_number = ?, status = 'confirmed', registration_date = CURRENT_TIMESTAMP
                    WHERE reg_id = ?
                ");
                $stmt->execute([$studentId, $contactNumber, $existingReg['reg_id']]);
                $success = 'Registration reactivated! You are now registered for this event.';
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO registrations (user_id, event_id, student_id, contact_number) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $eventId, $studentId, $contactNumber]);
                $success = 'Registration successful! You are now registered for this event.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'You are already registered for this event.';
            } else {
                $error = 'Registration failed. Please try again: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - Student Event Management</title>
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
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="my_registrations.php">My Registrations</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success" style="max-width: 600px; margin: 2rem auto; text-align: center;">
                <h3>🎉 Success!</h3>
                <p><?php echo htmlspecialchars($success); ?></p>
                <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 1rem;">
                    <a href="events.php" class="btn btn-outline">View All Events</a>
                    <a href="my_registrations.php" class="btn btn-primary">My Registrations</a>
                </div>
            </div>
        <?php else: ?>
            <div style="max-width: 700px; margin: 0 auto;">
                <div class="page-header">
                    <h1>Event Registration</h1>
                    <p>Confirm your attendance for the selected event.</p>
                </div>

                <!-- Event Details Card -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                        <div class="card-info">
                            📅 <?php echo date('F d, Y', strtotime($event['date'])); ?> at <?php echo date('g:i A', strtotime($event['time'])); ?>
                        </div>
                        <div class="card-info">
                            📍 Venue: <?php echo htmlspecialchars($event['venue']); ?>
                        </div>
                        <div class="card-info">
                            👤 Organizer: <?php echo htmlspecialchars($event['organizer']); ?>
                        </div>
                        <div class="card-info">
                            👥 Available Seats: <strong><?php echo max(0, $spotsLeft); ?></strong> / <?php echo $event['capacity']; ?>
                        </div>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                        <div style="margin-top: 1rem;">
                            <a href="events.php" class="btn btn-secondary btn-sm">← Back to Events</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Registration Form -->
                    <div class="form-container" style="max-width: 100%; margin: 0;">
                        <h3 style="text-align: center; margin-bottom: 1.5rem; color: var(--primary);">Complete Your Registration</h3>
                        
                        <form method="POST" onsubmit="return validateRegistrationForm()">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="student_id">Student ID / Registration No. *</label>
                                <input type="text" id="student_id" name="student_id" class="form-control" 
                                       placeholder="e.g. IT/2024/001" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_number">Contact Number *</label>
                                <input type="tel" id="contact_number" name="contact_number" class="form-control" 
                                       placeholder="e.g. 0771234567" required>
                            </div>

                            <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1rem;">Confirm Registration</button>
                        </form>

                        <p style="text-align: center; margin-top: 1.25rem;">
                            <a href="events.php">← Back to Events</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <footer>
                <p>Institute of Technology, University of Moratuwa.</p>
                <p>Email: itum@mrt.ac.lk</p>
            </footer>
        </div>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>
