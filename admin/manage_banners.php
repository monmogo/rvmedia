<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}

/**
 * Hàm trả về URL ảnh hợp lệ cho banner.
 *
 * @param string $imageUrl URL hoặc đường dẫn lưu trong cơ sở dữ liệu.
 * @return string URL ảnh hợp lệ hoặc ảnh mặc định nếu không hợp lệ.
 */
function getBannerImageUrl($imageUrl) {
    $default = 'assets/default.png';
    $imageUrl = trim($imageUrl);
    
    // Nếu ảnh rỗng, trả về ảnh mặc định
    if (empty($imageUrl)) {
        return $default;
    }
    
    // Nếu là URL tuyệt đối (http hoặc https), trả về luôn
    if (preg_match('/^https?:\/\//', $imageUrl)) {
        return $imageUrl;
    }
    
    // Nếu không bắt đầu bằng "uploads/", thêm tiền tố "uploads/"
    if (strpos($imageUrl, "uploads/") !== 0) {
        $imageUrl = "uploads/" . ltrim($imageUrl, '/');
    }
    
    // Kiểm tra xem file ảnh có tồn tại trên máy chủ không
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $imageUrl)) {
        return $default;
    }
    
    return $imageUrl;
}

// Lấy danh sách banner
$result = $conn->query("SELECT * FROM banners ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Banner</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Inline CSS -->
    <style>
    /* 🌟 Sidebar */
    body {
        background-color: #f4f6f9;
        font-family: 'Poppins', sans-serif;
    }

    .sidebar {
        width: 250px;
        height: 100vh;
        background: #343a40;
        position: fixed;
        top: 0;
        left: 0;
        color: white;
        padding-top: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar h3 {
        text-align: center;
        font-size: 1.5rem;
        padding-bottom: 15px;
        border-bottom: 1px solid #495057;
    }

    .nav-link {
        color: #ddd;
        padding: 10px 15px;
        display: block;
        transition: all 0.3s;
    }

    .nav-link:hover,
    .nav-link.active {
        background: #007bff;
        color: #fff;
        border-radius: 5px;
    }

    .nav-link.text-danger:hover {
        background: #dc3545;
    }

    /* 🌟 Nội dung chính */
    .content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 20px;
    }

    .table img {
        max-width: 120px;
        height: auto;
    }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- 🌟 Sidebar (nhúng từ file mẫu) -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- 🌟 Nội dung chính -->
        <div class="content">
            <h2 class="fw-bold">📢 Quản Lý Banner</h2>
            <p class="text-muted">Thêm, chỉnh sửa và xóa banner hiển thị trên trang chủ.</p>

            <!-- Nút thêm banner -->
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                ➕ Thêm Banner
            </button>

            <!-- Danh sách Banner -->
            <table class="table table-bordered shadow">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Hình ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                    <?php $imagePath = getBannerImageUrl($row['image_url']); ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" width="120"
                                onerror="this.src='assets/default.png';">
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                data-link="<?php echo htmlspecialchars($row['link']); ?>"
                                data-image="<?php echo htmlspecialchars($imagePath); ?>">
                                ✏️ Sửa
                            </button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">
                                🗑️ Xóa
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Banner -->
    <div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <form id="addBannerForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh Banner</label>
                            <input type="file" class="form-control" name="image_file" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">➕ Thêm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Banner -->
    <div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Sửa Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <form id="editBannerForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="editBannerId">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" id="editBannerTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" id="editBannerDescription"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link</label>
                            <input type="text" class="form-control" name="link" id="editBannerLink">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ảnh hiện tại</label>
                            <div id="currentBannerImage" class="mb-2"></div>
                            <label class="form-label">Chọn ảnh mới (nếu muốn thay đổi)</label>
                            <input type="file" class="form-control" name="image_file" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">✏️ Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Xử Lý -->
    <script>
    $(document).ready(function() {
        // Thêm banner
        $("#addBannerForm").submit(function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: "POST",
                url: "../api/add_banner.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert("Có lỗi xảy ra: " + error);
                }
            });
        });

        // Mở modal chỉnh sửa banner và điền dữ liệu
        $(".edit-btn").click(function() {
            var id = $(this).data("id");
            var title = $(this).data("title");
            var description = $(this).data("description");
            var link = $(this).data("link");
            var image = $(this).data("image");

            $("#editBannerId").val(id);
            $("#editBannerTitle").val(title);
            $("#editBannerDescription").val(description);
            $("#editBannerLink").val(link);
            $("#currentBannerImage").html('<img src="' + image +
                '" alt="Current Banner" width="100" class="img-thumbnail">');

            $("#editBannerModal").modal("show");
        });

        // Sửa banner
        $("#editBannerForm").submit(function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: "POST",
                url: "../api/edit_banner.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert("Có lỗi xảy ra: " + error);
                }
            });
        });

        // Xóa banner
        $(".delete-btn").click(function() {
            if (confirm("Bạn có chắc chắn muốn xóa banner này?")) {
                var bannerId = $(this).data("id");
                $.post("../api/delete_banner.php", {
                    id: bannerId
                }, function(response) {
                    alert(response);
                    location.reload();
                }).fail(function(xhr, status, error) {
                    alert("Có lỗi xảy ra: " + error);
                });
            }
        });
    });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>