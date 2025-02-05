<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin thì chặn truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="admin-container d-flex">
        <!-- 🌟 Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- 🌟 Nội dung chính -->
        <main class="content p-4">
            <h2 class="fw-bold">👋 Chào mừng, Admin <?php echo $_SESSION['username']; ?>!</h2>
            <p class="text-muted">Đây là trang quản trị, nơi bạn có thể quản lý website.</p>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card admin-card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">📦 Quản lý Theme</h5>
                            <p class="card-text">Thêm, chỉnh sửa, xóa các theme</p>
                            <a href="admin/manage_themes.php" class="btn btn-light">🛠️ Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card admin-card bg-success text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">🛒 Quản lý Đơn hàng</h5>
                            <p class="card-text">Xem danh sách đơn hàng từ khách hàng</p>
                            <a href="admin/manage_orders.php" class="btn btn-light">📄 Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card admin-card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h5 class="card-title">👥 Quản lý Người dùng</h5>
                            <p class="card-text">Xem danh sách user và quản lý tài khoản</p>
                            <a href="admin/manage_users.php" class="btn btn-dark">👤 Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card admin-card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">📢 Quản lý Banner</h5>
                            <p class="card-text">Thêm, chỉnh sửa banner quảng cáo</p>
                            <a href="admin/manage_banners.php" class="btn btn-light">📸 Đi đến</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>