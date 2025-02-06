<?php
session_start();
require 'db.php';

// Kiểm tra nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin người dùng từ database
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT username, email, wallet_balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    if (!empty($new_username) && !empty($new_email)) {
        $update_query = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $update_query->bind_param("ssi", $new_username, $new_email, $user_id);
        if ($update_query->execute()) {
            $_SESSION['success'] = "✅ Cập nhật thành công!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "❌ Có lỗi xảy ra. Vui lòng thử lại!";
        }
    } else {
        $_SESSION['error'] = "⚠ Vui lòng điền đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân | Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/core.css">
    <style>
    body {
        background-color: #f4f7fc;
    }

    .profile-container {
        max-width: 600px;
        margin: auto;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    }

    .profile-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
    }

    .btn-update {
        background: linear-gradient(45deg, #007bff, #6610f2);
        border: none;
        transition: 0.3s;
    }

    .btn-update:hover {
        background: linear-gradient(45deg, #6610f2, #007bff);
        transform: scale(1.05);
    }
    </style>
</head>

<body>

    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="container mt-5">
        <div class="profile-container">
            <div class="text-center">
                <img src="assets/user.png" class="profile-img shadow" alt="User Profile">
                <h3 class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <!-- Hiển thị thông báo -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success text-center">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="mt-3">
                <div class="mb-3">
                    <label class="form-label fw-bold">👤 Tên đăng nhập:</label>
                    <input type="text" name="username" class="form-control"
                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">✉️ Email:</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">💰 Số dư ví:</label>
                    <input type="text" class="form-control fw-bold text-success"
                        value="<?php echo number_format($user['wallet_balance'], 0, ',', '.'); ?> VNĐ" readonly>
                </div>

                <button type="submit" class="btn btn-update text-white w-100 py-2">💾 Cập nhật</button>
                <div class="d-flex justify-content-between mt-3">
                    <a href="index.php" class="btn btn-outline-secondary">🏠 Trang chủ</a>
                    <a href="logout.php" class="btn btn-outline-danger">🚪 Đăng xuất</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>