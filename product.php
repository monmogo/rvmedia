<?php
include 'db.php';

// Kiểm tra tham số ID theme
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ Không tìm thấy theme!");
}

$theme_id = intval($_GET['id']);

// Lấy thông tin theme từ database
$stmt = $conn->prepare("SELECT * FROM themes WHERE id = ?");
$stmt->bind_param("i", $theme_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Theme không tồn tại!");
}

$theme = $result->fetch_assoc();
$stmt->close();

// Tăng lượt xem bằng prepared statement
$updateStmt = $conn->prepare("UPDATE themes SET views = views + 1 WHERE id = ?");
$updateStmt->bind_param("i", $theme_id);
$updateStmt->execute();
$updateStmt->close();

// Xử lý đường dẫn ảnh với fallback mặc định
$imagePath = trim($theme['image_url']);
if (empty($imagePath)) {
    $imagePath = 'assets/default.png';
} elseif (!preg_match('/^(http|\/rvmedia2\/uploads\/)/', $imagePath)) {
    $imagePath = "/rvmedia2/uploads/" . $imagePath;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($theme['name']); ?> | Chi Tiết Theme</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
    <style>
    /* Cài đặt font và background */
    body {
        background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
        color: #333;
        font-family: 'Poppins', sans-serif;
    }

    /* Container chính cho theme */
    .theme-container {
        max-width: 1100px;
        margin: 50px auto;
        padding: 40px 20px;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    /* Ảnh theme */
    .theme-image {
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease-in-out;
    }

    .theme-image:hover {
        transform: scale(1.05);
    }

    /* Thông tin chi tiết theme */
    .theme-details h1 {
        font-size: 2.2rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 20px;
    }

    .theme-price {
        font-size: 1.8rem;
        font-weight: 600;
        color: #d9534f;
        margin-bottom: 20px;
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

    /* Nút bấm tùy chỉnh */
    .btn-custom {
        font-size: 1.1rem;
        padding: 12px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-demo {
        border: 2px solid #007bff;
        color: #007bff;
    }

    .btn-demo:hover {
        background-color: #007bff;
        color: #fff;
    }

    .btn-buy {
        background-color: #28a745;
        color: #fff;
    }

    .btn-buy:hover {
        background-color: #218838;
    }

    /* Mô tả theme */
    .theme-description {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        line-height: 1.8;
        margin-top: 40px;
    }

    .theme-description h3 {
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="theme-container">
        <div class="row align-items-center">
            <!-- Ảnh Theme -->
            <div class="col-md-6 text-center mb-4 mb-md-0">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="img-fluid theme-image"
                    alt="<?php echo htmlspecialchars($theme['name']); ?>" loading="lazy">
            </div>
            <!-- Thông tin Theme -->
            <div class="col-md-6 theme-details">
                <h1><?php echo htmlspecialchars($theme['name']); ?></h1>
                <p class="theme-price"><?php echo number_format($theme['price'], 2); ?> VND</p>
                <div class="theme-badges">
                    <span>👀 <?php echo number_format($theme['views']); ?> lượt xem</span>
                    <span>🛍 <?php echo number_format($theme['purchases']); ?> lượt mua</span>
                </div>
                <div class="d-flex gap-3 mb-3">
                    <a href="<?php echo htmlspecialchars($theme['file_url']); ?>" target="_blank"
                        class="btn btn-custom btn-demo">Xem demo</a>
                    <a href="cart.php?id=<?php echo $theme['id']; ?>" class="btn btn-custom btn-buy">Thêm vào giỏ</a>
                </div>
                <hr>
                <p class="text-muted mb-1">📅 Ngày tạo: <?php echo date('d/m/Y', strtotime($theme['created_at'])); ?>
                </p>
                <p class="text-muted">📂 Danh mục: <strong><?php echo htmlspecialchars($theme['category']); ?></strong>
                </p>
            </div>
        </div>
        <!-- Mô tả chi tiết theme -->
        <div class="theme-description">
            <h3 class="fw-bold">📖 Mô Tả Chi Tiết</h3>
            <p><?php echo nl2br(htmlspecialchars($theme['description'])); ?></p>
        </div>
    </div>

    <!-- Nhúng Footer -->
    <?php include 'template/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>