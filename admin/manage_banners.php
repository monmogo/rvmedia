<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="admin-container d-flex">
        <?php include 'sidebar.php'; ?>

        <main class="content p-4">
            <h2 class="fw-bold">📢 Quản Lý Banner</h2>
            <p class="text-muted">Thêm, chỉnh sửa và xóa banner hiển thị trên trang chủ.</p>

            <!-- Nút thêm banner -->
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                ➕ Thêm Banner
            </button>

            <!-- Danh sách Banner -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Mô tả</th>
                        <th>Hình ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><img src="<?php echo $row['image_url']; ?>" width="120"></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                                data-title="<?php echo $row['title']; ?>"
                                data-description="<?php echo $row['description']; ?>"
                                data-link="<?php echo $row['link']; ?>" data-image="<?php echo $row['image_url']; ?>">✏️
                                Sửa</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">🗑️
                                Xóa</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Thêm Banner -->
    <div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <label class="form-label">Link (Tùy chọn)</label>
                            <input type="text" class="form-control" name="link">
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
                }
            });
        });

        // Xóa banner
        $(".delete-btn").click(function() {
            if (confirm("Bạn có chắc chắn muốn xóa banner này?")) {
                var bannerId = $(this).data("id");
                $.post("api/delete_banner.php", {
                    id: bannerId
                }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        });
    });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>