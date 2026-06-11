<?php
require_once 'config/database.php';
?>

<html>
<head>
    <title>Student Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body style="background-image: url('back.png'); background-size: cover; background-repeat: no-repeat;">
    <!-- Navigation -->
    <nav class="navbar" style="background-image: url('img.png'); background-size: cover; background-repeat: no-repeat;">
        <div class="nav-container">
            <a href="index.php" class="nav-brand"><img src ="itumlogo.png" height="100" width="300"></a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="my_registrations.php">My Registrations</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container" >
         
        <div class="hero" style="background-image: url('img.png'); background-size: cover; background-repeat: no-repeat;">
            <h1>Welcome to Student Event Management</h1>
            <p>Discover, Register, and Participate in Amazing University Events</p>
            <?php if (!isLoggedIn()): ?>
                <a href="signup.php" class="btn btn-success">Get Started</a>
                <a href="events.php" class="btn btn-secondary">Browse Events</a>
            <?php else: ?>
                <a href="events.php" class="btn btn-success">Browse Events</a>
            <?php endif; ?>
        </div>

        <!-- Features Section -->
        <h2 style="text-align: center; margin: 3rem 0 2rem;">Why Use Our Platform?</h2>
        <div class="cards-grid">
            <div class="card">
                <div class="card-header" style="background-image: url('img2.png'); background-size: cover; background-repeat: no-repeat;">
                    <h3> Easy Registration</h3>
                </div>
                <div class="card-body">
                    <p>Register for events with just a few clicks. Simple and intuitive interface designed for students.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-image: url('img2.png'); background-size: cover; background-repeat: no-repeat;">
                    <h3> Smart Search</h3>
                </div>
                <div class="card-body">
                    <p>Find events that match your interests using our powerful search and filter features.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-image: url('img2.png'); background-size: cover; background-repeat: no-repeat;">
                    <h3>Track Participation</h3>
                </div>
                <div class="card-body">
                    <p>Keep track of all your registered events in one place. Never miss an important event.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background-image: url('img2.png'); background-size: cover; background-repeat: no-repeat;">
                    <h3>Admin Tools</h3>
                </div>
                <div class="card-body">
                    <p>Organizers can manage events, view registrations, and track attendance efficiently.</p>
                </div>
            </div>
        </div>

        <div class="eximage">
            <img src = "img1.png" width="1100" height="600" >
        </div>

        <?php
        // Display upcoming events
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("
                SELECT * FROM events 
                WHERE date >= CURDATE() 
                ORDER BY date ASC 
                LIMIT 4
            ");
            $stmt->execute();
            $upcomingEvents = $stmt->fetchAll();

            if (count($upcomingEvents) > 0):
        ?>
        <h2 style="text-align: center; margin: 3rem 0 2rem;">Upcoming Events</h2>
        <div class="cards-grid">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="card">
                <div class="card-header" style="background-image: url('img.png'); background-size: cover; background-repeat: no-repeat;">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                </div>
                <div class="card-body">
                    <p><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                    <div class="card-info">
                        <?php echo date('M d, Y', strtotime($event['date'])); ?> at <?php echo date('g:i A', strtotime($event['time'])); ?>
                    </div>
                    <div class="card-info">
                        <?php echo htmlspecialchars($event['venue']); ?>
                    </div>
                    <div class="card-info">
                         <?php echo htmlspecialchars($event['organizer']); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="events.php" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php 
            endif;
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error loading events.</p>";
        }
        ?>
        <div class="footer">
            <footer>
                <p>Institute of technology University of Moratuwa.</p>
                <p>Email:itum@mrt.ac.lk</p>
            </footer>
             </div>

    </div>
</body>
</html>