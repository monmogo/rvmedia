<?php
session_start();
include 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý yêu cầu nạp tiền
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);

    if ($amount <= 0) {
        $error = "Số tiền không hợp lệ!";
    } else {
        // Lưu giao dịch vào bảng `wallet_transactions`
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, status, description) VALUES (?, ?, 'deposit', 'pending', ?)");
        $stmt->bind_param("ids", $user_id, $amount, $description);
        if ($stmt->execute()) {
            $success = "Yêu cầu nạp tiền đã được gửi. Vui lòng chờ Admin duyệt!";
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp Tiền</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
</head>

<body>

    <?php include 'template/header.php'; ?>

    <div class="container mt-5">
        <h2 class="fw-bold text-center">💰 Nạp Tiền Vào Ví</h2>

        <?php if (isset($success)): ?>
        <div class="alert alert-success text-center"><?= $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"><?= $error; ?></div>
        <?php endif; ?>

        <div class="card p-4 shadow mx-auto" style="max-width: 500px;">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Số tiền (VNĐ):</label>
                    <input type="number" name="amount" class="form-control" min="10000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả giao dịch:</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập nội dung nạp tiền..."
                        required></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">📤 Gửi Yêu Cầu</button>
            </form>
        </div>
    </div>

    <?php include 'template/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>