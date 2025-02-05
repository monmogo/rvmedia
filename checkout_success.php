<?php session_start(); ?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Thành Công</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <!-- Navbar -->
    <?php include 'template/header.php'; ?>

    <div class="container text-center mt-5">
        <h2 class="text-success fw-bold">🎉 Thanh Toán Thành Công!</h2>
        <p>Cảm ơn bạn đã mua theme! Đơn hàng của bạn đang được xử lý.</p>
        <a href="index.php" class="btn btn-primary mt-3">🏠 Quay về Trang Chủ</a>
    </div>

    <!-- Footer -->
    <?php include 'template/footer.php'; ?>

</body>

</html>