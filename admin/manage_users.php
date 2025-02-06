<?php
session_start();
include '../db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("🚫 Bạn không có quyền truy cập vào trang này!");
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param("ss", $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="main.css">

</head>

<body>

    <div class="admin-container d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <main class="content p-4">
            <h2 class="fw-bold">👥 Quản Lý Người Dùng</h2>
            <p class="text-muted">Xem danh sách user và quản lý tài khoản.</p>

            <!-- Tìm kiếm người dùng -->
            <form method="GET" class="mb-4 d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="🔍 Nhập username hoặc email..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>

            <!-- Danh sách người dùng -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Vai Trò</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($row['role'] === 'admin') ? 'danger' : 'secondary'; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $row['id']; ?>"
                                data-username="<?php echo $row['username']; ?>"
                                data-email="<?php echo $row['email']; ?>" data-role="<?php echo $row['role']; ?>">✏️
                                Sửa</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $row['id']; ?>">🗑️
                                Xóa</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Chỉnh Sửa -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Chỉnh Sửa Người Dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vai Trò</label>
                            <select name="role" id="role" class="form-select">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">✔️ Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Xử Lý -->
    <script>
    $(document).ready(function() {
        // Khi bấm vào nút "Sửa"
        $(".edit-btn").click(function() {
            $("#user_id").val($(this).data("id"));
            $("#username").val($(this).data("username"));
            $("#email").val($(this).data("email"));
            $("#role").val($(this).data("role"));
            $("#editModal").modal("show");
        });

        // Xử lý cập nhật người dùng bằng AJAX
        $("#editForm").submit(function(event) {
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "api/edit_user.php",
                data: $("#editForm").serialize(),
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function() {
                    alert("❌ Lỗi khi cập nhật!");
                }
            });
        });

        // Xử lý xóa người dùng
        $(".delete-btn").click(function() {
            if (confirm("Bạn có chắc chắn muốn xóa người dùng này?")) {
                var userId = $(this).data("id");
                $.post("api/delete_user.php", {
                    user_id: userId
                }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        });
    });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>