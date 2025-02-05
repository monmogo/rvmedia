<link rel="stylesheet" type="text/css" href="./assets/core.css">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold" href="index.php">🎨 Theme Store</a>

        <form method="GET" class="search-box mx-auto d-none d-lg-block">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="🔍 Tìm kiếm..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Tìm</button>
            </div>
        </form>

        <div class="d-flex">
            <a href="cart.php" class="btn btn-outline-dark me-2">🛒 Giỏ Hàng</a>
            <a href="login.php" class="btn btn-outline-dark">🔑 Đăng Nhập</a>
        </div>

        <button class="navbar-toggler ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>