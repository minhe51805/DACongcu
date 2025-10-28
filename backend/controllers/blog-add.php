<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $content = $_POST['content'];
    $excerpt = trim($_POST['excerpt']);
    $category = trim($_POST['category']);
    $status = $_POST['status'];
    $beneficiaries = (int)$_POST['beneficiaries'];
    $locations = (int)$_POST['locations'];
    $budget = (float)$_POST['budget'];
    $donors = (int)$_POST['donors'];
    $meta_title = trim($_POST['meta_title']);
    $meta_description = trim($_POST['meta_description']);
    
    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    // Handle file upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/blog/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                $featured_image = 'blog/' . $filename;
            } else {
                $error_message = "Lỗi khi upload ảnh!";
            }
        } else {
            $error_message = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)!";
        }
    }
    
    // Handle gallery images
    $gallery = [];
    if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
        for ($i = 0; $i < count($_FILES['gallery']['name']); $i++) {
            if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                $file_extension = strtolower(pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($file_extension, $allowed_extensions)) {
                    $filename = uniqid() . '_' . time() . '_' . $i . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $upload_path)) {
                        $gallery[] = 'blog/' . $filename;
                    }
                }
            }
        }
    }
    
    if (empty($error_message)) {
        try {
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            
            $stmt = $pdo->prepare("
                INSERT INTO blog_posts (
                    title, subtitle, slug, content, excerpt, featured_image, gallery,
                    category, author, author_id, status, beneficiaries, locations, 
                    budget, donors, meta_title, meta_description, published_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title, $subtitle, $slug, $content, $excerpt, $featured_image, 
                json_encode($gallery), $category, $_SESSION['username'], $_SESSION['user_id'],
                $status, $beneficiaries, $locations, $budget, $donors, 
                $meta_title, $meta_description, $published_at
            ]);
            
            $success_message = "Bài viết đã được tạo thành công!";
            
            // Reset form
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = "Lỗi khi tạo bài viết: " . $e->getMessage();
        }
    }
}

$page_title = "Thêm bài viết - Admin";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-section h4 {
            color: var(--admin-primary);
            border-bottom: 2px solid var(--admin-accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .gallery-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .gallery-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .btn-primary {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
        }
        
        .btn-primary:hover {
            background: var(--admin-secondary);
            border-color: var(--admin-secondary);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-plus me-2"></i>Thêm bài viết mới</h1>
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
            <a href="blog-manage.php"><i class="fas fa-newspaper me-2"></i>Quản lý Blog</a>
            <a href="blog-add.php" class="active"><i class="fas fa-plus me-2"></i>Thêm bài viết</a>
            <a href="users.php"><i class="fas fa-users me-2"></i>Quản lý Users</a>
        </div>
    </div>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-edit me-2"></i>Thông tin cơ bản</h4>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Phụ đề</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                   value="<?= htmlspecialchars($_POST['subtitle'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Tóm tắt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung *</label>
                            <textarea class="form-control" id="content" name="content" rows="15"><?= $_POST['content'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <!-- Images -->
                    <div class="form-section">
                        <h4><i class="fas fa-images me-2"></i>Hình ảnh</h4>
                        
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Ảnh đại diện *</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                   accept="image/*" onchange="previewImage(this, 'featured-preview')">
                            <div id="featured-preview"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gallery" class="form-label">Thư viện ảnh</label>
                            <input type="file" class="form-control" id="gallery" name="gallery[]" 
                                   accept="image/*" multiple onchange="previewGallery(this)">
                            <small class="text-muted">Có thể chọn nhiều ảnh</small>
                            <div id="gallery-preview" class="gallery-preview"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Publishing Options -->
                    <div class="form-section">
                        <h4><i class="fas fa-cog me-2"></i>Tùy chọn xuất bản</h4>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                                <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Xuất bản</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Danh mục</label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   value="<?= htmlspecialchars($_POST['category'] ?? 'Hoạt động thiện nguyện') ?>">
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="form-section">
                        <h4><i class="fas fa-chart-bar me-2"></i>Thống kê dự án</h4>
                        
                        <div class="mb-3">
                            <label for="beneficiaries" class="form-label">Người thụ hưởng</label>
                            <input type="number" class="form-control" id="beneficiaries" name="beneficiaries" 
                                   value="<?= $_POST['beneficiaries'] ?? 0 ?>" min="0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="locations" class="form-label">Số địa điểm</label>
                            <input type="number" class="form-control" id="locations" name="locations" 
                                   value="<?= $_POST['locations'] ?? 0 ?>" min="0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="budget" class="form-label">Kinh phí (VNĐ)</label>
                            <input type="number" class="form-control" id="budget" name="budget" 
                                   value="<?= $_POST['budget'] ?? 0 ?>" min="0" step="1000">
                        </div>
                        
                        <div class="mb-3">
                            <label for="donors" class="form-label">Số nhà hảo tâm</label>
                            <input type="number" class="form-control" id="donors" name="donors" 
                                   value="<?= $_POST['donors'] ?? 0 ?>" min="0">
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="form-section">
                        <h4><i class="fas fa-search me-2"></i>SEO</h4>
                        
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                   value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-section">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Lưu bài viết
                            </button>
                            <a href="blog-manage.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });

        // Preview featured image
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Preview gallery images
        function previewGallery(input) {
            const preview = document.getElementById('gallery-preview');
            preview.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }
    </script>
</body>
</html>


