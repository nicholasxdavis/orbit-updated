<?php
// --- MariaDB Database Configuration ---
$db_host = 'zc40sw4cokgww8w08sgsss8w'; // Your Coolify service hostname.
$db_user = 'mariadb';                     // The normal user you specified.
$db_pass = 'HZEmhCyP7ziE0cq8J2GnsAA9eQ4LJ12vt5DgNRZijti3WLvcrzsHMJSS6AjuBhKM'; // The normal user password.
$db_name = 'default';                     // The initial database name.
$db_port = 3306;                          // Default MariaDB port.

// --- Establish Database Connection ---
// Creates a new mysqli object to connect to the database.
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// --- Connection Error Handling ---
// Checks if the connection failed and terminates the script if it did.
if ($mysqli->connect_error) {
    // In a production environment, you would log this error instead of displaying it.
    die('Connection Failed: ' . $mysqli->connect_error);
}

// --- Function to Create Users Table ---
// This function will create the 'users' table if it doesn't already exist.
function createUsersTable($mysqli) {
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reddit_username VARCHAR(255) NOT NULL UNIQUE,
        reddit_access_token TEXT,
        reddit_refresh_token TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    if (!$mysqli->query($sql)) {
        // Handle potential errors during table creation.
        die('Error creating table: ' . $mysqli->error);
    }
}

// --- Initialize Database Schema ---
// Run the function to ensure the users table exists.
createUsersTable($mysqli);
?>
