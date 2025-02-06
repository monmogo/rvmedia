<?php
session_start();
include '../db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}

// Cập nhật trạng thái đơn hàng bằng AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    echo "Cập nhật thành công!";
    exit();
}

// Xóa đơn hàng
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM orders WHERE id = $delete_id");
    header("Location: manage_orders.php");
    exit();
}

// Tìm kiếm đơn hàng
$searchQuery = "";
if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $searchQuery = "AND (users.username LIKE ? OR themes.name LIKE ?)";
}

// Lấy danh sách đơn hàng với tìm kiếm
$sql = "
    SELECT orders.id, users.username, themes.name AS theme_name, orders.status, orders.created_at
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN themes ON orders.theme_id = themes.id
    WHERE 1=1 $searchQuery
    ORDER BY orders.id DESC
";
$stmt = $conn->prepare($sql);

if (!empty($_GET['search'])) {
    $stmt->bind_param("ss", $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

// Thống kê đơn hàng
$countPending = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'")->fetch_assoc()['total'];
$countCompleted = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];
$countCanceled = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'canceled'")->fetch_assoc()['total'];
$totalOrders = $countPending + $countCompleted + $countCanceled;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
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

    /* 🌟 Căn chỉnh nội dung */
    .content {
        margin-left: 250px;
        width: calc(100% - 250px);
        padding: 20px;
    }

    /* 🌟 Hiệu ứng bảng */
    .table th,
    .table td {
        vertical-align: middle;
    }

    .shadow-card {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- 🌟 Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- 🌟 Nội dung chính -->
        <div class="content">
            <h2 class="fw-bold">📦 Quản Lý Đơn Hàng</h2>

            <!-- Thống kê trạng thái đơn hàng -->
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="card bg-warning text-white text-center shadow-card">
                        <div class="card-body">
                            <h5 class="card-title">🕒 Chờ Xử Lý</h5>
                            <h2 class="fw-bold"><?= $countPending ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center shadow-card">
                        <div class="card-body">
                            <h5 class="card-title">✅ Hoàn Tất</h5>
                            <h2 class="fw-bold"><?= $countCompleted ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white text-center shadow-card">
                        <div class="card-body">
                            <h5 class="card-title">❌ Đã Hủy</h5>
                            <h2 class="fw-bold"><?= $countCanceled ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thanh tìm kiếm -->
            <form method="GET" class="my-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="🔍 Nhập tên khách hàng hoặc theme..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </form>

            <!-- Bảng đơn hàng -->
            <table class="table table-bordered shadow-card">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Tên Theme</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt hàng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['theme_name']); ?></td>
                        <td>
                            <form method="POST" class="d-flex update-status-form">
                                <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                <select name="status" class="form-select form-select-sm me-2 status-select"
                                    data-id="<?= $row['id']; ?>">
                                    <option value="pending" <?= ($row['status'] == 'pending') ? 'selected' : ''; ?>>Chờ
                                        xử lý</option>
                                    <option value="completed" <?= ($row['status'] == 'completed') ? 'selected' : ''; ?>>
                                        Hoàn tất</option>
                                    <option value="canceled" <?= ($row['status'] == 'canceled') ? 'selected' : ''; ?>>
                                        Hủy</option>
                                </select>
                            </form>
                        </td>
                        <td><?= $row['created_at']; ?></td>
                        <td>
                            <a href="manage_orders.php?delete_id=<?= $row['id']; ?>"
                                class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id']; ?>">🗑️ Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Cập nhật trạng thái đơn hàng bằng AJAX
    $(".status-select").change(function() {
        var orderId = $(this).data("id");
        var newStatus = $(this).val();

        $.post("manage_orders.php", {
            update_status: 1,
            order_id: orderId,
            status: newStatus
        }, function(response) {
            alert(response);
        });
    });

    // Cảnh báo trước khi xóa đơn hàng
    $(".delete-btn").click(function(e) {
        var confirmDelete = confirm("Bạn có chắc chắn muốn xóa đơn hàng này?");
        if (!confirmDelete) {
            e.preventDefault();
        }
    });
    </script>

</body>

</html>