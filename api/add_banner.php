<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền thực hiện thao tác này!");
}

// Kiểm tra có dữ liệu gửi lên không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $link = $_POST['link'];
    $upload_dir = "../uploads/";

    // Kiểm tra thư mục upload, nếu chưa có thì tạo
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Xử lý upload file ảnh
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        $image_name = time() . "_" . basename($_FILES["image_file"]["name"]);
        $target_file = $upload_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra định dạng ảnh hợp lệ
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("❌ Chỉ chấp nhận file JPG, JPEG, PNG, GIF!");
        }

        // Lưu file ảnh
        if (!move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
            die("❌ Lỗi khi tải ảnh lên! Hãy kiểm tra quyền thư mục.");
        }

        $image_url = "uploads/" . $image_name;

        // Thêm vào database
        $stmt = $conn->prepare("INSERT INTO banners (title, description, link, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $link, $image_url);
        $stmt->execute();

        echo "✔️ Banner đã được thêm thành công!";
    } else {
        die("❌ Không có file ảnh hoặc file bị lỗi!");
    }
}
?>