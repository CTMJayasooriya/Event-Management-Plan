<?php

// Database configuration
// Support Docker container environment variables as well as local XAMPP/WAMP setups
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'student_event_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns a shared PDO connection instance.
 * Automatically tries primary host, and falls back to localhost if needed.
 */
function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If DB_HOST failed and was set to docker 'db' or '127.0.0.1', try alternative host
            $fallbackHost = (DB_HOST === 'db') ? '127.0.0.1' : 'db';
            try {
                $fallbackUser = ($fallbackHost === '127.0.0.1') ? 'root' : 'event_user';
                $fallbackPass = ($fallbackHost === '127.0.0.1') ? '' : 'event_pass';
                $fallbackDsn = "mysql:host=" . $fallbackHost . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $conn = new PDO($fallbackDsn, $fallbackUser, $fallbackPass, $options);
            } catch (PDOException $fallbackException) {
                // Throw the original exception with helpful context
                throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
            }
        }
    }

    return $conn;
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the currently logged in user is an administrator
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Enforce that the user must be logged in to view the page
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $isInsideAdmin = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin';
        $redirectUrl = $isInsideAdmin ? '../login.php' : 'login.php';
        header("Location: " . $redirectUrl);
        exit;
    }
}

/**
 * Enforce that the user must be an administrator to view the page
 */
function requireAdmin() {
    if (!isAdmin()) {
        $isInsideAdmin = basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin';
        $redirectUrl = $isInsideAdmin ? '../login.php' : 'login.php';
        header("Location: " . $redirectUrl);
        exit;
    }
}
