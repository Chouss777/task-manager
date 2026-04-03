<?php
// db.php - Database connection

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'task_manager');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div class="alert alert-error">Connexion échouée : ' . $conn->connect_error . '</div>');
}

$conn->set_charset('utf8mb4');

// Create table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS tasks (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        title     VARCHAR(255) NOT NULL,
        description TEXT,
        status    ENUM('pending','in_progress','done') DEFAULT 'pending',
        priority  ENUM('low','medium','high') DEFAULT 'medium',
        due_date  DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
?>
