<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: events.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<html>
<head>
    
    <title>Login - Student Event Management</title>
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
        <div class="form-container" >
            <h2 style="text-align: center; margin-bottom: 2px;">Login to Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateLoginForm()">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <p style="text-align: center; margin-top: 1rem;">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>

            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                <p style="margin: 0; font-size: 0.9rem;"><strong>Demo Accounts:</strong></p>
                <p style="margin: 0.5rem 0; font-size: 0.9rem;">Admin: admin@university.com / admin123</p>
                <p style="margin: 0; font-size: 0.9rem;">Student: chathu@student.com / chathu123</p>
            </div>
        </div>
    </div>

    <script src="js/validation.js"></script>
</body>
</html>