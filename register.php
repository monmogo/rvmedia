<?php
include 'db.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra các trường có bị bỏ trống không
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "⚠️ Vui lòng điền đầy đủ thông tin!";
    } elseif ($password !== $confirm_password) {
        $error_message = "❌ Mật khẩu xác nhận không khớp!";
    } else {
        // Kiểm tra xem email đã tồn tại chưa
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $error_message = "⚠️ Email đã được sử dụng!";
        } else {
            // Mã hóa mật khẩu và lưu vào database
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $success_message = "✅ Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a>";
            } else {
                $error_message = "❌ Lỗi hệ thống, vui lòng thử lại!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    body {
        background: #f8f9fa;
    }

    .register-container {
        max-width: 450px;
        margin: auto;
        margin-top: 60px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .register-container h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .form-control {
        border-radius: 8px;
    }

    .btn-register {
        width: 100%;
        border-radius: 8px;
    }

    .message {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }
    </style>
</head>

<body>

    <div class="register-container">
        <h2>📝 Đăng Ký</h2>

        <?php if ($error_message): ?>
        <p class="message text-danger"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
        <p class="message text-success"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">👤 Tên người dùng:</label>
                <input type="text" name="username" class="form-control" required placeholder="Nhập tên của bạn">
            </div>
            <div class="mb-3">
                <label class="form-label">📧 Email:</label>
                <input type="email" name="email" class="form-control" required placeholder="Nhập email">
            </div>
            <div class="mb-3">
                <label class="form-label">🔒 Mật khẩu:</label>
                <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
            </div>
            <div class="mb-3">
                <label class="form-label">🔄 Xác nhận mật khẩu:</label>
                <input type="password" name="confirm_password" class="form-control" required
                    placeholder="Nhập lại mật khẩu">
            </div>
            <button type="submit" class="btn btn-primary btn-register">🚀 Đăng Ký</button>
        </form>

        <p class="text-center mt-3">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>