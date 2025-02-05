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
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($theme['name']); ?> | Chi Tiết Theme</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/core.css">
</head>

<body>

    <!-- Thanh điều hướng -->
    <?php include 'template/header.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <!-- Ảnh Theme -->
            <div class="col-md-6">
                <?php
                $imagePath = trim($theme['image_url']);
                if (empty($imagePath)) {
                    $imagePath = 'assets/default.png';
                } elseif (!preg_match('/^(http|\/rvmedia2\/uploads\/)/', $imagePath)) {
                    $imagePath = "/rvmedia2/uploads/" . $imagePath;
                }
                ?>
                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="img-fluid rounded shadow-lg"
                    alt="<?php echo htmlspecialchars($theme['name']); ?>">
            </div>

            <!-- Thông Tin Theme -->
            <div class="col-md-6">
                <h1 class="fw-bold"><?php echo htmlspecialchars($theme['name']); ?></h1>
                <p class="fw-bold text-danger fs-4">💰 <?php echo number_format($theme['price'], 2); ?> USDT</p>

                <div class="d-flex gap-2">
                    <a href="<?php echo htmlspecialchars($theme['file_url']); ?>" target="_blank"
                        class="btn btn-outline-primary btn-lg">📥 Xem demo</a>
                    <a href="cart.php?id=<?php echo $theme['id']; ?>" class="btn btn-success btn-lg">🛒 Thêm vào giỏ</a>
                </div>

                <hr>

                <p class="text-muted">📅 Ngày tạo: <?php echo date('d/m/Y', strtotime($theme['created_at'])); ?></p>
                <p class="text-muted">📂 Danh mục: <strong><?php echo htmlspecialchars($theme['category']); ?></strong>
                </p>
            </div>
        </div>

        <!-- Mô Tả Theme -->
        <div class="mt-5">
            <h3 class="fw-bold">📖 Mô Tả Chi Tiết</h3>
            <div class="bg-white p-4 rounded shadow-sm">
                <p><?php echo nl2br(htmlspecialchars($theme['description'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'template/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>