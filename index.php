<?php
session_start();

// If the user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true) {
    header('Location: dashboard.php');
    exit;
}

// --- Reddit API Configuration ---
$client_id = 'k8zVPy-VzLzKS1XOETZhug';
// IMPORTANT: Make sure this redirect URI matches EXACTLY what you've set in your Reddit App settings.
$redirect_uri = 'https://www.orbitworkspace.net/callback.php'; 
$response_type = 'code';
// A random, unguessable string for security (CSRF protection).
$state = bin2hex(random_bytes(16)); 
$_SESSION['state'] = $state; // Store state in session to verify on callback
$duration = 'permanent'; // Request a refresh token
$scope = implode(',', [
    'identity', 'edit', 'flair', 'history', 'modconfig', 'modflair', 
    'modlog', 'modposts', 'modwiki', 'mysubreddits', 'privatemessages', 
    'read', 'report', 'save', 'submit', 'subscribe', 'vote', 
    'wikiedit', 'wikiread'
]);

// Construct the authorization URL
$auth_url = "https://www.reddit.com/api/v1/authorize?" . http_build_query([
    'client_id' => $client_id,
    'response_type' => $response_type,
    'state' => $state,
    'redirect_uri' => $redirect_uri,
    'duration' => $duration,
    'scope' => $scope
]);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect to Orbit Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.2/dist/full.min.css" rel="stylesheet" type="text/css" />
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center">

    <div class="text-center">
        <div class="card bg-base-200 shadow-xl p-8 max-w-md">
            <h1 class="text-5xl font-bold mb-4">Orbit Workspace</h1>
            <p class="text-gray-400 mb-8">Your AI-powered hub for digital content and community management.</p>
            <div>
                <a href="<?php echo $auth_url; ?>" class="btn btn-primary btn-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M12,0C5.373,0,0,5.373,0,12c0,6.627,5.373,12,12,12s12-5.373,12-12C24,5.373,18.627,0,12,0z M12,21.75 c-5.379,0-9.75-4.371-9.75-9.75S6.621,2.25,12,2.25s9.75,4.371,9.75,9.75S17.379,21.75,12,21.75z M16.94,11.21 c0,1.25-1.01,2.27-2.27,2.27s-2.27-1.02-2.27-2.27s1.02-2.27,2.27-2.27S16.94,9.95,16.94,11.21z M7.06,11.21 c0-1.25,1.01-2.27,2.27-2.27s2.27,1.02,2.27,2.27s-1.02,2.27-2.27,2.27S7.06,12.46,7.06,11.21z M12,15.75c-2.07,0-3.87-1.12-4.83-2.82c-0.15-0.26,0.03-0.58,0.32-0.58h9c0.29,0,0.47,0.32,0.32,0.58 C15.87,14.63,14.07,15.75,12,15.75z"/></svg>
                    Connect with Reddit
                </a>
            </div>
             <?php
                // Display a message if the user just logged out.
                if (isset($_GET['status']) && $_GET['status'] === 'loggedout') {
                    echo '<p class="text-success mt-4">You have been successfully logged out.</p>';
                }
                // Display an error if the callback failed.
                if (isset($_GET['error'])) {
                    echo '<p class="text-error mt-4">Authentication failed: ' . htmlspecialchars($_GET['error']) . '</p>';
                }
            ?>
        </div>
    </div>

</body>
</html>

