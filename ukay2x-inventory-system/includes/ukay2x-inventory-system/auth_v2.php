<?php
include_once('includes/load.php');

$req_fields = array('username', 'password');
validate_fields($req_fields);

// Debugging: Ensure form fields are received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Username: " . $_POST['username'] . "<br>";
    echo "Password: " . $_POST['password'] . "<br>";
}

$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if (empty($errors)) {
    $user = authenticate_v2($username, $password);
    
    if ($user) {
        // Debug authenticated user
        var_dump($user);

        // Create session with user ID
        $session->login($user['id']);
        
        // Update the last login time
        updateLastLogIn($user['id']);
        
        // Redirect user based on user level
        if ($user['user_level'] === '1') {
            $session->msg("s", "Hello, " . $user['username'] . ". Welcome to Jennifer Inventory.");
            redirect('admin.php', false);
        } elseif ($user['user_level'] === '2') {
            $session->msg("s", "Hello, " . $user['username'] . ". Welcome to Jennifer Inventory.");
            redirect('special.php', false);
        } else {
            $session->msg("s", "Hello, " . $user['username'] . ". Welcome to Jennifer Inventory.");
            redirect('home.php', false);
        }
    } else {
        $session->msg("d", "Sorry, Username/Password incorrect.");
        redirect('login_v2.php', false);
    }
} else {
    $session->msg("d", $errors);
    redirect('login_v2.php', false);
}
?>
