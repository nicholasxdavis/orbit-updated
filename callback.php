<?php
session_start();
require 'api/db_connect.php'; // Include your database connection script.

// --- Reddit API Credentials ---
$client_id = 'k8zVPy-VzLzKS1XOETZhug';
$client_secret = 'JqI-5k4MW7bKjZYnPaiewdPYYOfj3A'; // Your Reddit App Secret
$redirect_uri = 'https://www.orbitworkspace.net/callback.php';

// --- Error Handling ---
// Handle cases where the user denies access or an error occurs on Reddit's end.
if (isset($_GET['error'])) {
    header('Location: index.php?error=' . urlencode($_GET['error']));
    exit;
}

// --- Security Check (CSRF Protection) ---
// Validate that the 'state' parameter matches the one we stored in the session.
if (!isset($_GET['state']) || !isset($_SESSION['state']) || $_GET['state'] !== $_SESSION['state']) {
    header('Location: index.php?error=invalid_state');
    exit;
}

// --- Authorization Code Exchange ---
if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Prepare to exchange the code for an access token.
    $token_url = 'https://www.reddit.com/api/v1/access_token';
    $post_fields = http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirect_uri
    ]);

    // 2. Use cURL to make the POST request to Reddit's token endpoint.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    // Reddit requires Basic Auth with your client ID and secret for this step.
    curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: OrbitWorkspace/1.0']);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        header('Location: index.php?error=curl_error');
        exit;
    }
    curl_close($ch);
    $token_data = json_decode($result, true);

    if (isset($token_data['error'])) {
        header('Location: index.php?error=' . urlencode($token_data['error']));
        exit;
    }

    // 3. Use the new access token to get the user's identity.
    $access_token = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token']; // Store this for long-term access

    $user_info_url = 'https://oauth.reddit.com/api/v1/me';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_info_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: bearer ' . $access_token,
        'User-Agent: OrbitWorkspace/1.0'
    ]);

    $user_result = curl_exec($ch);
    curl_close($ch);
    $user_data = json_decode($user_result, true);

    if (!isset($user_data['name'])) {
        header('Location: index.php?error=fetch_user_failed');
        exit;
    }
    $username = $user_data['name'];

    // 4. Store the user's information in the database.
    // This query will insert a new user or update the tokens for an existing user.
    $sql = "INSERT INTO users (reddit_username, reddit_access_token, reddit_refresh_token) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE reddit_access_token = VALUES(reddit_access_token), reddit_refresh_token = VALUES(reddit_refresh_token)";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $username, $access_token, $refresh_token);
    $stmt->execute();
    $stmt->close();

    // 5. Set session variables to log the user in.
    $_SESSION['user_authenticated'] = true;
    $_SESSION['reddit_username'] = $username;
    
    // Clear the state variable now that it has been used.
    unset($_SESSION['state']);

    // 6. Redirect to the main dashboard.
    header('Location: dashboard.php');
    exit;

} else {
    // If no code is present, something went wrong.
    header('Location: index.php?error=no_code');
    exit;
}
?>

