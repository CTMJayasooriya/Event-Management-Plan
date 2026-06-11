<?php
require_once 'config/database.php';

try {
    $conn = getDBConnection();
    
    // Get all upcoming events
    $stmt = $conn->prepare("
        SELECT e.*, 
               COUNT(r.reg_id) as registered_count
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.status = 'confirmed'
        WHERE e.date >= CURDATE()
        GROUP BY e.event_id
        ORDER BY e.date ASC, e.time ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to load events';
    $events = [];
}
?>

<html>
<head>
    
    <title>Events - Student Event Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-image: url('back.png'); background-size: cover; background-repeat: no-repeat;">
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

    <div class="container" >
        <h1 style="text-align: center; margin: 2rem 0;">Upcoming Events</h1>

        <!-- Search and Filter -->
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search events by title, venue, or organizer..." class="form-control">
                <select id="sortSelect" class="form-control" style="max-width: 200px;">
                    <option value="">Sort By</option>
                    <option value="date">Date</option>
                    <option value="title">Title</option>
                </select>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <div class="alert alert-info">
                <p style="text-align: center;">No upcoming events at the moment. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($events as $event): ?>
                    <?php
                    $spotsLeft = $event['capacity'] - $event['registered_count'];
                    $isFull = $spotsLeft <= 0;
                    ?>
                    <div class="card">
                        <div class="card-header" style="background-image: url('img.png'); background-size: cover; background-repeat: no-repeat;">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <small><?php echo date('F d, Y', strtotime($event['date'])); ?></small>
                        </div>
                        <div class="card-body" >
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="card-info">
                                 <?php echo date('M d, Y', strtotime($event['date'])); ?> at <?php echo date('g:i A', strtotime($event['time'])); ?>
                            </div>
                            <div class="card-info">
                                 <?php echo htmlspecialchars($event['venue']); ?>
                            </div>
                            <div class="card-info">
                                Organizer: <?php echo htmlspecialchars($event['organizer']); ?>
                            </div>
                            <div class="card-info">
                                <?php echo $event['registered_count']; ?> / <?php echo $event['capacity']; ?> registered
                                <?php if ($isFull): ?>
                                    <span style="color: red; font-weight: bold;">(FULL)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn()): ?>
                                <?php if ($isFull): ?>
                                    <button class="btn btn-secondary" disabled>Event Full</button>
                                <?php else: ?>
                                    <a href="register_event.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary">Register Now</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Login to Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/search.js"></script>
</body>
</html>