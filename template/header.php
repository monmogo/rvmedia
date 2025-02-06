<?php
session_start();
?>
<link rel="stylesheet" type="text/css" href="./assets/core.css">
<link rel="stylesheet" type="text/css" href="./assets/sidebar.css">


<!-- 🌟 HEADER NAVIGATION -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
    <div class="container d-flex justify-content-between align-items-center">

        <!-- 🎨 Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="/rvmedia2/assets/logo.png" alt="Logo">

        </a>

        <!-- 🔍 Thanh Tìm Kiếm -->
        <form method="GET" class="search-box d-none d-lg-flex">
            <div class="input-group">
                <input type="text" name="search" class="form-control rounded-start" placeholder="🔍 Tìm kiếm app..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-end">Tìm</button>
            </div>
        </form>

        <!-- 🛒 User Menu -->
        <div class="user-menu d-flex align-items-center">
            <a href="cart.php" class="btn btn-outline-dark">🛒 Giỏ Hàng</a>

            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="btn btn-outline-dark">👤 Hồ sơ</a>
            <a href="deposit.php" class="btn btn-warning">💳 Nạp Tiền</a>
            <a href="logout.php" class="btn btn-danger">🚪 Đăng Xuất</a>
            <?php else: ?>
            <a href="login.php" class="btn btn-outline-dark">🔑 Đăng Nhập</a>
            <?php endif; ?>
        </div>

        <!-- ☰ Nút Toggle Menu Mobile -->
        <button class="navbar-toggler ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>