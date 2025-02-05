<?php
include 'db.php';
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['username'] = $result['username'];
        $_SESSION['role'] = $result['role']; // Lưu role vào session

        // Kiểm tra nếu là admin thì chuyển hướng đến trang admin
        if ($result['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php"); // Chuyển hướng user về trang chủ
        }
        exit();
    } else {
        $error_message = "❌ Sai email hoặc mật khẩu!";
    }
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    body {
        background: #f8f9fa;
    }

    .login-container {
        max-width: 400px;
        margin: auto;
        margin-top: 80px;
        padding: 30px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .login-container h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .form-control {
        border-radius: 8px;
    }

    .btn-login {
        width: 100%;
        border-radius: 8px;
    }

    .error-message {
        color: red;
        font-size: 14px;
    }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>🔑 Đăng Nhập</h2>

        <?php if ($error_message): ?>
        <p class="error-message text-center"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">📧 Email:</label>
                <input type="email" name="email" class="form-control" required placeholder="Nhập email của bạn">
            </div>
            <div class="mb-3">
                <label class="form-label">🔒 Mật khẩu:</label>
                <input type="password" name="password" class="form-control" required placeholder="Nhập mật khẩu">
            </div>
            <button type="submit" class="btn btn-primary btn-login">🚀 Đăng Nhập</button>
        </form>

        <p class="text-center mt-3">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>