<?php
include 'db.php';

// Lấy danh mục từ database
$categoriesStmt = $conn->prepare("SELECT DISTINCT category FROM themes");
$categoriesStmt->execute();
$categories = $categoriesStmt->get_result();

// Xử lý tìm kiếm và danh mục
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM themes WHERE name LIKE ?";
$params = ["%$search%"];

if ($category && $category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
// Lấy danh sách banner từ database
$bannerStmt = $conn->prepare("SELECT * FROM banners ORDER BY id DESC");
$bannerStmt->execute();
$banners = $bannerStmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Store | Kho Giao Diện Đẹp</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <!-- Nhúng Header -->
    <?php include 'template/header.php'; ?>
    <div class="container mt-4">
        <!-- 🌟 Banner Carousel -->
        <div id="bannerCarousel" class="carousel slide shadow-sm rounded mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
            $first = true;
            while ($banner = $banners->fetch_assoc()): 
            ?>
                <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($banner['image_url']); ?>" class="d-block w-100 banner-img"
                        alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="fw-bold"><?php echo htmlspecialchars($banner['title']); ?></h1>
                        <p class="lead"><?php echo htmlspecialchars($banner['description']); ?></p>
                        <?php if (!empty($banner['link'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['link']); ?>"
                            class="btn btn-light btn-lg fw-bold">🌟
                            Xem Ngay</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
            $first = false;
            endwhile; 
            ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Trước</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sau</span>
            </button>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center fw-bold mb-4">🎨 Khám Phá Các Theme Đẹp</h2>

        <div class="row">
            <!-- Sidebar (Danh Mục) -->
            <aside class="col-md-3">
                <h5 class="fw-bold">📂 Danh Mục</h5>
                <div class="list-group">
                    <button class="list-group-item list-group-item-action category-btn active" data-category="all">Tất
                        cả</button>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                    <button class="list-group-item list-group-item-action category-btn"
                        data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </button>
                    <?php endwhile; ?>
                </div>
            </aside>

            <!-- Danh sách Theme -->
            <div class="col-md-9">
                <div class="row" id="theme-list">
                    <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4 theme-card"
                        data-category="<?php echo htmlspecialchars($row['category']); ?>">
                        <div class="card">
                            <?php
                                $imagePath = trim($row['image_url']);
                                if (empty($imagePath)) {
                                    $imagePath = 'assets/default.png';
                                } elseif (!preg_match('/^(http|\/rvmedia2\/uploads\/)/', $imagePath)) {
                                    $imagePath = "/rvmedia2/uploads/" . $imagePath;
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top"
                                alt="Theme Image" onerror="this.src='assets/default.png';">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="fw-bold text-danger">💰 <?php echo number_format($row['price'], 2); ?> USDT
                                </p>
                                <div class="d-flex justify-content-center">
                                    <a href="product.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-outline-primary me-2">📖 Xem demo</a>
                                    <a href="cart.php?id=<?php echo $row['id']; ?>" class="btn btn-success">🛒 Thêm vào
                                        giỏ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <p class="text-center text-muted fs-5">❌ Không tìm thấy theme nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Nhúng Footer -->
    <?php include 'template/footer.php'; ?>

    <!-- Bootstrap & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/core.js"></script>
</body>

</html>