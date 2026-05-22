<<<<<<< HEAD
<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'hotel_db');

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
=======
<?php
// Secure connection credentials using environment variables
$host   = getenv('DB_HOST') ?: 'mysql-13670cdc-wvsu-09a7.j.aivencloud.com';
$user   = getenv('DB_USER') ?: 'avnadmin'; 
$pass   = getenv('DB_PASS') ?: ''; // Leave empty here; we will put it in Render securely
$dbname = getenv('DB_NAME') ?: 'defaultdb';
$port   = getenv('DB_PORT') ?: '27995';

// Aiven requires SSL for secure connections
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

if (!mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
>>>>>>> 665f4a3a3c4c4205492a17f248bce813b22a689e
