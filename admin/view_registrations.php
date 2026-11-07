<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$messageType = 'success';

// Handle Registration Status Change (Confirm / Cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $regId = (int)($_POST['reg_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if ($regId > 0 && in_array($newStatus, ['confirmed', 'cancelled'])) {
        try {
            $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE reg_id = ?");
            $stmt->execute([$newStatus, $regId]);
            $message = "Registration #$regId status changed to " . htmlspecialchars($newStatus) . ".";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Failed to update registration status: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Filters
$selectedEventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int)$_GET['event_id'] : null;
$selectedStatus = isset($_GET['status']) && in_array($_GET['status'], ['confirmed', 'cancelled']) ? $_GET['status'] : null;
$searchQuery = trim($_GET['search'] ?? '');

// Fetch all events for the filter dropdown
try {
    $eventsList = $conn->query("SELECT event_id, title, date FROM events ORDER BY date DESC")->fetchAll();
} catch (PDOException $e) {
    $eventsList = [];
}

// Build query for registrations
$sql = "
    SELECT r.*, 
           u.name as student_name, 
           u.email as student_email,
           e.title as event_title, 
           e.date as event_date,
           e.time as event_time, 
           e.venue as event_venue
    FROM registrations r
    JOIN users u ON r.user_id = u.user_id
    JOIN events e ON r.event_id = e.event_id
    WHERE 1=1
";
$params = [];

if ($selectedEventId) {
    $sql .= " AND r.event_id = ?";
    $params[] = $selectedEventId;
}

if ($selectedStatus) {
    $sql .= " AND r.status = ?";
    $params[] = $selectedStatus;
}

if (!empty($searchQuery)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR r.student_id LIKE ? OR r.contact_number LIKE ?)";
    $like = '%' . $searchQuery . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY r.registration_date DESC";

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $filename = 'registrations_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'Registration ID',
            'Event Title',
            'Event Date',
            'Event Venue',
            'Student ID',
            'Student Name',
            'Student Email',
            'Contact Number',
            'Registration Date',
            'Status'
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['reg_id'],
                $row['event_title'],
                $row['event_date'] . ' ' . $row['event_time'],
                $row['event_venue'],
                $row['student_id'],
                $row['student_name'],
                $row['student_email'],
                $row['contact_number'],
                $row['registration_date'],
                ucfirst($row['status'])
            ]);
        }

        fclose($output);
        exit();
    } catch (PDOException $e) {
        die('Failed to export CSV: ' . $e->getMessage());
    }
}

// Fetch records for display
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $registrations = [];
    $message = 'Failed to load registrations: ' . $e->getMessage();
    $messageType = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations - Admin Portal</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="view_registrations.php" class="active">Registrations</a></li>
                <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?>)</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header" style="text-align: left;">
            <h1>Event Registrations</h1>
            <p>Filter, manage student registrations, and export attendee lists to CSV.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" action="view_registrations.php" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                    <div style="flex: 2; min-width: 220px;">
                        <label for="event_id" style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.35rem; display: block;">Event</label>
                        <select name="event_id" id="event_id" class="form-control">
                            <option value="">All Events</option>
                            <?php foreach ($eventsList as $ev): ?>
                                <option value="<?php echo $ev['event_id']; ?>" <?php echo $selectedEventId === (int)$ev['event_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ev['title']); ?> (<?php echo date('M d, Y', strtotime($ev['date'])); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 140px;">
                        <label for="status" style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.35rem; display: block;">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="confirmed" <?php echo $selectedStatus === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="cancelled" <?php echo $selectedStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div style="flex: 2; min-width: 200px;">
                        <label for="search" style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.35rem; display: block;">Search Student / Contact</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Name, Email, or Student ID..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="view_registrations.php" class="btn btn-outline">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="toolbar">
            <div>
                <strong>Total Registrations Found:</strong> <?php echo count($registrations); ?>
            </div>
            <div>
                <?php
                    // Build export query string
                    $exportQuery = http_build_query([
                        'event_id' => $selectedEventId,
                        'status' => $selectedStatus,
                        'search' => $searchQuery,
                        'export' => 'csv'
                    ]);
                ?>
                <a href="view_registrations.php?<?php echo $exportQuery; ?>" class="btn btn-success">
                    📥 Export to CSV
                </a>
            </div>
        </div>

        <!-- Registrations Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reg ID</th>
                        <th>Student Name & Email</th>
                        <th>Student ID</th>
                        <th>Contact No</th>
                        <th>Event Details</th>
                        <th>Registered Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem;">No registrations match your search and filter criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td>#<?php echo $reg['reg_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['student_name']); ?></strong>
                                    <div class="text-muted small"><?php echo htmlspecialchars($reg['student_email']); ?></div>
                                </td>
                                <td><code><?php echo htmlspecialchars($reg['student_id']); ?></code></td>
                                <td><?php echo htmlspecialchars($reg['contact_number']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['event_title']); ?></strong>
                                    <div class="text-muted small">
                                        📅 <?php echo date('M d, Y', strtotime($reg['event_date'])); ?> (<?php echo htmlspecialchars($reg['event_venue']); ?>)
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y g:i A', strtotime($reg['registration_date'])); ?></td>
                                <td>
                                    <?php if ($reg['status'] === 'confirmed'): ?>
                                        <span class="badge badge-success">Confirmed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reg_id" value="<?php echo $reg['reg_id']; ?>">
                                        <?php if ($reg['status'] === 'confirmed'): ?>
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger);"
                                                    onclick="return confirm('Cancel this registration?');">
                                                Mark Cancelled
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--success);"
                                                    onclick="return confirm('Confirm this registration?');">
                                                Re-confirm
                                            </button>
                                        <?php endif; ?>
                                    </form>
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
            </footer>
        </div>
    </div>
</body>
</html>
