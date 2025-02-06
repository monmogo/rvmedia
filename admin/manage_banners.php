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
    <style>
    /* 🌟 Sidebar */
    .sidebar {
        width: 250px;
        height: 100vh;
        background: #343a40;
        position: fixed;
        top: 0;
        left: 0;
        color: white;
        padding-top: 20px;
        box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
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
        color: white;
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
        <!-- 🌟 Sidebar -->
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td>
                            <?php
                                $imagePath = trim($row['image_url']);

                                // Nếu ảnh rỗng, hiển thị ảnh mặc định
                                if (empty($imagePath)) {
                                    $imagePath = 'assets/default.png';
                                }
                                // Nếu đường dẫn ảnh thiếu dấu "/", thêm vào
                                elseif (strpos($imagePath, "uploads") === false) {
                                    $imagePath = "uploads" . ltrim($imagePath, '');
                                }

                                // Kiểm tra xem ảnh có tồn tại không
                                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
                                    $imagePath = 'assets/default.png';
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" width="120"
                                onerror="this.src='assets/default.png';">
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                data-link="<?php echo htmlspecialchars($row['link']); ?>"
                                data-image="<?php echo htmlspecialchars($imagePath); ?>">✏️ Sửa</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">🗑️
                                Xóa</button>
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
                $.post("../api/delete_banner.php", {
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