<?php

$host = "localhost";
$username = "root"; 
$password = ""; 
$database = "db_klinik";


$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}
function redirect($url) {
    header("Location: $url");
    exit();
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>