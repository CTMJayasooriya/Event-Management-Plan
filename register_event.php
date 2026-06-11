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

$eventId = $_GET['event_id'];

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
    
    if ($existingReg) {
        $error = 'You are already registered for this event';
    }
    
    // Check if event is full
    $spotsLeft = $event['capacity'] - $event['registered_count'];
    if ($spotsLeft <= 0) {
        $error = 'Sorry, this event is full';
    }
    
} catch (PDOException $e) {
    $error = 'Failed to load event details';
}

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $studentId = trim($_POST['student_id']);
    $contactNumber = trim($_POST['contact_number']);
    
    if (empty($studentId) || empty($contactNumber)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO registrations (user_id, event_id, student_id, contact_number) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $eventId, $studentId, $contactNumber]);
            
            $success = 'Registration successful! You are now registered for this event.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'You are already registered for this event';
            } else {
                $error = 'Registration failed. Please try again.';
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
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <br><br>
                <a href="events.php" class="btn btn-primary">View All Events</a>
                <a href="my_registrations.php" class="btn btn-secondary">View My Registrations</a>
            </div>
        <?php else: ?>
            <div style="max-width: 800px; margin: 0 auto;">
                <h2 style="text-align: center; margin: 2rem 0;">Event Registration</h2>

                <!-- Event Details -->
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
                            👥 Available Spots: <?php echo $spotsLeft; ?> / <?php echo $event['capacity']; ?>
                        </div>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                        <br><br>
                        <a href="events.php" class="btn btn-secondary">Back to Events</a>
                    </div>
                <?php else: ?>
                    <!-- Registration Form -->
                    <div class="form-container">
                        <h3 style="text-align: center; margin-bottom: 1.5rem;">Complete Your Registration</h3>
                        
                        <form method="POST" onsubmit="return validateRegistrationForm()">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['name']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="student_id">Student ID *</label>
                                <input type="text" id="student_id" name="student_id" class="form-control" 
                                       placeholder="Enter your student ID">
                            </div>

                            <div class="form-group">
                                <label for="contact_number">Contact Number *</label>
                                <input type="tel" id="contact_number" name="contact_number" class="form-control" 
                                       placeholder="Enter your contact number">
                            </div>

                            <button type="submit" class="btn btn-success" style="width: 100%;">Complete Registration</button>
                        </form>

                        <p style="text-align: center; margin-top: 1rem;">
                            <a href="events.php">← Back to Events</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>