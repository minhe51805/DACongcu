<?php
require_once __DIR__ . '/../../../../backend/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Blog Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-5">🧪 Simple Blog Test</h1>
        
        <?php
        try {
            // Test database connection
            echo "<div class='alert alert-info'>";
            echo "<h4>🔍 Database Test</h4>";
            $test = $pdo->query("SELECT 1")->fetchColumn();
            echo "<p>✅ Database connection: OK</p>";
            echo "</div>";
            
            // Get posts with simple query
            $sql = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC";
            echo "<div class='alert alert-secondary'>";
            echo "<h4>📋 SQL Query</h4>";
            echo "<code>$sql</code>";
            echo "</div>";
            
            $stmt = $pdo->query($sql);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='alert alert-" . (count($posts) > 0 ? 'success' : 'danger') . "'>";
            echo "<h4>📊 Query Result</h4>";
            echo "<p><strong>Posts found:</strong> " . count($posts) . "</p>";
            echo "</div>";
            
            if (count($posts) > 0) {
                echo "<div class='row'>";
                foreach ($posts as $index => $post) {
                    echo "<div class='col-md-6 mb-4'>";
                    echo "<div class='card h-100'>";
                    
                    // Image
                    if (!empty($post['image'])) {
                        echo "<img src='{$post['image']}' class='card-img-top' style='height: 200px; object-fit: cover;' alt='Blog image'>";
                    }
                    
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>" . htmlspecialchars($post['title']) . "</h5>";
                    
                    // Excerpt or content preview
                    $content_preview = !empty($post['excerpt']) ? $post['excerpt'] : substr($post['content'], 0, 150) . '...';
                    echo "<p class='card-text'>" . htmlspecialchars($content_preview) . "</p>";
                    
                    echo "<div class='card-footer bg-transparent'>";
                    echo "<small class='text-muted'>";
                    echo "<i class='fas fa-calendar'></i> " . date('d/m/Y', strtotime($post['created_at']));
                    if (isset($post['views'])) {
                        echo " | <i class='fas fa-eye'></i> " . number_format($post['views']);
                    }
                    if (isset($post['featured']) && $post['featured']) {
                        echo " | <i class='fas fa-star text-warning'></i> Nổi bật";
                    }
                    echo "</small>";
                    echo "</div>";
                    
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                echo "</div>";
                
                echo "<div class='alert alert-success mt-4'>";
                echo "<h4>🎉 SUCCESS!</h4>";
                echo "<p>Blog data is working perfectly! The issue might be in the main blog page layout or CSS.</p>";
                echo "</div>";
                
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<h4>⚠️ No Posts Found</h4>";
                echo "<p>Let's add some test data...</p>";
                echo "</div>";
                
                // Add test data
                $test_posts = [
                    [
                        'title' => 'Test Post 1',
                        'slug' => 'test-post-1',
                        'content' => 'This is test content for post 1. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        'excerpt' => 'This is test excerpt for post 1.',
                        'status' => 'published',
                        'featured' => 1,
                        'views' => 100
                    ],
                    [
                        'title' => 'Test Post 2', 
                        'slug' => 'test-post-2',
                        'content' => 'This is test content for post 2. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        'excerpt' => 'This is test excerpt for post 2.',
                        'status' => 'published',
                        'featured' => 0,
                        'views' => 75
                    ]
                ];
                
                $insert_sql = "INSERT INTO blog_posts (title, slug, content, excerpt, status, featured, views, created_at, updated_at, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())";
                $stmt = $pdo->prepare($insert_sql);
                
                foreach ($test_posts as $post) {
                    $stmt->execute([
                        $post['title'],
                        $post['slug'], 
                        $post['content'],
                        $post['excerpt'],
                        $post['status'],
                        $post['featured'],
                        $post['views']
                    ]);
                }
                
                echo "<div class='alert alert-success'>";
                echo "<h4>✅ Test Data Added</h4>";
                echo "<p>Added " . count($test_posts) . " test posts. <a href='simple.php' class='btn btn-primary btn-sm'>Refresh Page</a></p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h4>❌ Error</h4>";
            echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
            echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
            echo "</div>";
        }
        ?>
        
        <hr class="my-5">
        
        <div class="text-center">
            <h4>🔗 Test Links</h4>
            <a href="../blog/" class="btn btn-primary me-2">📖 Main Blog</a>
            <a href="../blog/?debug=1" class="btn btn-info me-2">🔍 Debug Mode</a>
            <a href="../blog_debug_simple.php" class="btn btn-secondary me-2">🧪 Debug Script</a>
            <a href="../index.php" class="btn btn-success">🏠 Home</a>
        </div>
        
        <div class="mt-4 p-3 bg-light rounded">
            <h5>📋 Diagnosis Steps:</h5>
            <ol>
                <li><strong>If this page shows posts:</strong> Data is OK, main blog has layout/CSS issue</li>
                <li><strong>If this page is empty:</strong> Database/query issue</li>
                <li><strong>If error shown:</strong> Configuration or PHP issue</li>
            </ol>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

