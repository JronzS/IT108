<?php
include_once('includes/load.php');
global $db;
try {
    $test_query = $db->query("SELECT 1");
    echo "Database connection successful.<br>";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
$req_fields = array('username', 'password');
validate_fields($req_fields);

// Debugging: Ensure form fields are received
if (empty($_POST['username']) || empty($_POST['password'])) {
    $session->msg("d", "Username or Password cannot be blank.");
    redirect('index.php', false);
}

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
    $user_id = authenticate($username, $password);
    if ($user_id) {
        // Create session with user ID
        $session->login($user_id);
        
        // Update the last login time
        updateLastLogIn($user_id);
        
        $session->msg("s", "Welcome to Jennifer Inventory.");
        redirect('home.php', false);
    } else {
        $session->msg("d", "Sorry, Username/Password incorrect.");
        redirect('index.php', false);
    }
} else {
    $session->msg("d", $errors);
    redirect('index.php', false);
}
?>
