<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_system');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="background:#fee2e2;color:#991b1b;padding:20px;font-family:sans-serif;border-radius:8px;margin:20px;">
        <strong>Database Connection Failed!</strong><br>
        Make sure XAMPP is running and the database <strong>hotel_system</strong> exists.<br>
        Error: ' . htmlspecialchars($e->getMessage()) . '
    </div>');
}
?>
