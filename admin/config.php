<?php

$servername = "localhost";
$usernameDB = "root";
$passwordDB = "";
$dbname = "latam";

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
