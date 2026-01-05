<?php
// ไฟล์เชื่อม MySQL ของคุณ
$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'booking_system';

// เชื่อมต่อ MySQL
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

?>