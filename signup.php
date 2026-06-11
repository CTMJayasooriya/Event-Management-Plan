<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                // Hash password and insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
                $stmt->execute([$name, $email, $hashedPassword]);
                
                $success = 'Registration successful! You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<html>
<head>
    
    <title>Sign Up - Student Event Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-image: url('back.png'); background-size: cover; background-repeat: no-repeat;">
    <nav class="navbar" style="background-image: url('img.png'); background-size: cover; background-repeat: no-repeat;">
        <div class="nav-container">
            <a href="index.php" class="nav-brand"><img src ="itumlogo.png" height="100" width="300"></a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 2rem;">Create Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php">Click here to login</a>
                </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateSignupForm()">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control">
                    <small style="color: #6c757d;">Minimum 6 characters</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%;">Sign Up</button>
            </form>

            <p style="text-align: center; margin-top: 1rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>