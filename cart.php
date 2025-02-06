<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* --- XỬ LÝ THÊM THEME VÀO GIỎ HÀNG (pending order) --- */
if (isset($_GET['id'])) {
    $theme_id = intval($_GET['id']);

    // Kiểm tra theme có tồn tại không
    $stmt = $conn->prepare("SELECT price FROM themes WHERE id = ?");
    $stmt->bind_param("i", $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $theme = $result->fetch_assoc();
    $stmt->close();

    if (!$theme) {
        die("❌ Theme không tồn tại!");
    }

    // Kiểm tra nếu theme chưa có trong giỏ hàng (trạng thái pending)
    $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? AND theme_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $user_id, $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO orders (user_id, theme_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $theme_id);
        $stmt->execute();
    }
    $stmt->close();

    header("Location: cart.php");
    exit();
}

/* --- XỬ LÝ XÓA THEME KHỎI GIỎ HÀNG --- */
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ? AND theme_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $user_id, $remove_id);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

/* --- XỬ LÝ XÓA TOÀN BỘ GIỎ HÀNG --- */
if (isset($_GET['clear'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: cart.php");
    exit();
}

/* --- LẤY DANH SÁCH THEME TRONG GIỎ HÀNG (pending orders) --- */
$stmt = $conn->prepare("SELECT themes.* FROM orders INNER JOIN themes ON orders.theme_id = themes.id WHERE orders.user_id = ? AND orders.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

$total = 0;
$themes = [];
while ($row = $cart_items->fetch_assoc()) {
    $themes[] = $row;
    $total += $row['price'];
}

/* --- LẤY SỐ DƯ VÍ CỦA NGƯỜI DÙNG --- */
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();
$wallet_balance = $user_data['wallet_balance'];

/* --- XỬ LÝ THANH TOÁN --- */
if (isset($_GET['checkout']) && $total > 0) {
    if ($wallet_balance >= $total) {
        // Trừ tiền trong ví
        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->bind_param("di", $total, $user_id);
        $stmt->execute();
        $stmt->close();

        // Cập nhật đơn hàng thành completed
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE user_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Ghi nhận giao dịch vào bảng wallet_transactions
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', 'Thanh toán đơn hàng')");
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();
        $stmt->close();

        header("Location: cart.php?success=1");
        exit();
    } else {
        header("Location: cart.php?error=insufficient_balance");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
    <style>
    body {
        background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
        font-family: 'Poppins', sans-serif;
        color: #333;
    }

    .container {
        margin-top: 50px;
    }

    h2.fw-bold {
        margin-bottom: 30px;
    }

    table {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    table thead th {
        background: #007bff;
        color: #fff;
        border: none;
    }

    table tbody td {
        vertical-align: middle;
    }

    table img {
        border-radius: 8px;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .cart-total {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .action-buttons a {
        margin-right: 10px;
    }

    .alert {
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="container">
        <h2 class="fw-bold text-center">🛒 Giỏ Hàng Của Bạn</h2>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">✅ Thanh toán thành công!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'insufficient_balance'): ?>
        <div class="alert alert-danger text-center">❌ Số dư ví không đủ để thanh toán!</div>
        <?php endif; ?>

        <?php if (empty($themes)): ?>
        <div class="alert alert-warning text-center">❌ Giỏ hàng của bạn đang trống!</div>
        <?php else: ?>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th class="text-center">Hình ảnh</th>
                    <th>Tên Theme</th>
                    <th class="text-center">Giá</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($themes as $theme): ?>
                <tr>
                    <td class="text-center">
                        <img src="uploads/<?= htmlspecialchars($theme['image_url'] ?? 'default.png') ?>" width="80"
                            class="rounded shadow" onerror="this.src='assets/default.png';">
                    </td>
                    <td><?= htmlspecialchars($theme['name']) ?></td>
                    <td class="fw-bold text-danger text-center"><?= number_format($theme['price'], 2) ?> USDT</td>
                    <td class="text-center">
                        <a href="cart.php?remove=<?= $theme['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa theme này khỏi giỏ hàng?')">
                            🗑️ Xóa
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <h4 class="cart-total">Tổng tiền: <span class="text-danger"><?= number_format($total, 2) ?> USDT</span></h4>
            <div class="action-buttons">
                <a href="cart.php?clear=1" class="btn btn-outline-danger"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')">🗑️ Xóa giỏ hàng</a>
                <a href="cart.php?checkout=1" class="btn btn-success">💳 Thanh Toán</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Nhúng Footer -->
    <?php include 'template/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>