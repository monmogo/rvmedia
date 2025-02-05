<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$theme_id = intval($_GET['id']);

// Lấy thông tin theme
$stmt = $conn->prepare("SELECT price FROM themes WHERE id = ?");
$stmt->bind_param("i", $theme_id);
$stmt->execute();
$result = $stmt->get_result();
$theme = $result->fetch_assoc();

if (!$theme) {
    die("Theme không tồn tại!");
}

$theme_price = $theme['price'];

// Kiểm tra số dư ví
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['wallet_balance'] < $theme_price) {
    die("Số dư ví không đủ để mua theme này!");
}

// Trừ tiền trong ví
$stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
$stmt->bind_param("di", $theme_price, $user_id);
$stmt->execute();

// Ghi giao dịch
$stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', 'Mua theme ID $theme_id')");
$stmt->bind_param("id", $user_id, $theme_price);
$stmt->execute();

// Lưu đơn hàng
$stmt = $conn->prepare("INSERT INTO orders (user_id, theme_id, status) VALUES (?, ?, 'completed')");
$stmt->bind_param("ii", $user_id, $theme_id);
$stmt->execute();

echo "Mua theme thành công!";
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
                        <?php 
    $imagePath = !empty($theme['image_url']) ? 'uploads/' . htmlspecialchars($theme['image_url']) : 'assets/default.png';
    ?>
                        <img src="<?= $imagePath ?>" width="80" class="rounded shadow"
                            onerror="this.src='assets/default.png';">
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
                <a href="checkout.php" class="btn btn-success">💳 Thanh Toán</a>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'template/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>