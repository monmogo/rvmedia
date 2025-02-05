<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

    // Xử lý Upload Ảnh
    if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = basename($_FILES["image_file"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("❌ Chỉ chấp nhận file JPG, JPEG, PNG, GIF!");
        }

        if (!move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
            die("❌ Lỗi khi tải ảnh lên!");
        }

        $stmt = $conn->prepare("INSERT INTO themes (name, description, price, image_url, file_url, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $name, $description, $price, $target_file, $file_url, $category);
        $stmt->execute();
    } else {
        die("❌ Không có file ảnh hoặc file bị lỗi!");
    }
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

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="admin.php">🛠️ Admin Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>📦 Quản Lý Theme</h2>

        <!-- Form Thêm Theme -->
        <form method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded mb-4">
            <h4>➕ Thêm Theme Mới</h4>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Tên theme" required>
                    <input type="text" name="category" class="form-control mb-2" placeholder="Danh mục" required>
                    <input type="number" name="price" class="form-control mb-2" placeholder="Giá" required>
                </div>
                <div class="col-md-6">
                    <input type="file" name="image_file" class="form-control mb-2" accept="image/*" required>
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
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Hình ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['price']; ?>$</td>
                    <td><img src="<?php echo $row['image_url']; ?>" width="50"></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

</body>

</html>