<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý thêm theme vào giỏ hàng (tạo đơn hàng với trạng thái `pending`)
if (isset($_GET['id'])) {
    $theme_id = intval($_GET['id']);

    // Kiểm tra theme có tồn tại không
    $stmt = $conn->prepare("SELECT price FROM themes WHERE id = ?");
    $stmt->bind_param("i", $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $theme = $result->fetch_assoc();

    if (!$theme) {
        die("❌ Theme không tồn tại!");
    }

    // Kiểm tra nếu theme đã có trong giỏ hàng (đơn hàng `pending`)
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND theme_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $user_id, $theme_id);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    if ($cart_result->num_rows == 0) {
        // Thêm vào giỏ hàng (tạo đơn hàng `pending`)
        $stmt = $conn->prepare("INSERT INTO orders (user_id, theme_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $user_id, $theme_id);
        $stmt->execute();
    }

    header("Location: cart.php");
    exit();
}

// Xử lý xóa theme khỏi giỏ hàng (xóa đơn hàng `pending`)
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ? AND theme_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $user_id, $remove_id);
    $stmt->execute();
    header("Location: cart.php");
    exit();
}

// Xử lý xóa toàn bộ giỏ hàng
if (isset($_GET['clear'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: cart.php");
    exit();
}

// Lấy danh sách theme trong giỏ hàng (đơn hàng `pending`)
$stmt = $conn->prepare("SELECT themes.* FROM orders INNER JOIN themes ON orders.theme_id = themes.id WHERE orders.user_id = ? AND orders.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Tính tổng tiền
$total = 0;
$themes = [];
while ($row = $cart_items->fetch_assoc()) {
    $themes[] = $row;
    $total += $row['price'];
}

// Kiểm tra số dư ví
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$wallet_balance = $user['wallet_balance'];

// Xử lý thanh toán
if (isset($_GET['checkout']) && $total > 0) {
    if ($wallet_balance >= $total) {
        // Trừ tiền
        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $stmt->bind_param("di", $total, $user_id);
        $stmt->execute();

        // Cập nhật đơn hàng thành `completed`
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE user_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Ghi nhận giao dịch
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', 'Thanh toán đơn hàng')");
        $stmt->bind_param("id", $user_id, $total);
        $stmt->execute();

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
</head>

<body>

    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="container mt-5">
        <h2 class="fw-bold">🛒 Giỏ Hàng Của Bạn</h2>

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
                    <th>Hình ảnh</th>
                    <th>Tên Theme</th>
                    <th>Giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($themes as $theme): ?>
                <tr>
                    <td>
                        <img src="uploads/<?= htmlspecialchars($theme['image_url'] ?? 'default.png') ?>" width="80"
                            class="rounded shadow" onerror="this.src='assets/default.png';">
                    </td>
                    <td><?= htmlspecialchars($theme['name']) ?></td>
                    <td class="fw-bold text-danger"><?= number_format($theme['price'], 2) ?> USDT</td>
                    <td>
                        <a href="cart.php?remove=<?= $theme['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa theme này khỏi giỏ hàng?')">🗑️ Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <h4 class="fw-bold">Tổng tiền: <span class="text-danger"><?= number_format($total, 2) ?> USDT</span></h4>
            <div>
                <a href="cart.php?clear=1" class="btn btn-outline-danger"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')">🗑️ Xóa giỏ hàng</a>
                <a href="cart.php?checkout=1" class="btn btn-success">💳 Thanh Toán</a>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'template/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>