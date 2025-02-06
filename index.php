<?php
include 'db.php';
session_start();

/**
 * Hàm thực hiện truy vấn trả về 1 bản ghi.
 *
 * @param mysqli $conn Kết nối cơ sở dữ liệu.
 * @param string $query Câu truy vấn SQL.
 * @param array  $params Mảng tham số.
 * @param string $paramTypes Kiểu tham số (vd: "i", "s", "ss", ...).
 * @return array|null Bản ghi (assoc array) hoặc null nếu lỗi.
 */
function getSingleResult($conn, $query, $params = [], $paramTypes = '')
{
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return null;
    }
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return null;
    }
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

/**
 * Hàm thực hiện truy vấn trả về nhiều bản ghi.
 *
 * @param mysqli $conn Kết nối cơ sở dữ liệu.
 * @param string $query Câu truy vấn SQL.
 * @param array  $params Mảng tham số.
 * @param string $paramTypes Kiểu tham số.
 * @return array Mảng các bản ghi (assoc arrays).
 */
function getResults($conn, $query, $params = [], $paramTypes = '')
{
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return [];
    }
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return [];
    }
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

// Lấy số dư ví của người dùng (nếu đã đăng nhập)
$wallet_balance = 0;
if (!empty($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id'];
    $walletData = getSingleResult($conn, "SELECT wallet_balance FROM users WHERE id = ?", [$user_id], "i");
    if ($walletData) {
        $wallet_balance = $walletData['wallet_balance'];
    }
}

// Lấy danh mục (DISTINCT category từ bảng themes)
$categories = getResults($conn, "SELECT DISTINCT category FROM themes");

// Xử lý tìm kiếm và lọc theo danh mục
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM themes WHERE name LIKE ?";
$params = ["%$search%"];
$paramTypes = "s";

if ($category && $category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $paramTypes .= "s";
}

$themes = getResults($conn, $sql, $params, $paramTypes);

// Lấy danh sách banner, sắp xếp giảm dần theo id
$banners = getResults($conn, "SELECT * FROM banners ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RVMedia68 | Chuyên gia thiết kế web app</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Inline CSS cho giao diện -->
    <style>
    body {
        background: linear-gradient(135deg, #f9f9f9, #e3f2fd);
        font-family: 'Poppins', sans-serif;
        color: #333;
    }

    /* Banner Carousel */
    .carousel-item img {
        object-fit: cover;
        height: 500px;
        width: 100%;
    }

    .carousel-caption {
        background: rgba(0, 0, 0, 0.5);
        padding: 20px;
        border-radius: 10px;
    }

    .carousel-caption h1 {
        font-size: 2.5rem;
        font-weight: 600;
    }

    .carousel-caption p {
        font-size: 1.2rem;
    }

    /* Sidebar (Danh mục) */
    aside {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    /* Theme Cards */
    .theme-card .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .theme-card .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .theme-card .card-img-top {
        border-radius: 15px 15px 0 0;
        height: 250px;
        object-fit: cover;
    }

    /* Telegram Float */
    .telegram-float {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        z-index: 1000;
    }

    .telegram-float:hover {
        transform: scale(1.1);
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
    }

    .telegram-icon {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    </style>
</head>

<body>
    <!-- Nhúng Header -->
    <?php include 'template/header.php'; ?>

    <div class="container mt-4">
        <!-- Hiển thị số dư ví của người dùng nếu đã đăng nhập -->
        <?php if (!empty($_SESSION['user_id'])): ?>
        <div class="alert alert-info text-center fw-bold">
            💰 Số dư ví của bạn:
            <span class="text-success">
                <?php echo number_format($wallet_balance, 0, ',', '.'); ?> VNĐ
            </span>
        </div>
        <?php endif; ?>

        <!-- Banner Carousel -->
        <div id="bannerCarousel" class="carousel slide shadow-sm rounded mb-5" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php 
                $first = true;
                foreach ($banners as $banner): ?>
                <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($banner['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($banner['title']); ?>">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="fw-bold"><?php echo htmlspecialchars($banner['title']); ?></h1>
                        <p class="lead"><?php echo htmlspecialchars($banner['description']); ?></p>
                        <?php if (!empty($banner['link'])): ?>
                        <a href="<?php echo htmlspecialchars($banner['link']); ?>"
                            class="btn btn-light btn-lg fw-bold">🌟 Xem Ngay</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    $first = false;
                endforeach; ?>
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
        <h2 class="text-center fw-bold mb-4">- SOURCE CODE NGON NGÀY MỚI UPDATE -</h2>
        <div class="row">
            <!-- Sidebar (Danh mục) -->
            <aside class="col-md-3">
                <h5 class="fw-bold mb-3">📂 Danh Mục</h5>
                <div class="list-group">
                    <button class="list-group-item list-group-item-action category-btn active" data-category="all">Tất
                        cả</button>
                    <?php foreach ($categories as $cat): ?>
                    <button class="list-group-item list-group-item-action category-btn"
                        data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Danh sách Theme -->
            <div class="col-md-9">
                <div class="row" id="theme-list">
                    <?php if (count($themes) > 0): ?>
                    <?php foreach ($themes as $theme): ?>
                    <div class="col-md-6 col-lg-4 mb-4 theme-card"
                        data-category="<?php echo htmlspecialchars($theme['category']); ?>">
                        <div class="card">
                            <?php
                                $imagePath = trim($theme['image_url']);
                                if (empty($imagePath)) {
                                    $imagePath = 'assets/default.png';
                                } elseif (!preg_match('/^(http|\/rvmedia2\/uploads\/)/', $imagePath)) {
                                    $imagePath = "/rvmedia2/uploads/" . $imagePath;
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top"
                                alt="Theme Image" loading="lazy" onerror="this.src='assets/default.png';">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($theme['name']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($theme['description']); ?>
                                </p>
                                <p class="fw-bold text-danger fs-4"><?php echo number_format($theme['price'], 2); ?> VND
                                </p>
                                <div class="d-flex justify-content-center mb-3">
                                    <span class="badge rounded-pill bg-secondary text-white px-3 py-2 me-3">
                                        <?php echo number_format($theme['views']); ?> lượt xem
                                    </span>
                                    <span class="badge rounded-pill bg-secondary text-white px-3 py-2">
                                        <?php echo number_format($theme['purchases']); ?> lượt mua
                                    </span>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <a href="product.php?id=<?php echo $theme['id']; ?>"
                                        class="btn btn-lg btn-outline-primary me-3">Xem demo</a>
                                    <a href="cart.php?id=<?php echo $theme['id']; ?>"
                                        class="btn btn-lg btn-success">Thêm vào giỏ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p class="text-center text-muted fs-5">❌ Không tìm thấy theme nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bóng bấm Telegram -->
    <a href="https://t.me/yourtelegramchannel" target="_blank" class="telegram-float" title="Tham gia Telegram">
        <img src="assets/image.png" alt="Telegram" class="telegram-icon">
    </a>

    <!-- Nhúng Footer -->
    <?php include 'template/footer.php'; ?>

    <!-- Scripts: Bootstrap & JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/core.js"></script>
</body>

</html>