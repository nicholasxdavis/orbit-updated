<?php
session_start();
// The database connection is now required to check the status of services.
require 'api/db_connect.php';

// --- Authentication Check ---
// Redirects to the login page if the user is not logged in.
if (!isset($_SESSION['user_authenticated']) || !isset($_SESSION['reddit_username'])) {
    header('Location: index.php');
    exit;
}

$reddit_username = $_SESSION['reddit_username'];

// --- Function to Check Service Connection Status ---
// This function queries the database to see if a valid token exists for the user.
// It's designed to be expandable for other services in the future.
function isServiceConnected($mysqli, $username, $service) {
    if ($service === 'reddit') {
        $sql = "SELECT reddit_access_token FROM users WHERE reddit_username = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) return false;
        
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Return true if a user is found and their token is not empty.
        return ($user && !empty($user['reddit_access_token']));
    }
    // Add checks for other services like 'google' or 'discord' here in the future.
    return false;
}

// Check the connection status for Reddit.
$is_reddit_connected = isServiceConnected($mysqli, $reddit_username, 'reddit');

// --- Logout Logic ---
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?status=loggedout');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orbit Workspace - Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.2/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>
<body class="bg-gray-900 text-white min-h-screen">

    <div class="navbar bg-base-300 shadow-lg">
        <div class="flex-1">
            <a class="btn btn-ghost text-xl">Orbit Workspace</a>
        </div>
        <div class="flex-none">
            <span class="mr-4">Welcome, <?php echo htmlspecialchars($reddit_username); ?>!</span>
            <a href="dashboard.php?logout=true" class="btn btn-outline btn-error">Logout</a>
        </div>
    </div>

    <div class="container mx-auto p-8">
        <h1 class="text-4xl font-bold mb-2">Connect Your Services</h1>
        <p class="text-gray-400 mb-8">Link your accounts to unlock AI-powered tools and insights.</p>
        
        <!-- Service Connection Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Reddit Card -->
            <div class="card bg-base-200 shadow-xl transition-transform hover:scale-105">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-orange-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12,0C5.373,0,0,5.373,0,12c0,6.627,5.373,12,12,12s12-5.373,12-12C24,5.373,18.627,0,12,0z M12,21.75 c-5.379,0-9.75-4.371-9.75-9.75S6.621,2.25,12,2.25s9.75,4.371,9.75,9.75S17.379,21.75,12,21.75z M16.94,11.21 c0,1.25-1.01,2.27-2.27,2.27s-2.27-1.02-2.27-2.27s1.02-2.27,2.27-2.27S16.94,9.95,16.94,11.21z M7.06,11.21 c0-1.25,1.01-2.27,2.27-2.27s2.27,1.02,2.27,2.27s-1.02,2.27-2.27,2.27S7.06,12.46,7.06,11.21z M12,15.75c-2.07,0-3.87-1.12-4.83-2.82c-0.15-0.26,0.03-0.58,0.32-0.58h9c0.29,0,0.47,0.32,0.32,0.58 C15.87,14.63,14.07,15.75,12,15.75z"/></svg>
                        Reddit
                    </h2>
                    <p>Analyze trends, schedule posts, and manage your community.</p>
                    <div class="card-actions justify-end mt-4">
                        <?php if ($is_reddit_connected): ?>
                            <div class="flex items-center gap-2 text-success font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>Connected</span>
                            </div>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-primary">Connect</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Google Card (Placeholder) -->
            <div class="card bg-base-200 shadow-xl opacity-50">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5.03,16.42 5.03,12.5C5.03,8.58 8.36,5.73 12.19,5.73C14.03,5.73 15.69,6.33 16.86,7.38L19.08,5.21C17.21,3.54 14.85,2.5 12.19,2.5C7.03,2.5 3,6.58 3,12.5C3,18.42 7.03,22.5 12.19,22.5C17.6,22.5 21.5,18.33 21.5,12.75C21.5,11.95 21.43,11.52 21.35,11.1Z"/></svg>
                        Google
                    </h2>
                    <p>Connect Docs, Sheets, and Gmail for AI-powered creation.</p>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-disabled">Coming Soon</button>
                    </div>
                </div>
            </div>
            
            <!-- Discord Card (Placeholder) -->
            <div class="card bg-base-200 shadow-xl opacity-50">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-indigo-400" viewBox="0 0 24 24" fill="currentColor"><path d="M19.54,6.03C18.88,5.56 18.15,5.16 17.38,4.82C17.38,4.82 17.37,4.82 17.37,4.81C16.3,5.17 15.29,5.45 14.24,5.66C13.28,5.13 12.28,4.54 11.23,3.91C10.7,4.45 10.19,5 9.62,5.5L9.26,5.2C8.24,4.6 7.23,4.04 6.22,3.47C6.22,3.47 6.21,3.47 6.21,3.47C3.17,4.72 1.42,7.95 2.24,10.91C3.88,16.88 9.32,19.56 14.81,18.42C18.43,17.65 20.8,14.62 21.43,11.23C21.6,10.16 21.54,9.07 21.28,8.04C20.8,6.96 20.21,6.43 19.54,6.03Z"/></svg>
                        Discord
                    </h2>
                    <p>Automate server management and gain insights on your community.</p>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-disabled">Coming Soon</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>

