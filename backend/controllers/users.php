<?php
require_once __DIR__ . '/../bootstrap.php';;
require_once __DIR__ . '/../bootstrap.php';
;

// Check if user is logged in and is admin
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if (!$auth->isAdmin()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$message = '';
$messageType = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? '';

    if ($action === 'update_status' && $userId) {
        $newStatus = $_POST['status'] ?? '';

    
    if ($action === 'update_status' && $userId) {
        $newStatus = $_POST['status'] ?? '';
        
        if (in_array($newStatus, ['active', 'pending', 'inactive'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $userId]);

                
                $message = 'Cập nhật trạng thái người dùng thành công!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Có lỗi xảy ra khi cập nhật trạng thái.';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'update_role' && $userId) {
        $newRole = $_POST['role'] ?? '';

        
        if (in_array($newRole, ['user', 'admin'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$newRole, $userId]);

                
                $message = 'Cập nhật vai trò người dùng thành công!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Có lỗi xảy ra khi cập nhật vai trò.';
                $messageType = 'error';
            }
        }
    }
}

// Get users with pagination
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$role_filter = $_GET['role'] ?? '';

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM users $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_users = $stmt->fetchColumn();

    
    // Get users
    $sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_pages = ceil($total_users / $limit);
    
    $total_pages = ceil($total_users / $limit);
    
} catch (Exception $e) {
    $users = [];
    $total_users = 0;
    $total_pages = 0;
}

$page_title = "Quản lý người dùng - CONVOI VinTech";
include __DIR__ . '/../../frontend/common/components/header.php';
?>

<div class="admin-users-page">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="page-title">
                        <i class="fas fa-users me-3"></i>
                        Quản lý người dùng
                    </h1>
                    <p class="page-subtitle">Tổng cộng: <?= number_format($total_users) ?> người dùng</p>
                </div>
                <div class="col-lg-6 text-end">
                    <a href="index.php" class="vintech-btn-enhanced vintech-btn-outline-enhanced">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>

        <!-- Message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="filters-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text"
                                name="search"
                                class="form-control"
                                placeholder="Tên, email, username..."
                                value="<?= htmlspecialchars($search) ?>">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Tên, email, username..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xác thực</option>
                                <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Vô hiệu hóa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Vai trò</label>
                            <select name="role" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Người dùng</option>
                                <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="vintech-btn-enhanced vintech-btn-primary-enhanced w-100">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="users-table-card">
            <div class="table-responsive">
                <table class="table users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tham gia</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <div class="user-info">
                                        <img src="<?= $user['avatar'] ?? '../assets/images/default-avatar.jpg' ?>"
                                            alt="Avatar" class="user-avatar">
                                        <div class="user-details">
                                            <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                            <div class="username">@<?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Người dùng</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="status" class="form-control form-control-sm status-select" onchange="this.form.submit()">
                                            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                            <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Chờ xác thực</option>
                                            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Vô hiệu hóa</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick="viewUser(<?= $user['id'] ?>)"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning"
                                            onclick="editUser(<?= $user['id'] ?>)"
                                            title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <div class="user-info">
                                    <img src="<?= $user['avatar'] ?? '../assets/images/default-avatar.jpg' ?>" 
                                         alt="Avatar" class="user-avatar">
                                    <div class="user-details">
                                        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                        <div class="username">@<?= htmlspecialchars($user['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Người dùng</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="status" class="form-control form-control-sm status-select" onchange="this.form.submit()">
                                        <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                                        <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>Chờ xác thực</option>
                                        <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Vô hiệu hóa</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewUser(<?= $user['id'] ?>)"
                                            title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" 
                                            onclick="editUser(<?= $user['id'] ?>)"
                                            title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="User pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <div class="pagination-wrapper">
                <nav aria-label="User pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .admin-users-page {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .page-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .page-title {
        color: var(--vintech-primary);
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        color: #6c757d;
        margin: 0;
    }

    .filters-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .users-table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .users-table {
        margin: 0;
    }

    .users-table th {
        background: rgba(111, 187, 107, 0.1);
        color: var(--vintech-primary);
        font-weight: 600;
        border: none;
        padding: 1rem;
    }

    .users-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--vintech-primary);
    }

    .user-name {
        font-weight: 600;
        color: var(--vintech-primary);
    }

    .username {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .inline-form {
        margin: 0;
    }

    .status-select {
        border-radius: 0.5rem;
        border: 1px solid #ddd;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .pagination-wrapper {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .pagination .page-link {
        border-radius: 0.5rem;
        margin: 0 0.25rem;
        border: 1px solid #ddd;
        color: var(--vintech-primary);
    }

    .pagination .page-item.active .page-link {
        background: var(--vintech-primary);
        border-color: var(--vintech-primary);
    }

    .alert {
        border-radius: 1rem;
        border: none;
        font-weight: 500;
    }

    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        color: #155724;
        border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }

    @media (max-width: 768px) {
        .admin-users-page {
            padding: 1rem 0;
        }

        .page-header {
            text-align: center;
        }

        .table-responsive {
            font-size: 0.9rem;
        }

        .user-info {
            flex-direction: column;
            text-align: center;
        }

        .action-buttons {
            justify-content: center;
        }
    }
</style>

<script>
    function viewUser(userId) {
        alert('Chức năng xem chi tiết người dùng sẽ được phát triển!');
    }

    function editUser(userId) {
        alert('Chức năng chỉnh sửa người dùng sẽ được phát triển!');
    }

    // Auto-submit forms with confirmation
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelects = document.querySelectorAll('.status-select');
        statusSelects.forEach(select => {
            select.addEventListener('change', function(e) {
                if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái người dùng này?')) {
                    this.form.submit();
                } else {
                    // Reset to original value
                    this.selectedIndex = 0;
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../frontend/common/components/footer.php'; ?>
.admin-users-page {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.page-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.page-title {
    color: var(--vintech-primary);
    font-weight: 700;
    margin: 0;
}

.page-subtitle {
    color: #6c757d;
    margin: 0;
}

.filters-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.users-table-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.users-table {
    margin: 0;
}

.users-table th {
    background: rgba(111, 187, 107, 0.1);
    color: var(--vintech-primary);
    font-weight: 600;
    border: none;
    padding: 1rem;
}

.users-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--vintech-primary);
}

.user-name {
    font-weight: 600;
    color: var(--vintech-primary);
}

.username {
    font-size: 0.8rem;
    color: #6c757d;
}

.inline-form {
    margin: 0;
}

.status-select {
    border-radius: 0.5rem;
    border: 1px solid #ddd;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.pagination-wrapper {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.pagination .page-link {
    border-radius: 0.5rem;
    margin: 0 0.25rem;
    border: 1px solid #ddd;
    color: var(--vintech-primary);
}

.pagination .page-item.active .page-link {
    background: var(--vintech-primary);
    border-color: var(--vintech-primary);
}

.alert {
    border-radius: 1rem;
    border: none;
    font-weight: 500;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border: 1px solid rgba(40, 167, 69, 0.2);
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    color: #721c24;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

@media (max-width: 768px) {
    .admin-users-page {
        padding: 1rem 0;
    }
    
    .page-header {
        text-align: center;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        justify-content: center;
    }
}
</style>

<script>
function viewUser(userId) {
    alert('Chức năng xem chi tiết người dùng sẽ được phát triển!');
}

function editUser(userId) {
    alert('Chức năng chỉnh sửa người dùng sẽ được phát triển!');
}

// Auto-submit forms with confirmation
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function(e) {
            if (confirm('Bạn có chắc chắn muốn thay đổi trạng thái người dùng này?')) {
                this.form.submit();
            } else {
                // Reset to original value
                this.selectedIndex = 0;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../frontend/common/components/footer.php'; ?>


