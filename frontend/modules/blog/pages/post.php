<?php
require_once '../includes/helpers.php';

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? '';

// Support both slug and id for flexibility
if (empty($slug) && empty($id)) {
    header('Location: index.php');
    exit;
}

// Get post by slug or id
try {
    if (!empty($slug)) {
        $stmt = $pdo->prepare("
            SELECT bp.*, u.full_name as author_name, u.avatar as author_avatar
            FROM blog_posts bp
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE bp.slug = ? AND bp.status = 'published'
        ");
        $stmt->execute([$slug]);
    } else {
        $stmt = $pdo->prepare("
            SELECT bp.*, u.full_name as author_name, u.avatar as author_avatar
            FROM blog_posts bp
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE bp.id = ? AND bp.status = 'published'
        ");
        $stmt->execute([$id]);
    }
    
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        header('Location: index.php');
        exit;
    }
    
    // Update view count
    $stmt = $pdo->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post['id']]);
    $post['views']++; // Update local copy
    
    // Get related posts
    $stmt = $pdo->prepare("
        SELECT bp.*, u.full_name as author_name
        FROM blog_posts bp
        LEFT JOIN users u ON bp.author_id = u.id
        WHERE bp.status = 'published' AND bp.id != ?
        ORDER BY bp.published_at DESC
        LIMIT 3
    ");
    $stmt->execute([$post['id']]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Read actual data from blog_posts table columns
    // Check if the database has target_amount, current_amount, supporters_count columns
    $goal_amount = 0;
    $raised_amount = 0;
    $total_donors = 0;

    // Try to use target_amount, current_amount, supporters_count if they exist
    if (isset($post['target_amount'])) {
        $goal_amount = $post['target_amount'];
    } elseif (isset($post['budget'])) {
        $goal_amount = $post['budget'];
    } else {
        $goal_amount = 50000000; // Default fallback
    }

    if (isset($post['current_amount'])) {
        $raised_amount = $post['current_amount'];
    } elseif (isset($post['budget'])) {
        $raised_amount = $post['budget']; // For completed projects
    } else {
        $raised_amount = $goal_amount * 0.3; // Default fallback
    }

    if (isset($post['supporters_count'])) {
        $total_donors = $post['supporters_count'];
    } elseif (isset($post['donors'])) {
        $total_donors = $post['donors'];
    } else {
        $total_donors = 150; // Default fallback
    }

    // Calculate progress percentage
    $progress_percentage = $goal_amount > 0 ? round(($raised_amount / $goal_amount) * 100) : 0;
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

$page_title = htmlspecialchars($post['title']) . " - Blog VinTech";
include __DIR__ . '/../../../common/components/header.php';
?>

<style>
/* Modern VinTech Blog Post */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #2c3e50;
    background: #f8fafc;
    margin: 0;
    padding: 0;
}

.blog-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header Section */
.blog-header {
    background: linear-gradient(135deg, #2d5a27 0%, #4a7c59 100%);
    padding: 40px 0 60px;
    position: relative;
    overflow: hidden;
}

.blog-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.header-content {
    position: relative;
    z-index: 2;
}

.breadcrumb-nav {
    margin-bottom: 30px;
}

.breadcrumb-nav a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 14px;
    margin-right: 8px;
    transition: color 0.3s ease;
}

.breadcrumb-nav a:hover {
    color: white;
}

.breadcrumb-nav span {
    color: rgba(255,255,255,0.5);
    margin: 0 8px;
}

.post-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(255,255,255,0.15);
    border-radius: 20px;
    font-size: 13px;
    color: rgba(255,255,255,0.9);
    backdrop-filter: blur(10px);
}

.post-title {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1.2;
    color: white;
    margin-bottom: 16px;
    max-width: 800px;
}

.post-excerpt {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 0;
    max-width: 600px;
    line-height: 1.5;
}

/* Main Content */
.main-content {
    background: white;
    margin-top: -40px;
    position: relative;
    z-index: 3;
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

.content-wrapper {
    padding: 60px 0;
}

/* Blog Layout */
.blog-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
    align-items: start;
}

.article-column {
    max-width: 800px;
}

/* Featured Image */
.featured-image {
    margin-bottom: 40px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.featured-image img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
}

/* Article Meta */
.article-meta {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 40px;
    padding: 20px 0;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.author-details h6 {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.author-details span {
    font-size: 13px;
    color: #64748b;
}

.article-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.stat {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.stat i {
    color: #2d5a27;
}

/* Article Content */
.article-content {
    font-size: 16px;
    line-height: 1.8;
    color: #374151;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    color: #1f2937;
    font-weight: 700;
    margin-top: 2em;
    margin-bottom: 1em;
    line-height: 1.3;
}

.article-content h1 { font-size: 2.25em; }
.article-content h2 { font-size: 1.875em; }
.article-content h3 { font-size: 1.5em; }
.article-content h4 { font-size: 1.25em; }

.article-content p {
    margin-bottom: 1.5em;
}

.article-content blockquote {
    background: #f8fafc;
    border-left: 4px solid #2d5a27;
    padding: 20px 30px;
    margin: 30px 0;
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: #4a5568;
    position: relative;
}

.article-content blockquote::before {
    content: '"';
    font-size: 4em;
    color: #2d5a27;
    position: absolute;
    top: -10px;
    left: 15px;
    opacity: 0.3;
}

.article-content ul,
.article-content ol {
    margin: 20px 0;
    padding-left: 30px;
}

.article-content li {
    margin-bottom: 8px;
}

.article-content a {
    color: #2d5a27;
    text-decoration: none;
    font-weight: 500;
    border-bottom: 1px solid transparent;
    transition: all 0.3s ease;
}

.article-content a:hover {
    border-bottom-color: #2d5a27;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 30px 0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

/* Social Sharing */
.social-share {
    background: #f8fafc;
    padding: 30px;
    border-radius: 16px;
    margin: 40px 0;
    text-align: center;
}

.social-share h4 {
    margin-bottom: 20px;
    color: #1f2937;
    font-size: 18px;
}

.social-buttons {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.social-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.social-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.social-btn.facebook {
    background: #1877f2;
    color: white;
}

.social-btn.twitter {
    background: #1da1f2;
    color: white;
}

.social-btn.linkedin {
    background: #0077b5;
    color: white;
}

.social-btn.copy {
    background: white;
    color: #374151;
    border-color: #d1d5db;
}

.social-btn.copy:hover {
    background: #f9fafb;
}

/* Donation Section */
.donation-section {
    background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
    border: 2px solid #fecaca;
    border-radius: 20px;
    padding: 40px;
    margin: 50px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.donation-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="hearts" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M25,35 C25,25 15,25 15,35 C15,25 5,25 5,35 C5,45 25,55 25,55 C25,55 45,45 45,35 C45,25 35,25 35,35 C35,25 25,25 25,35 Z" fill="rgba(239,68,68,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23hearts)"/></svg>');
    opacity: 0.3;
}

.donation-header {
    position: relative;
    z-index: 2;
    margin-bottom: 30px;
}

.donation-header h3 {
    color: #dc2626;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
}

.donation-header p {
    color: #7f1d1d;
    font-size: 16px;
    max-width: 600px;
    margin: 0 auto;
}

.campaign-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 30px 0;
    position: relative;
    z-index: 2;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px 20px;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.1);
    border: 1px solid #fecaca;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #dc2626, #ef4444);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 20px;
}

.stat-info {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #dc2626;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #7f1d1d;
    font-weight: 500;
}

.progress-container {
    margin: 30px 0;
    position: relative;
    z-index: 2;
}

.progress-bar {
    width: 100%;
    height: 12px;
    background: #fecaca;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc2626, #ef4444, #f97316);
    border-radius: 10px;
    transition: width 1.5s ease;
    position: relative;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #7f1d1d;
    font-weight: 500;
}

.donation-amounts {
    margin: 30px 0;
    position: relative;
    z-index: 2;
}

.donation-amounts h4 {
    color: #dc2626;
    margin-bottom: 20px;
    font-size: 18px;
}

.amount-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.amount-btn {
    padding: 12px 20px;
    border: 2px solid #dc2626;
    background: white;
    color: #dc2626;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.amount-btn:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
}

.amount-btn.custom {
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
    border-color: #059669;
}

.amount-btn.custom:hover {
    background: linear-gradient(135deg, #047857, #059669);
}

.donation-actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.donate-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 30px;
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    transition: all 0.3s ease;
}

.donate-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
    color: white;
}

.donate-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 30px;
    background: white;
    color: #dc2626;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    border: 2px solid #dc2626;
    transition: all 0.3s ease;
}

.donate-btn-secondary:hover {
    background: #fef2f2;
    transform: translateY(-2px);
    color: #dc2626;
}

/* Author Section */
.author-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px;
    margin: 40px 0;
}

.author-card {
    display: flex;
    align-items: center;
    gap: 20px;
}

.author-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

.author-info h5 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 18px;
}

.author-info p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.5;
}

/* Old related posts styles removed - now using sidebar */

/* Sidebar Styles */
.sidebar-column {
    position: sticky;
    top: 20px;
}

.sidebar-widget {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.sidebar-widget h4 {
    color: #1f2937;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sidebar-widget h4 i {
    color: #2d5a27;
}

/* Related Posts Sidebar */
.related-posts-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.related-item {
    display: flex;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.related-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.related-image-small {
    flex-shrink: 0;
    width: 80px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.related-image-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-content-small {
    flex: 1;
}

.related-title-small {
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
}

.related-title-small a {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.3s ease;
}

.related-title-small a:hover {
    color: #2d5a27;
}

.related-meta-small {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
}

/* Newsletter Form */
.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.newsletter-input {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.newsletter-input:focus {
    outline: none;
    border-color: #2d5a27;
    box-shadow: 0 0 0 3px rgba(45, 90, 39, 0.1);
}

.newsletter-btn {
    padding: 12px 16px;
    background: linear-gradient(135deg, #2d5a27, #4a7c59);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.newsletter-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(45, 90, 39, 0.3);
}

/* Popular Posts */
.popular-posts {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.popular-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.popular-number {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #2d5a27, #4a7c59);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.popular-content {
    flex: 1;
}

.popular-content a {
    color: #1f2937;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    display: block;
    margin-bottom: 4px;
    transition: color 0.3s ease;
}

.popular-content a:hover {
    color: #2d5a27;
}

.popular-meta {
    font-size: 12px;
    color: #64748b;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .blog-layout {
        grid-template-columns: 1fr 300px;
        gap: 30px;
    }
}

@media (max-width: 768px) {
    .blog-container {
        padding: 0 16px;
    }

    .post-title {
        font-size: 2rem;
    }

    .blog-layout {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .sidebar-column {
        position: static;
        order: 2;
    }

    .article-column {
        order: 1;
    }
    
    .content-wrapper {
        padding: 40px 0;
    }
    
    .featured-image img {
        height: 250px;
    }
    
    .article-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .post-meta {
        flex-direction: column;
        gap: 10px;
    }

    .donation-section {
        padding: 30px 20px;
    }

    .campaign-stats {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .amount-buttons {
        flex-direction: column;
        align-items: center;
    }

    .amount-btn {
        width: 200px;
    }

    .donation-actions {
        flex-direction: column;
        align-items: center;
    }

    .donate-btn-primary,
    .donate-btn-secondary {
        width: 250px;
        justify-content: center;
    }

    .social-buttons {
        flex-direction: column;
        align-items: center;
    }

    .social-btn {
        width: 200px;
        justify-content: center;
    }

    .author-card {
        flex-direction: column;
        text-align: center;
    }

    .sidebar-widget {
        margin-bottom: 20px;
    }
}
</style>

<!-- Modern VinTech Blog Post -->
<div class="blog-header">
    <div class="blog-container">
        <div class="header-content">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-nav">
                <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
                <span>/</span>
                <a href="<?= BASE_URL ?>/blog/"><i class="fas fa-blog"></i> Blog</a>
                <span>/</span>
                <span><?= htmlspecialchars(substr($post['title'], 0, 30)) ?>...</span>
            </nav>
            
            <!-- Post Meta -->
            <div class="post-meta">
                <div class="meta-badge">
                    <i class="fas fa-calendar"></i>
                    <span><?= date('d/m/Y', strtotime($post['published_at'])) ?></span>
                </div>
                <div class="meta-badge">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($post['author_name']) ?></span>
                </div>
                <div class="meta-badge">
                    <i class="fas fa-eye"></i>
                    <span><?= number_format($post['views']) ?> lượt xem</span>
                </div>
                <div class="meta-badge">
                    <i class="fas fa-clock"></i>
                    <span><?= ceil(str_word_count($post['content']) / 200) ?> phút đọc</span>
                </div>
            </div>
            
            <!-- Post Title -->
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            
            <!-- Post Excerpt -->
            <?php if ($post['excerpt']): ?>
            <p class="post-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="content-wrapper">
        <div class="blog-container">
            <div class="blog-layout">
                <!-- Main Article Column -->
                <div class="article-column">
            <!-- Featured Image -->
            <?php if ($post['image']): ?>
            <div class="featured-image">
                <img src="<?= htmlspecialchars($post['image']) ?>"
                     alt="<?= htmlspecialchars($post['title']) ?>">
            </div>
            <?php endif; ?>

            <!-- Article Meta -->
            <div class="article-meta">
                <div class="author-info">
                    <img src="<?= $post['author_avatar'] ?? '../assets/images/default-avatar.jpg' ?>"
                         alt="<?= htmlspecialchars($post['author_name']) ?>"
                         class="author-avatar">
                    <div class="author-details">
                        <h6><?= htmlspecialchars($post['author_name']) ?></h6>
                        <span>Xuất bản vào <?= date('d/m/Y H:i', strtotime($post['published_at'])) ?></span>
                    </div>
                </div>

                <div class="article-stats">
                    <div class="stat">
                        <i class="fas fa-eye"></i>
                        <span><?= number_format($post['views']) ?> lượt xem</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-clock"></i>
                        <span><?= ceil(str_word_count($post['content']) / 200) ?> phút đọc</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-calendar"></i>
                        <span><?= date('d/m/Y', strtotime($post['published_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Article Content -->
            <div class="article-content">
                <?= $post['content'] ?>
            </div>

            <!-- Donation Call-to-Action -->
            <div class="donation-section">
                <div class="donation-header">
                    <h3><i class="fas fa-heart"></i> Ủng hộ hoạt động thiện nguyện</h3>
                    <p>Mỗi đóng góp của bạn đều có ý nghĩa và giúp chúng tôi tiếp tục các hoạt động thiện nguyện ý nghĩa.</p>
                </div>

                <div class="campaign-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($raised_amount) ?>đ</span>
                            <span class="stat-label">Đã quyên góp</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($goal_amount) ?>đ</span>
                            <span class="stat-label">Mục tiêu</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($total_donors) ?></span>
                            <span class="stat-label">Nhà hảo tâm</span>
                        </div>
                    </div>
                </div>

                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progress_percentage ?>%"></div>
                    </div>
                    <div class="progress-info">
                        <span class="progress-text"><?= $progress_percentage ?>% đã đạt được</span>
                        <span class="remaining-text">Còn <?= number_format($goal_amount - $raised_amount) ?>đ nữa</span>
                    </div>
                </div>

                <div class="donation-amounts">
                    <h4>Chọn số tiền ủng hộ:</h4>
                    <div class="amount-buttons">
                        <button class="amount-btn" data-amount="100000">100.000đ</button>
                        <button class="amount-btn" data-amount="200000">200.000đ</button>
                        <button class="amount-btn" data-amount="500000">500.000đ</button>
                        <button class="amount-btn" data-amount="1000000">1.000.000đ</button>
                        <button class="amount-btn custom" data-amount="custom">Số khác</button>
                    </div>
                </div>

                <div class="donation-actions">
                    <a href="<?= BASE_URL ?>/charity/donate.php" class="donate-btn-primary">
                        <i class="fas fa-heart"></i>
                        <span>Ủng hộ ngay</span>
                    </a>
                    <a href="<?= BASE_URL ?>/charity/" class="donate-btn-secondary">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>Xem các hoạt động khác</span>
                    </a>
                </div>
            </div>

            <!-- Social Sharing -->
            <div class="social-share">
                <h4><i class="fas fa-share-alt"></i> Chia sẻ bài viết</h4>
                <div class="social-buttons">
                    <a href="#" class="social-btn facebook" onclick="shareOnFacebook()">
                        <i class="fab fa-facebook-f"></i>
                        <span>Facebook</span>
                    </a>
                    <a href="#" class="social-btn twitter" onclick="shareOnTwitter()">
                        <i class="fab fa-twitter"></i>
                        <span>Twitter</span>
                    </a>
                    <a href="#" class="social-btn linkedin" onclick="shareOnLinkedIn()">
                        <i class="fab fa-linkedin-in"></i>
                        <span>LinkedIn</span>
                    </a>
                    <a href="#" class="social-btn copy" onclick="copyLink()">
                        <i class="fas fa-link"></i>
                        <span>Copy Link</span>
                    </a>
                </div>
            </div>

            <!-- Author Section -->
            <div class="author-section">
                <div class="author-card">
                    <img src="<?= $post['author_avatar'] ?? '../assets/images/default-avatar.jpg' ?>"
                         alt="<?= htmlspecialchars($post['author_name']) ?>"
                         class="author-avatar-large">
                    <div class="author-info">
                        <h5><?= htmlspecialchars($post['author_name']) ?></h5>
                        <p>Tác giả tại VinTech Blog, chuyên viết về công nghệ và các hoạt động thiện nguyện của cộng đồng.</p>
                    </div>
                </div>
            </div>

                    <!-- Back to Blog -->
                    <div class="text-center mt-5">
                        <a href="<?= BASE_URL ?>/blog/" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-arrow-left"></i> Quay lại Blog
                        </a>
                    </div>
                </div>

                <!-- Sidebar Column -->
                <div class="sidebar-column">
                    <!-- Related Posts Sidebar -->
                    <?php if (!empty($related_posts)): ?>
                    <div class="sidebar-widget">
                        <h4><i class="fas fa-newspaper"></i> Bài viết liên quan</h4>
                        <div class="related-posts-sidebar">
                            <?php foreach ($related_posts as $related): ?>
                            <div class="related-item">
                                <div class="related-image-small">
                                    <img src="<?= $related['image'] ?? '../assets/images/blog-default.jpg' ?>"
                                         alt="<?= htmlspecialchars($related['title']) ?>">
                                </div>
                                <div class="related-content-small">
                                    <h6 class="related-title-small">
                                        <a href="<?= BASE_URL ?>/blog/post-modern.php?slug=<?= $related['slug'] ?>">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h6>
                                    <div class="related-meta-small">
                                        <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($related['published_at'])) ?>
                                        <br>
                                        <i class="fas fa-eye"></i> <?= number_format($related['views']) ?> lượt xem
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Newsletter Signup -->
                    <div class="sidebar-widget">
                        <h4><i class="fas fa-envelope"></i> Đăng ký nhận tin</h4>
                        <p>Nhận thông báo về các bài viết mới và hoạt động thiện nguyện của VinTech.</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Email của bạn" class="newsletter-input">
                            <button type="submit" class="newsletter-btn">
                                <i class="fas fa-paper-plane"></i> Đăng ký
                            </button>
                        </form>
                    </div>

                    <!-- Popular Posts -->
                    <div class="sidebar-widget">
                        <h4><i class="fas fa-fire"></i> Bài viết phổ biến</h4>
                        <div class="popular-posts">
                            <div class="popular-item">
                                <span class="popular-number">1</span>
                                <div class="popular-content">
                                    <a href="#">Cách VinTech đang thay đổi cuộc sống cộng đồng</a>
                                    <div class="popular-meta">
                                        <i class="fas fa-eye"></i> 2,543 lượt xem
                                    </div>
                                </div>
                            </div>
                            <div class="popular-item">
                                <span class="popular-number">2</span>
                                <div class="popular-content">
                                    <a href="#">Những dự án thiện nguyện nổi bật 2024</a>
                                    <div class="popular-meta">
                                        <i class="fas fa-eye"></i> 1,876 lượt xem
                                    </div>
                                </div>
                            </div>
                            <div class="popular-item">
                                <span class="popular-number">3</span>
                                <div class="popular-content">
                                    <a href="#">Hướng dẫn tham gia hoạt động tình nguyện</a>
                                    <div class="popular-meta">
                                        <i class="fas fa-eye"></i> 1,234 lượt xem
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Social Sharing Functions
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
}

function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Show success message
        const btn = event.target.closest('.social-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> <span>Đã copy!</span>';
        btn.style.background = '#28a745';

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '#6c757d';
        }, 2000);
    });
}
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

