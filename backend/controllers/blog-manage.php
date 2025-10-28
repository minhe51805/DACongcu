<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success_message = "Bài viết đã được xóa thành công!";
    } catch (PDOException $e) {
        $error_message = "Lỗi khi xóa bài viết: " . $e->getMessage();
    }
}

// Handle status change
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $toggle_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT status FROM blog_posts WHERE id = ?");
        $stmt->execute([$toggle_id]);
        $current_status = $stmt->fetchColumn();
        
        $new_status = ($current_status === 'published') ? 'draft' : 'published';
        $published_at = ($new_status === 'published') ? date('Y-m-d H:i:s') : null;
        
        $stmt = $pdo->prepare("UPDATE blog_posts SET status = ?, published_at = ? WHERE id = ?");
        $stmt->execute([$new_status, $published_at, $toggle_id]);
        
        $success_message = "Trạng thái bài viết đã được cập nhật!";
    } catch (PDOException $e) {
        $error_message = "Lỗi khi cập nhật trạng thái: " . $e->getMessage();
    }
}

// Pagination
$posts_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $posts_per_page;

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Build query
    $where_clause = "WHERE 1=1";
    $params = [];
    
    if (!empty($status_filter)) {
        $where_clause .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts $where_clause");
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $posts_per_page);
    
    // Get posts
    $stmt = $pdo->prepare("
        SELECT id, title, category, status, view_count, author, created_at, published_at
        FROM blog_posts 
        $where_clause
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $params[] = $posts_per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $posts = [];
    $total_pages = 0;
}

$page_title = "Quản lý Blog - Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #2d5a27;
            --admin-secondary: #5a9a52;
            --admin-accent: #6fbb6b;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .admin-nav {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        
        .admin-nav a {
            color: var(--admin-primary);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: var(--admin-accent);
            color: white;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-archived {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-toggle {
            background: #28a745;
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-newspaper me-2"></i>Quản lý Blog</h1>
                </div>
                <div class="col-md-6 text-end">
                    <span>Xin chào, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="../auth/logout.php" class="btn btn-outline-light ms-3">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-nav">
        <div class="container">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            <a href="blog-manage.php" class="active"><i class="fas fa-newspaper me-2"></i>Quản lý Blog</a>
            <a href="blog-add.php"><i class="fas fa-plus me-2"></i>Thêm bài viết</a>
            <a href="users.php"><i class="fas fa-users me-2"></i>Quản lý Users</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <h5><i class="fas fa-newspaper me-2"></i>Tổng bài viết</h5>
                    <h2><?= $total_posts ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5><i class="fas fa-eye me-2"></i>Tổng lượt xem</h5>
                    <h2>
                        <?php
                        $view_stmt = $pdo->query("SELECT SUM(view_count) FROM blog_posts");
                        echo number_format($view_stmt->fetchColumn());
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5><i class="fas fa-check me-2"></i>Đã xuất bản</h5>
                    <h2>
                        <?php
                        $pub_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
                        echo $pub_stmt->fetchColumn();
                        ?>
                    </h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h5><i class="fas fa-edit me-2"></i>Bản nháp</h5>
                    <h2>
                        <?php
                        $draft_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'");
                        echo $draft_stmt->fetchColumn();
                        ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <form method="GET" class="row align-items-center">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published" <?= $status_filter === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Lọc
                    </button>
                    <a href="blog-manage.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Xóa bộ lọc
                    </a>
                </div>
                <div class="col-md-4 text-end">
                    <a href="blog-add.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Thêm bài viết mới
                    </a>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Tác giả</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Chưa có bài viết nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?= $post['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($post['title']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($post['category']) ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?= $post['status'] ?>">
                                                <?= ucfirst($post['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-eye me-1"></i><?= number_format($post['view_count']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($post['author']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></td>
                                        <td>
                                            <a href="blog-edit.php?id=<?= $post['id'] ?>" class="action-btn btn-edit" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=toggle_status&id=<?= $post['id'] ?>" 
                                               class="action-btn btn-toggle" 
                                               title="<?= $post['status'] === 'published' ? 'Ẩn bài viết' : 'Xuất bản' ?>"
                                               onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái?')">
                                                <i class="fas fa-<?= $post['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                                            </a>
                                            <a href="?action=delete&id=<?= $post['id'] ?>" 
                                               class="action-btn btn-delete" 
                                               title="Xóa"
                                               onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


