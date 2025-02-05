<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT amount, type, description, created_at FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Lịch sử ví tiền</title>
</head>

<body>
    <h2>Lịch sử giao dịch</h2>
    <table border="1">
        <tr>
            <th>Số tiền</th>
            <th>Loại giao dịch</th>
            <th>Mô tả</th>
            <th>Thời gian</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= number_format($row['amount'], 2) ?> USDT</td>
            <td><?= ucfirst($row['type']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">Quay lại</a>
</body>

</html>