<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Event Management System - ITUM</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body style="background: url('back.png') no-repeat center center fixed; background-size: cover; background-color: #eaf3f9;">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="itumlogo.png" alt="ITUM Logo" onerror="this.style.display='none'">
                <span>Event Management</span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Home</a></li>
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

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <h1>Welcome to Student Event Management</h1>
            <p>Discover, Register, and Participate in Amazing University Events at ITUM</p>
            <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
                <?php if (!isLoggedIn()): ?>
                    <a href="signup.php" class="btn btn-success">Get Started</a>
                    <a href="events.php" class="btn btn-secondary">Browse Events</a>
                <?php else: ?>
                    <a href="events.php" class="btn btn-success">Browse Events</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn btn-secondary">Go to Admin Dashboard</a>
                    <?php else: ?>
                        <a href="my_registrations.php" class="btn btn-secondary">View My Registrations</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Features Section -->
        <div class="page-header">
            <h2>Why Use Our Platform?</h2>
            <p>Built specifically to streamline university event registration, discovery, and administration.</p>
        </div>

        <div class="cards-grid">
            <div class="card">
                <div class="card-header">
                    <h3>⚡ Easy Registration</h3>
                </div>
                <div class="card-body">
                    <p>Register for campus events with just a few clicks. Real-time seat availability checking ensures hassle-free reservations.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>🔍 Smart Search</h3>
                </div>
                <div class="card-body">
                    <p>Quickly locate events that match your interests by searching across titles, venues, organizers, or dates.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>📊 Track Participation</h3>
                </div>
                <div class="card-body">
                    <p>Keep track of all your confirmed registrations in one unified dashboard. Cancel or reschedule with ease.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>🛠️ Admin Tools</h3>
                </div>
                <div class="card-body">
                    <p>Organizers and administrators can create events, monitor attendee caps, and export attendee rosters directly to CSV.</p>
                </div>
            </div>
        </div>

        <div class="eximage">
            <img src="img1.png" alt="University Event Highlights" onerror="this.style.display='none'">
        </div>

        <!-- Upcoming Events Highlight -->
        <?php
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                SELECT e.*, 
                       COUNT(r.reg_id) as registered_count
                FROM events e
                LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'confirmed'
                WHERE e.date >= CURDATE() 
                GROUP BY e.event_id
                ORDER BY e.date ASC 
                LIMIT 4
            ");
            $stmt->execute();
            $upcomingEvents = $stmt->fetchAll();

            if (!empty($upcomingEvents)):
        ?>
        <div class="page-header" style="margin-top: 3rem;">
            <h2>Upcoming Events</h2>
            <p>Don't miss out on these upcoming university sessions and competitions!</p>
        </div>

        <div class="cards-grid">
            <?php foreach ($upcomingEvents as $event): ?>
            <?php 
                $spotsLeft = $event['capacity'] - $event['registered_count'];
                $isFull = $spotsLeft <= 0;
            ?>
            <div class="card">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <small>📅 <?php echo date('M d, Y', strtotime($event['date'])); ?></small>
                </div>
                <div class="card-body">
                    <p><?php echo htmlspecialchars(substr($event['description'], 0, 90)) . '...'; ?></p>
                    <div class="card-info">
                        🕒 <?php echo date('g:i A', strtotime($event['time'])); ?>
                    </div>
                    <div class="card-info">
                        📍 <?php echo htmlspecialchars($event['venue']); ?>
                    </div>
                    <div class="card-info">
                        👤 <?php echo htmlspecialchars($event['organizer']); ?>
                    </div>
                    <div class="card-info">
                        👥 <?php echo $event['registered_count']; ?> / <?php echo $event['capacity']; ?> registered
                        <?php if ($isFull): ?>
                            <span class="badge badge-danger">Full</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="events.php" class="btn btn-primary btn-sm">View & Register</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php 
            endif;
        } catch (PDOException $e) {
            // Silently handle if database is not yet initialized or connected
        }
        ?>

        <!-- Footer -->
        <div class="footer">
            <footer>
                <p>Institute of Technology, University of Moratuwa.</p>
                <p>Email: itum@mrt.ac.lk | Student Event Management System</p>
            </footer>
        </div>
    </div>
</body>
</html>
