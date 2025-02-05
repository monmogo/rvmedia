<?php
session_start();
include 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    
    if ($amount <= 0) {
        $error = "Số tiền nạp không hợp lệ!";
    } else {
        // Cập nhật số dư ví tiền
        $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        // Ghi lại giao dịch nạp tiền
        $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, amount, type, description) VALUES (?, ?, 'deposit', 'Nạp tiền vào ví')");
        $stmt->bind_param("id", $user_id, $amount);
        $stmt->execute();

        $success = "Nạp tiền thành công!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Nạp tiền vào ví</title>
</head>

<body>
    <h2>Nạp tiền vào ví</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="post">
        <label for="amount">Số tiền:</label>
        <input type="number" name="amount" required min="1" step="0.01">
        <button type="submit">Nạp tiền</button>
    </form>

    <a href="index.php">Quay lại</a>
</body>

</html>