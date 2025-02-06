<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Xử lý duyệt giao dịch
if (isset($_GET['approve'])) {
    $txn_id = intval($_GET['approve']);

    // Lấy thông tin giao dịch
    $stmt = $conn->prepare("SELECT * FROM wallet_transactions WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("i", $txn_id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();

    if ($transaction) {
        $user_id = $transaction['user_id'];
        $amount = $transaction['amount'];
        $type = $transaction['type'];

        // Cập nhật trạng thái giao dịch
        $stmt = $conn->prepare("UPDATE wallet_transactions SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $txn_id);
        $stmt->execute();

        // Nếu là nạp tiền, cộng vào ví
        if ($type === 'deposit') {
            $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
        }

        header("Location: manage_wallet.php?success=approve");
        exit();
    }
}

// Xử lý từ chối giao dịch
if (isset($_GET['reject'])) {
    $txn_id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE wallet_transactions SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $txn_id);
    $stmt->execute();
    header("Location: manage_wallet.php?success=reject");
    exit();
}

// Lấy danh sách giao dịch
$transactions = $conn->query("SELECT wallet_transactions.*, users.username FROM wallet_transactions JOIN users ON wallet_transactions.user_id = users.id ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nạp & Rút tiền</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>


    <div class="container mt-5">
        <h2 class="fw-bold">📜 Quản lý Nạp & Rút tiền</h2>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✅ <?php echo ($_GET['success'] == 'approve') ? "Giao dịch đã được duyệt!" : "Giao dịch đã bị từ chối!"; ?>
        </div>
        <?php endif; ?>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Loại giao dịch</th>
                    <th>Số tiền</th>
                    <th>Mô tả</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($txn = $transactions->fetch_assoc()): ?>
                <tr>
                    <td><?= $txn['id'] ?></td>
                    <td><?= htmlspecialchars($txn['username']) ?></td>
                    <td class="<?= $txn['type'] == 'deposit' ? 'text-success' : 'text-danger' ?>">
                        <?= ucfirst($txn['type']) ?>
                    </td>
                    <td class="fw-bold text-primary"><?= number_format($txn['amount'], 2) ?> VNĐ</td>
                    <td><?= htmlspecialchars($txn['description']) ?></td>
                    <td>
                        <?php
                    if ($txn['status'] == 'pending') {
                        echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                    } elseif ($txn['status'] == 'approved') {
                        echo '<span class="badge bg-success">Đã duyệt</span>';
                    } else {
                        echo '<span class="badge bg-danger">Bị từ chối</span>';
                    }
                    ?>
                    </td>
                    <td><?= $txn['created_at'] ?></td>
                    <td>
                        <?php if ($txn['status'] == 'pending'): ?>
                        <a href="?approve=<?= $txn['id'] ?>" class="btn btn-success btn-sm">✔️ Duyệt</a>
                        <a href="?reject=<?= $txn['id'] ?>" class="btn btn-danger btn-sm">❌ Từ chối</a>
                        <?php else: ?>
                        <span class="text-muted">Đã xử lý</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include 'admin_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>