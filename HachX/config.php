<?php
$host = 'localhost'; // Change this to your database host
$db = 'ehr'; // Your database name
$user = 'root'; // Your MySQL user
$password = 'root'; // Your MySQL password

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>