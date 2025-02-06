<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "🚫 Bạn không có quyền truy cập vào API này!";
    exit();
}

// Chỉ cho phép phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Phương thức yêu cầu không hợp lệ!";
    exit();
}

// Lấy dữ liệu từ POST
$title       = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$link        = isset($_POST['link']) ? trim($_POST['link']) : ''; // Link có thể là rỗng

// Kiểm tra các trường bắt buộc
if (empty($title) || empty($description)) {
    echo "Vui lòng điền đầy đủ tiêu đề và mô tả!";
    exit();
}

// Kiểm tra file ảnh được tải lên
if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
    echo "Vui lòng tải lên ảnh banner hợp lệ!";
    exit();
}

$file = $_FILES['image_file'];

// Cho phép các định dạng ảnh: JPEG, PNG, GIF, WEBP
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo "Chỉ cho phép tải lên các file ảnh (JPEG, PNG, GIF, WEBP)!";
    exit();
}

// Xác định thư mục lưu file (sử dụng __DIR__ để đảm bảo đường dẫn chính xác)
// Giả sử file add_banner.php nằm trong thư mục api/, ta đặt thư mục uploads bên ngoài api/
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo "Không thể tạo thư mục tải lên!";
        exit();
    }
}

// Tạo tên file duy nhất dựa trên uniqid và phần mở rộng của file gốc
$fileExt    = pathinfo($file['name'], PATHINFO_EXTENSION);
$uniqueName = uniqid('banner_', true) . '.' . $fileExt;
$targetPath = $uploadDir . $uniqueName;

// Di chuyển file từ vị trí tạm thời sang thư mục uploads
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo "Có lỗi xảy ra khi tải ảnh lên!";
    exit();
}

// Tạo đường dẫn tương đối để lưu vào cơ sở dữ liệu
$imageUrl = 'uploads/' . $uniqueName;

// Chèn dữ liệu banner vào cơ sở dữ liệu
$stmt = $conn->prepare("INSERT INTO banners (title, description, image_url, link) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    echo "Prepare failed: " . $conn->error;
    exit();
}

$stmt->bind_param("ssss", $title, $description, $imageUrl, $link);
if ($stmt->execute()) {
    echo "Thêm banner thành công!";
} else {
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>