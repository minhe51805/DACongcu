<?php
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Enable debug mode
$debug = isset($_GET['debug']) ? true : false;

if ($debug) {
    echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px; border-radius: 10px; border: 2px solid #007bff;'>";
    echo "<h3>🔍 DEBUG MODE</h3>";
}

// Get blog posts
$page = $_GET['page'] ?? 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$where_conditions = ["bp.status = 'published'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

if ($debug) {
    echo "<p><strong>Where clause:</strong> $where_clause</p>";
    echo "<p><strong>Parameters:</strong> " . json_encode($params) . "</p>";
}

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM blog_posts bp $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_posts = $stmt->fetchColumn();

    if ($debug) {
        echo "<p><strong>Total posts found:</strong> $total_posts</p>";
    }

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

    if ($debug) {
        echo "<p><strong>Featured posts found:</strong> " . count($featured_posts) . "</p>";
    }

    // Get regular posts with project-like data
    $sql = "
        SELECT bp.*, u.full_name as author_name,
               COALESCE(bp.target_amount, 0) as target_amount,
               COALESCE(bp.current_amount, 0) as current_amount,
               COALESCE(bp.supporters_count, 0) as supporters_count,
               CASE
                   WHEN bp.target_amount > 0 THEN ROUND((bp.current_amount / bp.target_amount) * 100, 1)
                   ELSE 0
               END as progress_percentage
        FROM blog_posts bp
        LEFT JOIN users u ON bp.author_id = u.id
        $where_clause
        ORDER BY bp.published_at DESC
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($debug) {
        echo "<p><strong>Regular posts found:</strong> " . count($posts) . "</p>";
        if (!empty($posts)) {
            echo "<p><strong>First post title:</strong> " . htmlspecialchars($posts[0]['title']) . "</p>";
        }
    }

    $total_pages = ceil($total_posts / $limit);

} catch (Exception $e) {
    $error_msg = "Database error - " . $e->getMessage();
    if ($debug) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>❌ Error:</h4>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>SQL:</strong> " . ($count_sql ?? 'N/A') . "</p>";
        echo "<p><strong>Params:</strong> " . json_encode($params) . "</p>";
        echo "</div>";
    } else {
        echo "<!-- Debug: $error_msg -->";
        echo "<!-- Debug SQL: " . ($count_sql ?? 'N/A') . " -->";
        echo "<!-- Debug Params: " . json_encode($params) . " -->";
    }
    $featured_posts = [];
    $posts = [];
    $total_posts = 0;
    $total_pages = 0;
}

if ($debug) {
    echo "<p><strong>Final result:</strong></p>";
    echo "<ul>";
    echo "<li>Total posts: $total_posts</li>";
    echo "<li>Featured posts: " . count($featured_posts) . "</li>";
    echo "<li>Regular posts: " . count($posts) . "</li>";
    echo "<li>Total pages: $total_pages</li>";
    echo "</ul>";
    echo "</div>";
}

$page_title = "Blog - CONVOI VinTech";



include __DIR__ . '/../../../common/components/header.php';
?>

<!-- Modern Blog CSS -->
<link rel="stylesheet" href="../assets/css/blog-new.css">
<link rel="stylesheet" href="../assets/css/vintech-toast.css">

<div class="blog-page">
    <!-- Modern Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="blog-hero-content" data-animate="fadeInUp">
                <div class="hero-badge">
                    <i class="fas fa-blog"></i>
                    <span>XAYDUNGTUONGLAI Blog</span>
                </div>

                <h1 class="blog-hero-title">
                    Khám phá tương lai
                    <span class="gradient-text">Công nghệ & Thiện nguyện</span>
                </h1>

                <p class="blog-hero-description">
                    Nơi chia sẻ những câu chuyện truyền cảm hứng, kiến thức công nghệ tiên tiến
                    và những hoạt động thiện nguyện ý nghĩa từ cộng đồng XAYDUNGTUONGLAI
                </p>

                <!-- Search -->
                <div class="hero-search" data-animate="fadeInUp">
                    <form method="GET" class="search-form">
                        <div class="search-container">
                            <input type="text"
                                   name="search"
                                   class="search-input"
                                   placeholder="Tìm kiếm bài viết, chủ đề, tác giả..."
                                   value="<?= htmlspecialchars($search) ?>"
                                   autocomplete="off">
                            <button type="submit" class="search-btn">
                                <span>Tìm kiếm</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Stats -->
                <div class="hero-stats" data-animate="fadeInUp">
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($total_posts) ?></div>
                        <div class="stat-label">Bài viết</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">1.2K+</div>
                        <div class="stat-label">Độc giả</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24</div>
                        <div class="stat-label">Chủ đề</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="blog-categories">
        <div class="container">
            <div class="section-header" data-animate="fadeInUp">
                <h2 class="section-title">Khám phá theo chủ đề</h2>
                <p class="section-subtitle">
                    Tìm hiểu những bài viết được phân loại theo từng lĩnh vực chuyên biệt
                </p>
            </div>

            <div class="categories-grid">
                <div class="category-card" data-animate="fadeInUp" onclick="filterPosts('')">
                    <div class="category-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <h3 class="category-title">Tất cả bài viết</h3>
                    <p class="category-desc">Xem toàn bộ bài viết từ mọi chủ đề</p>
                    <span class="category-count"><?= number_format($total_posts) ?> bài viết</span>
                </div>

                <div class="category-card" data-animate="fadeInUp" onclick="filterPosts('technology')">
                    <div class="category-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="category-title">Công nghệ</h3>
                    <p class="category-desc">Xu hướng và đổi mới công nghệ mới nhất</p>
                    <span class="category-count">24 bài viết</span>
                </div>

                <div class="category-card" data-animate="fadeInUp" onclick="filterPosts('charity')">
                    <div class="category-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="category-title">Thiện nguyện</h3>
                    <p class="category-desc">Những hoạt động ý nghĩa vì cộng đồng</p>
                    <span class="category-count">12 bài viết</span>
                </div>

                <div class="category-card" data-animate="fadeInUp" onclick="filterPosts('news')">
                    <div class="category-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3 class="category-title">Tin tức</h3>
                    <p class="category-desc">Cập nhật tin tức mới nhất từ VinTech</p>
                    <span class="category-count">18 bài viết</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Posts Section -->
    <section class="blog-posts">
        <div class="container">
            <div class="section-header" data-animate="fadeInUp">
                <h2 class="section-title">
                    <?php if (!empty($search)): ?>
                        Kết quả tìm kiếm
                    <?php else: ?>
                        Bài viết mới nhất
                    <?php endif; ?>
                </h2>
                <p class="section-subtitle">
                    <?php if (!empty($search)): ?>
                        Kết quả cho từ khóa: "<strong><?= htmlspecialchars($search) ?></strong>" - <?= number_format($total_posts) ?> bài viết
                    <?php else: ?>
                        Khám phá những bài viết mới nhất và cập nhật từ cộng đồng XAYDUNGTUONGLAI
                    <?php endif; ?>
                </p>
            </div>

        <?php if (empty($posts)): ?>
        <!-- Modern Empty State -->
        <div class="modern-empty-state" data-animate="fadeInUp">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="empty-title">
                <?php if (!empty($search)): ?>
                    Không tìm thấy bài viết nào
                <?php else: ?>
                    Chưa có bài viết nào
                <?php endif; ?>
            </h3>
            <p class="empty-desc">
                <?php if (!empty($search)): ?>
                    Thử tìm kiếm với từ khóa khác hoặc xem tất cả bài viết
                <?php else: ?>
                    Hãy quay lại sau để đọc những bài viết mới nhất từ CONVOI VinTech
                <?php endif; ?>
            </p>
            <div class="empty-actions">
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="modern-btn modern-btn-primary">
                        <i class="fas fa-list me-2"></i>Xem tất cả bài viết
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>" class="modern-btn modern-btn-outline">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
            </div>
        </div>
        <?php else: ?>
        
            <div class="posts-grid">
                <?php foreach ($posts as $index => $post): ?>
                <article class="post-card" data-animate="fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="post-image">
                        <img src="<?= $post['image'] ?? 'https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?w=800&h=400&fit=crop&crop=center' ?>"
                             alt="<?= htmlspecialchars($post['title'] ?? 'Blog post') ?>"
                             loading="lazy">
                        <div class="post-overlay">
                            <span class="post-category">BLOG</span>
                            <div class="post-favorite">
                                <i class="far fa-heart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="post-content">
                        <div class="post-meta">
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span><?= date('d/m/Y', strtotime($post['published_at'])) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?></span>
                            </div>
                        </div>

                        <h3 class="post-title">
                            <a href="post.php?slug=<?= $post['slug'] ?>">
                                <?= htmlspecialchars($post['title'] ?? 'Untitled') ?>
                            </a>
                        </h3>

                        <p class="post-excerpt">
                            <?= htmlspecialchars(substr(($post['excerpt'] ?? $post['content'] ?? ''), 0, 120)) ?>...
                        </p>

                        <!-- Project Progress Section -->
                        <?php if (isset($post['target_amount']) && $post['target_amount'] > 0): ?>
                        <div class="project-progress">
                            <div class="progress-stats">
                                <div class="progress-stat-item">
                                    <div class="progress-stat-label">Đã huy động</div>
                                    <div class="progress-stat-value"><?= number_format($post['current_amount'] ?? 0) ?>đ</div>
                                </div>
                                <div class="progress-stat-item">
                                    <div class="progress-stat-label">Mục tiêu</div>
                                    <div class="progress-stat-value"><?= number_format($post['target_amount']) ?>đ</div>
                                </div>
                            </div>

                            <div class="progress-bar-container">
                                <div class="progress-bar">
                                    <?php
                                    $progress = $post['target_amount'] > 0 ? min(($post['current_amount'] ?? 0) / $post['target_amount'] * 100, 100) : 0;
                                    ?>
                                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                </div>
                                <span class="progress-percentage"><?= number_format($progress, 1) ?>%</span>
                            </div>

                            <div class="project-supporters">
                                <i class="fas fa-users"></i>
                                <span><?= number_format($post['supporters_count'] ?? 0) ?> người ủng hộ</span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="post-footer">
                            <a href="post.php?slug=<?= $post['slug'] ?>" class="read-more">
                                <span>Đọc thêm</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            <div class="post-views">
                                <i class="fas fa-eye"></i>
                                <span><?= number_format($post['views'] ?? 0) ?></span>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
        <!-- Modern Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="modern-pagination" data-animate="fadeInUp">
            <nav aria-label="Blog pagination">
                <ul class="pagination-modern">
                    <?php if ($page > 1): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item-modern <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link-modern" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item-modern">
                        <a class="page-link-modern" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        </div>
    </section>
</div>

<script>
// Modern Blog Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Animation observer
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    // Observe animated elements
    document.querySelectorAll('[data-animate]').forEach(el => {
        observer.observe(el);
    });

    // Category filter
    window.filterPosts = function(category) {
        const url = new URL(window.location);
        if (category) {
            url.searchParams.set('category', category);
        } else {
            url.searchParams.delete('category');
        }
        window.location.href = url.toString();
    };
});
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

