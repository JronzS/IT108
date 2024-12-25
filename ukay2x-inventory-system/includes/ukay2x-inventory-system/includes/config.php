<?php

// Database Configuration
define('DB_HOST', 'localhost'); 
define('DB_PORT', '5432'); 
define('DB_NAME', 'inventory'); 
define('DB_USER', 'postgres'); 
define('DB_PASS', 'root'); // Consistent with the rest of your code

// Database Connection 
try {
    $pdo = new PDO("pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
