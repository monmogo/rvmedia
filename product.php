<?php
include 'db.php';

// Kiểm tra xem có ID sản phẩm không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ Không tìm thấy theme!");
}

$theme_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM themes WHERE id = ?");
$stmt->bind_param("i", $theme_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Theme không tồn tại!");
}

$theme = $result->fetch_assoc();

// 🌟 Tăng lượt xem khi truy cập
$conn->query("UPDATE themes SET views = views + 1 WHERE id = $theme_id");

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($theme['name']); ?> | Chi Tiết Theme</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/core.css">

    <style>
    /* 🌟 Thiết kế hiện đại */
    body {
        background-color: #f8f9fa;
        color: #333;
    }

    .theme-container {
        max-width: 1100px;
        margin: auto;
        padding: 40px 20px;
    }

    .theme-image {
        border-radius: 12px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease-in-out;
    }

    .theme-image:hover {
        transform: scale(1.05);
    }

    .theme-details h1 {
        font-size: 2rem;
        font-weight: bold;
        color: #222;
    }

    .theme-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: #d9534f;
    }

    .theme-badges {
        display: flex;
        gap: 12px;
        margin: 15px 0;
    }

    .theme-badges span {
        font-size: 0.95rem;
        padding: 8px 14px;
        border-radius: 20px;
        background-color: #e9ecef;
        color: #555;
        font-weight: 600;
    }

    .btn-custom {
        font-size: 1.1rem;
        padding: 12px 20px;
        border-radius: 8px;
    }

    .btn-demo {
        border: 2px solid #007bff;
        color: #007bff;
        transition: all 0.3s;
    }

    .btn-demo:hover {
        background-color: #007bff;
        color: white;
    }

    .btn-buy {
        background-color: #28a745;
        color: white;
        transition: all 0.3s;
    }

    .btn-buy:hover {
        background-color: #218838;
    }

    .theme-description {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        line-height: 1.8;
    }
    </style>
</head>

<body>

    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="theme-container">
        <div class="row align-items-center">
            <!-- Ảnh Theme -->
            <div class="col-md-6 text-center">
                <?php
                $imagePath = trim($theme['image_url']);
                if (empty($imagePath)) {
                    $imagePath = 'assets/default.png';
                } elseif (!preg_match('/^(http|\/rvmedia2\/uploads\/)/', $imagePath)) {
                    $imagePath = "/rvmedia2/uploads/" . $imagePath;
                }
                ?>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="img-fluid theme-image"
                    alt="<?php echo htmlspecialchars($theme['name']); ?>">
            </div>

            <!-- Thông Tin Theme -->
            <div class="col-md-6 theme-details">
                <h1><?php echo htmlspecialchars($theme['name']); ?></h1>

                <!-- 🌟 Giá Theme -->
                <p class="theme-price"><?php echo number_format($theme['price'], 2); ?> VND</p>

                <!-- 🌟 Hiển thị lượt xem & lượt mua -->
                <div class="theme-badges">
                    <span>👀 <?php echo number_format($theme['views']); ?> lượt xem</span>
                    <span>🛍 <?php echo number_format($theme['purchases']); ?> lượt mua</span>
                </div>

                <!-- 🌟 Nút chức năng -->
                <div class="d-flex gap-3">
                    <a href="<?php echo htmlspecialchars($theme['file_url']); ?>" target="_blank"
                        class="btn btn-lg btn-custom btn-demo">Xem demo</a>
                    <a href="cart.php?id=<?php echo $theme['id']; ?>" class="btn btn-lg btn-custom btn-buy">Thêm vào
                        giỏ</a>
                </div>

                <hr>

                <p class="text-muted">📅 Ngày tạo: <?php echo date('d/m/Y', strtotime($theme['created_at'])); ?></p>
                <p class="text-muted">📂 Danh mục: <strong><?php echo htmlspecialchars($theme['category']); ?></strong>
                </p>
            </div>
        </div>

        <!-- Mô Tả Theme -->
        <div class="mt-5 theme-description">
            <h3 class="fw-bold">📖 Mô Tả Chi Tiết</h3>
            <p><?php echo nl2br(htmlspecialchars($theme['description'])); ?></p>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'template/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>