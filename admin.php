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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom Admin CSS (nếu có) -->
    <link rel="stylesheet" type="text/css" href="assets/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Tổng thể */
    body {
        background: linear-gradient(135deg, #f4f6f9, #e9ecef);
        font-family: 'Poppins', sans-serif;
    }

    .admin-container {
        display: flex;
        min-height: 100vh;
    }

    /* Nội dung chính */
    .content {
        flex: 1;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 20px;
        margin: 20px;
    }

    /* Admin Cards */
    .admin-card {
        border: none;
        border-radius: 10px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .admin-card h5 {
        font-size: 1.2rem;
    }

    .admin-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .admin-card .card-icon {
        font-size: 3rem;
        margin-bottom: 10px;
    }

    /* Màu sắc Gradient cao cấp */
    .bg-primary {
        background: linear-gradient(135deg, #007bff, #0056b3) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #28a745, #1e7e34) !important;
    }

    .bg-warning {
        background: linear-gradient(135deg, #ffc107, #d39e00) !important;
    }

    .bg-danger {
        background: linear-gradient(135deg, #dc3545, #a71d2a) !important;
    }

    .bg-info {
        background: linear-gradient(135deg, #17a2b8, #0c5460) !important;
    }

    /* Biểu đồ & Card thống kê */
    .card.shadow {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card-body h2 {
        font-size: 2rem;
    }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'template/sidebar.php'; ?>

        <!-- Nội dung chính -->
        <main class="content">
            <h2 class="fw-bold text-center">👑 Chào mừng, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </h2>
            <p class="text-center text-muted">Quản lý toàn bộ hệ thống một cách chuyên nghiệp.</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card admin-card bg-primary text-white text-center">
                        <div class="card-body">
                            <div class="card-icon">📦</div>
                            <h5 class="card-title">Quản lý Theme</h5>
                            <p class="card-text">Thêm, chỉnh sửa, xóa các theme</p>
                            <a href="admin/manage_themes.php" class="btn btn-light">🛠️ Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card admin-card bg-success text-white text-center">
                        <div class="card-body">
                            <div class="card-icon">🛒</div>
                            <h5 class="card-title">Quản lý Đơn hàng</h5>
                            <p class="card-text">Xem danh sách đơn hàng từ khách hàng</p>
                            <a href="admin/manage_orders.php" class="btn btn-light">📄 Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card admin-card bg-warning text-dark text-center">
                        <div class="card-body">
                            <div class="card-icon">👥</div>
                            <h5 class="card-title">Quản lý Người dùng</h5>
                            <p class="card-text">Xem danh sách user và quản lý tài khoản</p>
                            <a href="admin/manage_users.php" class="btn btn-dark">👤 Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card admin-card bg-danger text-white text-center">
                        <div class="card-body">
                            <div class="card-icon">📢</div>
                            <h5 class="card-title">Quản lý Banner</h5>
                            <p class="card-text">Thêm, chỉnh sửa banner quảng cáo</p>
                            <a href="admin/manage_banners.php" class="btn btn-light">📸 Đi đến</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card admin-card bg-info text-white text-center">
                        <div class="card-body">
                            <div class="card-icon">💰</div>
                            <h5 class="card-title">Quản lý Giao Dịch</h5>
                            <p class="card-text">Duyệt yêu cầu nạp/rút tiền</p>
                            <a href="admin/manage_wallet.php" class="btn btn-light">📥 Đi đến</a>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="fw-bold text-center mt-5">📊 Phân tích bán hàng & Dòng tiền</h2>

            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white text-center shadow">
                        <div class="card-body">
                            <h5 class="card-title">💰 Tổng Doanh Thu</h5>
                            <h2 class="fw-bold"><?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center shadow">
                        <div class="card-body">
                            <h5 class="card-title">📥 Tổng Nạp Tiền</h5>
                            <h2 class="fw-bold"><?= number_format($totalDeposits, 0, ',', '.') ?> VNĐ</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white text-center shadow">
                        <div class="card-body">
                            <h5 class="card-title">📤 Tổng Rút Tiền</h5>
                            <h2 class="fw-bold"><?= number_format($totalWithdrawals, 0, ',', '.') ?> VNĐ</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ Dòng Tiền -->
            <div class="card shadow mt-4 p-3">
                <h5 class="fw-bold text-center">📈 Biểu đồ Dòng Tiền (6 tháng gần nhất)</h5>
                <canvas id="salesChart"></canvas>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $monthsJSON ?>,
            datasets: [{
                label: 'Doanh Thu (VNĐ)',
                data: <?= $salesJSON ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });
    </script>
</body>

</html>