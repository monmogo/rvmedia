<?php
include 'db.php';
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy user ID
$user_id = $_SESSION['user_id'];

// Kiểm tra giỏ hàng có sản phẩm không
if (empty($_SESSION['cart'])) {
    header("Location: cart.php?error=empty");
    exit();
}

// Xử lý thanh toán
foreach ($_SESSION['cart'] as $theme) {
    $theme_id = $theme['id'];
    $stmt = $conn->prepare("INSERT INTO orders (user_id, theme_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $user_id, $theme_id);
    $stmt->execute();
}

// Xóa giỏ hàng sau khi thanh toán thành công
$_SESSION['cart'] = [];

header("Location: checkout_success.php");
exit();
?>