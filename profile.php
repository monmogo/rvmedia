<?php
session_start();
require 'db.php';

// Nếu người dùng chưa đăng nhập, chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT username, email, wallet_balance FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Xử lý cập nhật thông tin cá nhân khi form được gửi
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = trim($_POST['username']);
    $new_email    = trim($_POST['email']);

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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/core.css">
    <style>
    /* Tổng quan cho body */
    body {
        background: linear-gradient(135deg, #e0eafc, #cfdef3);
        font-family: 'Poppins', sans-serif;
        min-height: 100vh;
    }

    /* Container hồ sơ */
    .profile-container {
        max-width: 600px;
        margin: 50px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* Ảnh đại diện */
    .profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 20px;
        border: 4px solid #007bff;
    }

    /* Nút cập nhật */
    .btn-update {
        background: linear-gradient(135deg, #007bff, #6610f2);
        border: none;
        transition: background 0.3s, transform 0.3s;
    }

    .btn-update:hover {
        background: linear-gradient(135deg, #6610f2, #007bff);
        transform: scale(1.03);
    }

    /* Label của form */
    .form-label {
        font-weight: 600;
    }

    /* Thông báo */
    .alert {
        border-radius: 10px;
    }

    /* Box hiển thị số dư ví */
    .info-card {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        margin-bottom: 20px;
    }

    .info-card input {
        background: transparent;
        border: none;
        font-size: 1.1rem;
        font-weight: 600;
        color: #28a745;
    }
    </style>
</head>

<body>
    <!-- Nhúng thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="container">
        <div class="profile-container">
            <div class="text-center">
                <img src="assets/user.png" alt="User Profile" class="profile-img shadow">
                <h3 class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <!-- Hiển thị thông báo nếu có -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success text-center">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">👤 Tên đăng nhập</label>
                    <input type="text" id="username" name="username" class="form-control"
                        value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">✉️ Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3 info-card">
                    <label class="form-label">💰 Số dư ví</label>
                    <input type="text" class="form-control fw-bold text-success"
                        value="<?php echo number_format($user['wallet_balance'], 0, ',', '.'); ?> VNĐ" readonly>
                </div>

                <button type="submit" class="btn btn-update text-white w-100 py-2">💾 Cập nhật</button>
                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-outline-secondary px-4">🏠 Trang chủ</a>
                    <a href="logout.php" class="btn btn-outline-danger px-4">🚪 Đăng xuất</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>