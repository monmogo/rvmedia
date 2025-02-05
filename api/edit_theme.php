<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền thực hiện thao tác này!");
}

// Xử lý cập nhật theme qua AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_theme'])) {
    $theme_id = $_POST['theme_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $file_url = $_POST['file_url'];
    $category = $_POST['category'];
    $old_image_url = $_POST['old_image_url']; // Ảnh cũ

    // Kiểm tra xem có file ảnh mới được chọn không
    if (isset($_FILES["new_image"]) && $_FILES["new_image"]["error"] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = basename($_FILES["new_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("❌ Chỉ chấp nhận file JPG, JPEG, PNG, GIF!");
        }

        if (!move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file)) {
            die("❌ Lỗi khi tải ảnh lên!");
        }

        // Xóa ảnh cũ nếu có
        if (file_exists($old_image_url) && !empty($old_image_url)) {
            unlink($old_image_url);
        }

        $image_url = $target_file; // Cập nhật đường dẫn ảnh mới
    } else {
        $image_url = $old_image_url; // Giữ nguyên nếu không chọn ảnh mới
    }

    // Cập nhật dữ liệu trong database
    $stmt = $conn->prepare("UPDATE themes SET name=?, description=?, price=?, image_url=?, file_url=?, category=? WHERE id=?");
    $stmt->bind_param("ssdsssi", $name, $description, $price, $image_url, $file_url, $category, $theme_id);

    if ($stmt->execute()) {
        echo "✔️ Cập nhật thành công!";
    } else {
        echo "❌ Lỗi khi cập nhật!";
    }
    exit();
}
?>

<!-- Modal Edit Theme -->
<!-- Modal Edit Theme -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">✏️ Chỉnh Sửa Theme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" enctype="multipart/form-data">
                    <input type="hidden" name="theme_id" id="theme_id">
                    <input type="hidden" name="old_image_url" id="old_image_url"> <!-- Lưu ảnh cũ -->

                    <!-- Ảnh hiện tại -->
                    <div class="mb-3 text-center">
                        <label class="form-label">Ảnh Hiện Tại</label><br>
                        <img id="preview_image" src="" alt="Ảnh Theme" class="img-thumbnail" width="150">
                    </div>

                    <!-- Chọn ảnh mới -->
                    <div class="mb-3">
                        <label class="form-label">Chọn Ảnh Mới (Nếu muốn đổi ảnh)</label>
                        <input type="file" class="form-control" name="new_image" id="new_image">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên Theme</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh Mục</label>
                        <input type="text" class="form-control" name="category" id="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" class="form-control" name="price" id="price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL File Tải Theme</label>
                        <input type="text" class="form-control" name="file_url" id="file_url" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô Tả</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">✔️ Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- AJAX Xử Lý Cập Nhật -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Khi bấm vào nút "Sửa"
    $(".edit-btn").click(function() {
        $("#theme_id").val($(this).data("id"));
        $("#name").val($(this).data("name"));
        $("#category").val($(this).data("category"));
        $("#price").val($(this).data("price"));
        $("#file_url").val($(this).data("file_url"));
        $("#description").val($(this).data("description"));

        let imageUrl = $(this).data("image_url");
        $("#old_image_url").val(imageUrl); // Lưu đường dẫn ảnh cũ
        $("#preview_image").attr("src", imageUrl); // Hiển thị ảnh cũ

        $("#editModal").modal("show");
    });

    // Xử lý cập nhật theme bằng AJAX và file upload
    $("#editForm").submit(function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        formData.append("update_theme", 1);

        $.ajax({
            type: "POST",
            url: "../api/edit_theme.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function() {
                alert("❌ Lỗi khi cập nhật!");
            }
        });
    });
});
</script>