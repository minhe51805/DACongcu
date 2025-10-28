<?php
require_once "../includes/config.php";

// Get featured posts
$featured_sql = "
    SELECT bp.*, u.full_name as author_name 
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    WHERE bp.status = 'published' AND bp.featured = 1 
    ORDER BY bp.published_at DESC 
    LIMIT 3
";
$stmt = $pdo->query($featured_sql);
$featured_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get regular posts
$where_conditions = ["bp.status = 'published'"];
$params = [];
$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

$sql = "
    SELECT bp.*, u.full_name as author_name 
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    $where_clause 
    ORDER BY bp.published_at DESC 
    LIMIT 9 OFFSET 0
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total
$count_sql = "SELECT COUNT(*) FROM blog_posts bp WHERE bp.status = 'published'";
$stmt = $pdo->query($count_sql);
$total_posts = $stmt->fetchColumn();

$page_title = "Blog - CONVOI VinTech";
include "../includes/header.php";
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 text-success">📖 Blog CONVOI VinTech</h1>
        <p class="lead text-muted">Chia sẻ kiến thức công nghệ và hoạt động thiện nguyện</p>
    </div>
    
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> 
        <strong>Thành công!</strong> Tìm thấy <?= $total_posts ?> bài viết published
    </div>
    
    <?php if (!empty($featured_posts)): ?>
    <section class="mb-5">
        <h2 class="h3 mb-4">⭐ Bài viết nổi bật</h2>
        <div class="row">
            <?php foreach ($featured_posts as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-star"></i> Nổi bật
                    </div>
                    <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Blog image">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars(substr($post['content'], 0, 120)) ?>...</p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                            </small>
                            <?php if (isset($post['views'])): ?>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> <?= number_format($post['views']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if (!empty($posts)): ?>
    <section>
        <h2 class="h3 mb-4">📝 Tất cả bài viết</h2>
        <div class="row">
            <?php foreach ($posts as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Blog image">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...</p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                            </small>
                            <?php if (isset($post['views'])): ?>
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> <?= number_format($post['views']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($post['author_name']): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php else: ?>
    <div class="alert alert-info text-center">
        <h3>📝 Chưa có bài viết nào</h3>
        <p>Hãy quay lại sau để đọc những bài viết mới nhất!</p>
    </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
