<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}

// Cập nhật trạng thái đơn hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: manage_orders.php");
    exit();
}

// Xóa đơn hàng
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM orders WHERE id = $delete_id");
    header("Location: manage_orders.php");
    exit();
}

// Lấy danh sách đơn hàng
$result = $conn->query("
    SELECT orders.id, users.username, themes.name AS theme_name, orders.status, orders.created_at
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN themes ON orders.theme_id = themes.id
    ORDER BY orders.id DESC
");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="container mt-4">
        <h2>📦 Quản Lý Đơn Hàng</h2>

        <table class="table table-bordered">
            <thead>
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
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['theme_name']); ?></td>
                    <td>
                        <form method="POST" class="d-flex">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <select name="status" class="form-select form-select-sm me-2">
                                <option value="pending" <?php echo ($row['status'] == 'pending') ? 'selected' : ''; ?>>
                                    Chờ xử lý</option>
                                <option value="completed"
                                    <?php echo ($row['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn tất</option>
                                <option value="canceled"
                                    <?php echo ($row['status'] == 'canceled') ? 'selected' : ''; ?>>Hủy</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">✔️ Cập
                                nhật</button>
                        </form>
                    </td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="manage_orders.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')">🗑️ Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>

</html>