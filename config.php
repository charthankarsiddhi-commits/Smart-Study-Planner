<?php
// Database connection and session setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$db_user = "root";
$db_password = ""; // Default XAMPP MySQL password is usually blank
$db_name = "smart_study_planner";

$conn = new mysqli($host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Database connection failed. Check config.php and make sure MySQL is running. Details: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function require_login(): void {
    if (empty($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
