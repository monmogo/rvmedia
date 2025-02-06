<?php
session_start();
session_unset(); // Xóa tất cả các biến session
session_destroy(); // Hủy session

// Chuyển hướng về trang đăng nhập hoặc trang chủ
header("Location: index.php");
exit();
?>