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
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$link = isset($_POST['link']) ? trim($_POST['link']) : '';

// Kiểm tra các trường bắt buộc
if ($id <= 0 || empty($title) || empty($description)) {
    echo "Vui lòng điền đầy đủ thông tin cần thiết!";
    exit();
}

// Xử lý file ảnh nếu có file mới được tải lên
$newImageUrl = null;
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image_file'];
    
    // Cho phép các định dạng ảnh: JPEG, PNG, GIF, WEBP
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo "Chỉ cho phép tải lên các file ảnh (JPEG, PNG, GIF, WEBP)!";
        exit();
    }
    
    // Xác định thư mục lưu file (nếu chưa tồn tại thì tạo mới)
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo "Không thể tạo thư mục tải lên!";
            exit();
        }
    }
    
    // Tạo tên file duy nhất dựa trên uniqid và phần mở rộng của file gốc
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid('banner_', true) . '.' . $fileExt;
    $targetPath = $uploadDir . $uniqueName;
    
    // Di chuyển file từ vị trí tạm thời sang thư mục uploads
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo "Có lỗi xảy ra khi tải ảnh lên!";
        exit();
    }
    
    // Tạo đường dẫn tương đối để lưu vào cơ sở dữ liệu
    $newImageUrl = 'uploads/' . $uniqueName;
}

// Cập nhật dữ liệu vào cơ sở dữ liệu
if ($newImageUrl !== null) {
    // Nếu có file ảnh mới, cập nhật cả trường image_url
    $stmt = $conn->prepare("UPDATE banners SET title = ?, description = ?, link = ?, image_url = ? WHERE id = ?");
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        exit();
    }
    $stmt->bind_param("ssssi", $title, $description, $link, $newImageUrl, $id);
} else {
    // Nếu không có file ảnh mới, chỉ cập nhật các trường khác
    $stmt = $conn->prepare("UPDATE banners SET title = ?, description = ?, link = ? WHERE id = ?");
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        exit();
    }
    $stmt->bind_param("sssi", $title, $description, $link, $id);
}

if ($stmt->execute()) {
    echo "Cập nhật banner thành công!";
} else {
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>