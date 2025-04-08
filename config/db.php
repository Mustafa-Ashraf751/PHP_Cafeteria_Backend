<?php
$host = 'localhost';
$dbname = 'cafeteria_db';
$username = 'root';
$password = '';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

