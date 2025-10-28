<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

try {
    // Get blog statistics
    $blog_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_posts,
            SUM(view_count) as total_views,
            COUNT(CASE WHEN status = 'published' THEN 1 END) as published_posts,
            COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_posts
        FROM blog_posts
    ")->fetch(PDO::FETCH_ASSOC);
    
    // Get recent posts
    $recent_posts = $pdo->query("
        SELECT id, title, status, view_count, created_at
        FROM blog_posts 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top viewed posts
    $top_posts = $pdo->query("
        SELECT id, title, view_count, category
        FROM blog_posts 
        WHERE status = 'published'
        ORDER BY view_count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $blog_stats = ['total_posts' => 0, 'total_views' => 0, 'published_posts' => 0, 'draft_posts' => 0];
    $recent_posts = [];
    $top_posts = [];
}

$page_title = "Dashboard - Admin";
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
        
        .stats-card {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .recent-posts-card,
        .top-posts-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .post-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .post-item:last-child {
            border-bottom: none;
        }
        
        .post-title {
            font-weight: 600;
            color: var(--admin-primary);
            text-decoration: none;
        }
        
        .post-title:hover {
            color: var(--admin-secondary);
        }
        
        .post-meta {
            font-size: 0.9rem;
            color: #666;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 10px;
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
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .action-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            background: var(--admin-accent);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: var(--admin-secondary);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
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
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
            <a href="blog-manage.php"><i class="fas fa-newspaper me-2"></i>Quản lý Blog</a>
            <a href="blog-add.php"><i class="fas fa-plus me-2"></i>Thêm bài viết</a>
            <a href="users.php"><i class="fas fa-users me-2"></i>Quản lý Users</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3><?= number_format($blog_stats['total_posts']) ?></h3>
                            <p><i class="fas fa-newspaper me-2"></i>Tổng bài viết</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3><?= number_format($blog_stats['total_views']) ?></h3>
                            <p><i class="fas fa-eye me-2"></i>Tổng lượt xem</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3><?= number_format($blog_stats['published_posts']) ?></h3>
                            <p><i class="fas fa-check me-2"></i>Đã xuất bản</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3><?= number_format($blog_stats['draft_posts']) ?></h3>
                            <p><i class="fas fa-edit me-2"></i>Bản nháp</p>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-edit"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Posts -->
            <div class="col-lg-6">
                <div class="recent-posts-card">
                    <h4 class="mb-4"><i class="fas fa-clock me-2"></i>Bài viết gần đây</h4>
                    
                    <?php if (empty($recent_posts)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có bài viết nào</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_posts as $post): ?>
                            <div class="post-item">
                                <div>
                                    <a href="blog-edit.php?id=<?= $post['id'] ?>" class="post-title">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                    <div class="post-meta">
                                        <span class="status-badge status-<?= $post['status'] ?>">
                                            <?= ucfirst($post['status']) ?>
                                        </span>
                                        <span class="ms-2">
                                            <i class="fas fa-eye me-1"></i><?= number_format($post['view_count']) ?>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="blog-manage.php" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Xem tất cả
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Viewed Posts -->
            <div class="col-lg-6">
                <div class="top-posts-card">
                    <h4 class="mb-4"><i class="fas fa-fire me-2"></i>Bài viết nổi bật</h4>
                    
                    <?php if (empty($top_posts)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có dữ liệu</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($top_posts as $index => $post): ?>
                            <div class="post-item">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-3"><?= $index + 1 ?></span>
                                    <div>
                                        <a href="../blog-detail.php?id=<?= $post['id'] ?>" class="post-title" target="_blank">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                        <div class="post-meta">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($post['category']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong class="text-primary">
                                        <i class="fas fa-eye me-1"></i><?= number_format($post['view_count']) ?>
                                    </strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-12">
                <div class="quick-actions">
                    <h4 class="mb-4"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="blog-add.php" class="action-btn">
                                <i class="fas fa-plus me-2"></i>Thêm bài viết mới
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="blog-manage.php" class="action-btn">
                                <i class="fas fa-list me-2"></i>Quản lý bài viết
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../blog/" class="action-btn" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>Xem blog công khai
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="../index.php" class="action-btn" target="_blank">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


