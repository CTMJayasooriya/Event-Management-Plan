<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$message = '';
$messageType = 'success';
$conn = getDBConnection();

// Handle Event Actions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $organizer = trim($_POST['organizer'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 50);

        if (empty($title) || empty($date) || empty($time) || empty($venue) || empty($organizer) || $capacity <= 0) {
            $message = 'Please fill in all required event details with a valid capacity.';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO events (title, description, date, time, venue, organizer, capacity, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $description, $date, $time, $venue, $organizer, $capacity, $_SESSION['user_id']]);
                $message = 'Event created successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to create event: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'update') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $organizer = trim($_POST['organizer'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 50);

        if ($eventId <= 0 || empty($title) || empty($date) || empty($time) || empty($venue) || empty($organizer) || $capacity <= 0) {
            $message = 'Please provide valid details to update the event.';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $conn->prepare("
                    UPDATE events 
                    SET title = ?, description = ?, date = ?, time = ?, venue = ?, organizer = ?, capacity = ?
                    WHERE event_id = ?
                ");
                $stmt->execute([$title, $description, $date, $time, $venue, $organizer, $capacity, $eventId]);
                $message = 'Event updated successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to update event: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        if ($eventId > 0) {
            try {
                $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
                $stmt->execute([$eventId]);
                $message = 'Event deleted successfully.';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to delete event: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Fetch System Statistics
$totalEvents = 0;
$totalRegistrations = 0;
$upcomingEventsCount = 0;
$totalStudents = 0;

try {
    $totalEvents = (int)$conn->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $totalRegistrations = (int)$conn->query("SELECT COUNT(*) FROM registrations WHERE status = 'confirmed'")->fetchColumn();
    $upcomingEventsCount = (int)$conn->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE()")->fetchColumn();
    $totalStudents = (int)$conn->query("SELECT COUNT(DISTINCT user_id) FROM users WHERE role = 'student'")->fetchColumn();

    // Fetch all events with confirmed registrations count
    $stmt = $conn->prepare("
        SELECT e.*, 
               COUNT(CASE WHEN r.status = 'confirmed' THEN 1 END) as confirmed_count,
               COUNT(r.reg_id) as total_registered
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        GROUP BY e.event_id
        ORDER BY e.date DESC, e.time DESC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to fetch dashboard data: ' . $e->getMessage();
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Event Management</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body style="background: url('../back.png') no-repeat center center fixed; background-size: cover; background-color: #eaf3f9;">
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="nav-brand">
                <img src="../itumlogo.png" alt="ITUM Logo" onerror="this.style.display='none'">
                <span>Admin Portal</span>
            </a>
            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../events.php">Browse Events</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="view_registrations.php">Registrations</a></li>
                <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header" style="text-align: left;">
            <h1>Administrator Dashboard</h1>
            <p>Manage university events, inspect registrations, and view participation metrics.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Quick Metrics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $totalEvents; ?></div>
                    <div class="stat-label">Total Events</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $upcomingEventsCount; ?></div>
                    <div class="stat-label">Upcoming Events</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $totalRegistrations; ?></div>
                    <div class="stat-label">Active Registrations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $totalStudents; ?></div>
                    <div class="stat-label">Registered Students</div>
                </div>
            </div>
        </div>

        <!-- Management Toolbar -->
        <div class="toolbar">
            <h2>All Events</h2>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">+ Create New Event</button>
                <a href="view_registrations.php" class="btn btn-secondary">📋 View All Registrations</a>
            </div>
        </div>

        <!-- Events Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Title</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Organizer</th>
                        <th>Registrations / Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">No events found. Click "+ Create New Event" to add your first event.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php 
                                $isUpcoming = strtotime($event['date']) >= strtotime(date('Y-m-d'));
                                $isFull = $event['confirmed_count'] >= $event['capacity'];
                            ?>
                            <tr>
                                <td>#<?php echo $event['event_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                    <div class="text-muted small"><?php echo htmlspecialchars(substr($event['description'], 0, 60)) . '...'; ?></div>
                                </td>
                                <td>
                                    <div><?php echo date('M d, Y', strtotime($event['date'])); ?></div>
                                    <div class="text-muted small"><?php echo date('g:i A', strtotime($event['time'])); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($event['venue']); ?></td>
                                <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                <td>
                                    <strong><?php echo $event['confirmed_count']; ?></strong> / <?php echo $event['capacity']; ?>
                                    <?php if ($isFull): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isUpcoming): ?>
                                        <span class="badge badge-success">Upcoming</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                        <a href="view_registrations.php?event_id=<?php echo $event['event_id']; ?>" 
                                           class="btn btn-sm btn-outline" title="View Registrations">
                                            👥 (<?php echo $event['confirmed_count']; ?>)
                                        </a>
                                        <button type="button" class="btn btn-sm btn-secondary" 
                                                onclick='openEditModal(<?php echo json_encode($event); ?>)'>
                                            ✏️ Edit
                                        </button>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this event? All associated registrations will also be removed.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <footer>
                <p>Institute of Technology, University of Moratuwa - Event Management System</p>
                <p>Admin Control Panel</p>
            </footer>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Event</h3>
                <button type="button" class="close-btn" onclick="closeModal('createEventModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="createEventForm" method="POST" onsubmit="return validateEventForm('createEventForm')">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="create_title">Event Title *</label>
                        <input type="text" id="create_title" name="title" class="form-control" required placeholder="e.g. AI Innovation Summit 2026">
                    </div>

                    <div class="form-group">
                        <label for="create_description">Description</label>
                        <textarea id="create_description" name="description" class="form-control" rows="3" placeholder="Provide a summary of the event..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="create_date">Date *</label>
                            <input type="date" id="create_date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="create_time">Time *</label>
                            <input type="time" id="create_time" name="time" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="create_venue">Venue *</label>
                        <input type="text" id="create_venue" name="venue" class="form-control" required placeholder="e.g. ITUM Main Auditorium">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="create_organizer">Organizer *</label>
                            <input type="text" id="create_organizer" name="organizer" class="form-control" required placeholder="e.g. ITUM Tech Society">
                        </div>
                        <div class="form-group">
                            <label for="create_capacity">Capacity (Seats) *</label>
                            <input type="number" id="create_capacity" name="capacity" class="form-control" min="1" value="50" required>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeModal('createEventModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Event</h3>
                <button type="button" class="close-btn" onclick="closeModal('editEventModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editEventForm" method="POST" onsubmit="return validateEventForm('editEventForm')">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_event_id" name="event_id">

                    <div class="form-group">
                        <label for="edit_title">Event Title *</label>
                        <input type="text" id="edit_title" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="edit_date">Date *</label>
                            <input type="date" id="edit_date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_time">Time *</label>
                            <input type="time" id="edit_time" name="time" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_venue">Venue *</label>
                        <input type="text" id="edit_venue" name="venue" class="form-control" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="edit_organizer">Organizer *</label>
                            <input type="text" id="edit_organizer" name="organizer" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_capacity">Capacity (Seats) *</label>
                            <input type="number" id="edit_capacity" name="capacity" class="form-control" min="1" required>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeModal('editEventModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/validation.js"></script>
    <script>
        function openCreateModal() {
            document.getElementById('createEventModal').classList.add('active');
        }

        function openEditModal(event) {
            document.getElementById('edit_event_id').value = event.event_id;
            document.getElementById('edit_title').value = event.title;
            document.getElementById('edit_description').value = event.description || '';
            document.getElementById('edit_date').value = event.date;
            document.getElementById('edit_time').value = event.time;
            document.getElementById('edit_venue').value = event.venue;
            document.getElementById('edit_organizer').value = event.organizer;
            document.getElementById('edit_capacity').value = event.capacity;
            document.getElementById('editEventModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        };
    </script>
</body>
</html>
