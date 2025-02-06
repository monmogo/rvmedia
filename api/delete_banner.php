<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "🚫 Bạn không có quyền truy cập vào API này!";
    exit();
}

// Chỉ cho phép phương thức POST và yêu cầu có id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo "Yêu cầu không hợp lệ!";
    exit();
}

$id = intval($_POST['id']);
if ($id <= 0) {
    echo "ID không hợp lệ!";
    exit();
}

// Lấy thông tin banner (để biết đường dẫn ảnh cần xóa, nếu có)
$stmt = $conn->prepare("SELECT image_url FROM banners WHERE id = ?");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
    exit();
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Banner không tồn tại!";
    $stmt->close();
    exit();
}
$row = $result->fetch_assoc();
$imageUrl = trim($row['image_url']);
$stmt->close();

// Xóa banner khỏi cơ sở dữ liệu
$stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
    exit();
}
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    // Nếu banner có ảnh và đường dẫn bắt đầu bằng "uploads/", xóa file ảnh khỏi máy chủ
    if (!empty($imageUrl) && strpos($imageUrl, "uploads/") === 0) {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $imageUrl;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    echo "Xóa banner thành công!";
} else {
    echo "Lỗi: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>