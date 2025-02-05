<?php
$host = "localhost"; // Hoặc IP của server MySQL
$user = "root"; // Tên user MySQL
$pass = ""; // Mật khẩu MySQL (để trống nếu chạy trên XAMPP)
$dbname = "theme_store"; // Tên database

// Kết nối MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset utf8 để tránh lỗi font tiếng Việt
$conn->set_charset("utf8");
?>