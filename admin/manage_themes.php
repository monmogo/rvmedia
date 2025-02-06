<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}
// Thêm Theme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_theme'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $file_url = $_POST['file_url'];
    $category = $_POST['category'];
    $views = $_POST['views'];
    $purchases = $_POST['purchases'];

    // Xử lý Upload Ảnh
    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_path = "uploads/default.png"; // Ảnh mặc định nếu không tải ảnh lên
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        $image_name = time() . "_" . basename($_FILES["image_file"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types) && move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
            $image_path = "../uploads/" . $image_name;
        } else {
            die("❌ Lỗi khi tải ảnh lên hoặc định dạng không hợp lệ!");
        }
    }

    $stmt = $conn->prepare("INSERT INTO themes (name, description, price, image_url, file_url, category, views, purchases) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsssii", $name, $description, $price, $image_path, $file_url, $category, $views, $purchases);
    $stmt->execute();
}

// Cập nhật lượt xem/lượt mua
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_theme'])) {
    $id = $_POST['theme_id'];
    $views = $_POST['views'];
    $purchases = $_POST['purchases'];

    $stmt = $conn->prepare("UPDATE themes SET views = ?, purchases = ? WHERE id = ?");
    $stmt->bind_param("iii", $views, $purchases, $id);
    $stmt->execute();

    echo "✅ Cập nhật thành công!";
    exit();
}

// Xóa Theme
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM themes WHERE id = $delete_id");
    header("Location: manage_themes.php");
    exit();
}

// Lấy danh sách theme
$result = $conn->query("SELECT * FROM themes");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Theme</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="main.css">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="container mt-4">
        <h2 class="fw-bold">📦 Quản Lý Theme</h2>

        <!-- Form Thêm Theme -->
        <form method="POST" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm mb-4">
            <h4>➕ Thêm Theme Mới</h4>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Tên theme" required>
                    <input type="text" name="category" class="form-control mb-2" placeholder="Danh mục" required>
                    <input type="number" name="price" class="form-control mb-2" placeholder="Giá" required>
                    <input type="number" name="views" class="form-control mb-2" placeholder="Lượt xem" value="0"
                        required>
                    <input type="number" name="purchases" class="form-control mb-2" placeholder="Lượt mua" value="0"
                        required>
                </div>
                <div class="col-md-6">
                    <input type="file" name="image_file" class="form-control mb-2" accept="image/*">
                    <input type="text" name="file_url" class="form-control mb-2" placeholder="URL file tải theme"
                        required>
                    <button type="submit" name="add_theme" class="btn btn-primary w-100">➕ Thêm Theme</button>
                </div>
                <div class="col-12">
                    <textarea name="description" class="form-control mb-2" placeholder="Mô tả theme"
                        required></textarea>
                </div>
            </div>
        </form>

        <!-- Danh sách Theme -->
        <table class="table table-bordered shadow-sm bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Lượt Xem</th>
                    <th>Lượt Mua</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo number_format($row['price'], 2); ?>$</td>
                    <td>
                        <input type="number" class="form-control form-control-sm update-input"
                            data-id="<?php echo $row['id']; ?>" data-field="views" value="<?php echo $row['views']; ?>">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm update-input"
                            data-id="<?php echo $row['id']; ?>" data-field="purchases"
                            value="<?php echo $row['purchases']; ?>">
                    </td>
                    <td><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Theme Image"></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                            data-name="<?php echo $row['name']; ?>" data-category="<?php echo $row['category']; ?>"
                            data-price="<?php echo $row['price']; ?>" data-image_url="<?php echo $row['image_url']; ?>"
                            data-file_url="<?php echo $row['file_url']; ?>"
                            data-description="<?php echo $row['description']; ?>">
                            ✏️ Sửa
                        </button>
                        <a href="manage_themes.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Bạn có chắc chắn muốn xóa theme này?')">🗑️ Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Gọi Modal -->
    <?php include '../api/edit_theme.php'; ?>

    <script>
    $(document).ready(function() {
        $(".edit-btn").click(function() {
            $("#theme_id").val($(this).data("id"));
            $("#name").val($(this).data("name"));
            $("#category").val($(this).data("category"));
            $("#price").val($(this).data("price"));
            $("#image_url").val($(this).data("image_url"));
            $("#file_url").val($(this).data("file_url"));
            $("#description").val($(this).data("description"));
            $("#editModal").modal("show");
        });
    });
    </script>
    <!-- <script>
    $(".update-input").change(function() {
        var id = $(this).data("id");
        var field = $(this).data("field");
        var value = $(this).val();

        $.post("manage_themes.php", {
            update_theme: 1,
            theme_id: id,
            [field]: value
        }, function(response) {
            alert(response);
        });
    });
    </script> -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>