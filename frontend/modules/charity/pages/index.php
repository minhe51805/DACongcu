<?php
session_start();
require_once __DIR__ . '/../../../../backend/bootstrap.php';

// Helper function cho charity
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VND';
}

// Lấy thống kê cho trang charity
try {
    // Tổng số sự kiện
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM events WHERE status = 'active'");
    $total_events = $stmt->fetch()['total'] ?? 0;

    // Tổng số người tham gia
    $stmt = $pdo->query("SELECT SUM(quantity) as total FROM event_registrations");
    $total_participants = $stmt->fetch()['total'] ?? 0;

    // Tổng số tiền đã quyên góp
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'");
    $total_donated = $stmt->fetch()['total'] ?? 0;

    // Lấy sự kiện nổi bật với thông tin quyên góp
    $stmt = $pdo->query("
        SELECT e.*,
               COUNT(er.id) as registration_count,
               COALESCE(SUM(d.amount), 0) as current_amount,
               COALESCE(e.goal_amount, 0) as target_amount,
               COUNT(DISTINCT d.id) as supporters_count,
               CASE
                   WHEN e.goal_amount > 0 THEN ROUND((COALESCE(SUM(d.amount), 0) / e.goal_amount) * 100, 1)
                   ELSE 0
               END as progress_percentage
        FROM events e
        LEFT JOIN event_registrations er ON e.id = er.event_id
        LEFT JOIN donations d ON e.id = d.event_id AND d.status IN ('completed', 'confirmed')
        WHERE e.status = 'active' AND e.event_date >= CURDATE()
        GROUP BY e.id
        ORDER BY e.event_date ASC
        LIMIT 6
    ");
    $events = $stmt->fetchAll();

    // Lấy categories cho charity
    $charity_categories = [
        ['name' => 'Giáo dục', 'icon' => 'graduation-cap', 'desc' => 'Xây dựng trường học và hỗ trợ học bổng', 'count' => 0],
        ['name' => 'Y tế', 'icon' => 'heartbeat', 'desc' => 'Chăm sóc sức khỏe cộng đồng', 'count' => 0],
        ['name' => 'Cứu trợ', 'icon' => 'hands-helping', 'desc' => 'Hỗ trợ khẩn cấp thiên tai ứng cứu kịp thời', 'count' => 0],
        ['name' => 'Môi trường', 'icon' => 'leaf', 'desc' => 'Bảo vệ mô trường xanh sạch đẹp', 'count' => 0]
    ];

} catch (PDOException $e) {
    $total_events = $total_participants = $total_donated = 0;
    $events = [];
    $charity_categories = [];
}

$page_title = "Thiện Nguyện - XAYDUNGTUONGLAI";
$extra_css = ['charity-modern.css']; // Sử dụng CSS charity mới
include __DIR__ . '/../../../common/components/header.php';
?>

<!-- Modern Hero Section -->
<section class="charity-hero">
    <div class="container">
        <div class="charity-hero-content" data-animate="fadeInUp">
            <div class="charity-hero-badge">
                <i class="fas fa-heart"></i>
                <span>XAYDUNGTUONGLAI Charity</span>
            </div>
            <h1 class="charity-hero-title">
                Cùng nhau tạo nên<br>
                <span style="color: #ffd700;">Sự thay đổi tích cực</span>
            </h1>
            <p class="charity-hero-subtitle">
                Tham gia cùng chúng tôi trong hành trình mang lại những điều tốt đẹp cho xã hội.
                Mỗi đóng góp của bạn đều có ý nghĩa và tạo ra tác động tích cực.
            </p>

            <div class="charity-hero-stats">
                <div class="charity-stat">
                    <span class="charity-stat-number"><?= number_format($total_events) ?></span>
                    <span class="charity-stat-label">Dự án đang thực hiện</span>
                </div>
                <div class="charity-stat">
                    <span class="charity-stat-number"><?= number_format($total_participants) ?></span>
                    <span class="charity-stat-label">Người đã tham gia</span>
                </div>
                <div class="charity-stat">
                    <span class="charity-stat-number"><?= number_format($total_donated/1000000, 1) ?>M</span>
                    <span class="charity-stat-label">VNĐ đã quyên góp</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="charity-categories">
    <div class="container">
        <div class="section-header" data-animate="fadeInUp">
            <h2 class="section-title">Các lĩnh vực thiện nguyện</h2>
            <p class="section-subtitle">
                Khám phá những dự án thiện nguyện đang được thực hiện bởi XAYDUNGTUONGLAI
            </p>
        </div>

        <div class="categories-grid"><?php foreach ($charity_categories as $category): ?>
            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-<?= $category['icon'] ?>"></i>
                </div>
                <h3 class="category-title"><?= $category['name'] ?></h3>
                <p class="category-desc"><?= $category['desc'] ?></p>
                <span class="category-count"><?= $category['count'] ?> dự án</span>
                <a href="#" class="category-link">
                    Khám phá <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="category-title">Giáo dục</h3>
                <p class="category-desc">Hỗ trợ học bổng và cơ sở vật chất giáo dục</p>
                <span class="category-count"><?= number_format($total_events) ?> dự án</span>
            </div>

            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-medkit"></i>
                </div>
                <h3 class="category-title">Y tế</h3>
                <p class="category-desc">Chăm sóc sức khỏe cộng đồng và hỗ trợ y tế</p>
                <span class="category-count">15 dự án</span>
            </div>

            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="category-title">Nhà ở</h3>
                <p class="category-desc">Xây dựng nhà tình thương cho người nghèo</p>
                <span class="category-count">8 dự án</span>
            </div>

            <div class="category-card" data-animate="fadeInUp">
                <div class="category-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3 class="category-title">Môi trường</h3>
                <p class="category-desc">Bảo vệ môi trường và phát triển bền vững</p>
                <span class="category-count">12 dự án</span>
            </div>
        </div>
    </div>
</section> -->

<!-- Projects Section -->

<!-- Impact Statistics Section -->
<section class="charity-impact">
    <div class="container">
        <div class="section-header" data-animate="fadeInUp">
            <h2 class="section-title">Tác động tích cực của chúng ta</h2>
            <p class="section-subtitle">
                Những con số minh chứng cho sự thay đổi tích cực mà chúng ta đã tạo ra cho cộng đồng
            </p>
        </div>

        <!-- Main Statistics Grid -->
        <div class="impact-stats-grid">
            <div class="impact-stat-card" data-animate="fadeInUp">
                <div class="stat-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-count="<?= $total_events ?>"><?= number_format($total_events) ?></div>
                    <div class="stat-label">Dự án thiện nguyện</div>
                    <div class="stat-desc">Đang được thực hiện</div>
                </div>
            </div>

            <div class="impact-stat-card" data-animate="fadeInUp">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" data-count="<?= $total_participants ?>"><?= number_format($total_participants) ?></div>
                    <div class="stat-label">Tình nguyện viên</div>
                    <div class="stat-desc">Đã tham gia</div>
                </div>
            </div>

            <div class="impact-stat-card" data-animate="fadeInUp">
                <div class="stat-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">63</div>
                    <div class="stat-label">Tỉnh thành</div>
                    <div class="stat-desc">Có hoạt động</div>
                </div>
            </div>
        </div>

        <!-- Achievement Progress -->
        <div class="achievement-section" data-animate="fadeInUp">
            <div class="achievement-grid">
                <div class="achievement-card">
                    <div class="achievement-header">
                        <div class="achievement-icon">
                            <i class="fas fa-target"></i>
                        </div>
                        <div class="achievement-info">
                            <h4 class="achievement-title">Mục tiêu năm 2024</h4>
                            <p class="achievement-desc">Tiến độ thực hiện các dự án trong năm</p>
                        </div>
                    </div>
                    <div class="achievement-progress">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-new">
                                <div class="progress-fill-new" data-width="75%" style="width: 0%"></div>
                            </div>
                            <span class="progress-percent">75%</span>
                        </div>
                        <p class="progress-text">Đã hoàn thành 75% mục tiêu quyên góp trong năm</p>
                    </div>
                </div>

                <div class="achievement-card">
                    <div class="achievement-header">
                        <div class="achievement-icon">
                            <i class="fas fa-smile"></i>
                        </div>
                        <div class="achievement-info">
                            <h4 class="achievement-title">Độ hài lòng</h4>
                            <p class="achievement-desc">Phản hồi từ người thụ hưởng</p>
                        </div>
                    </div>
                    <div class="achievement-progress">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-new">
                                <div class="progress-fill-new" data-width="98%" style="width: 0%"></div>
                            </div>
                            <span class="progress-percent">98%</span>
                        </div>
                        <p class="progress-text">Tỷ lệ hài lòng của người thụ hưởng các dự án</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Events Section -->
<section class="charity-events">
    <div class="container">
        <div class="section-header" data-animate="fadeInUp">
            <h2 class="section-title">Sự kiện thiện nguyện</h2>
            <p class="section-subtitle">
                Tham gia cùng chúng tôi để mang lại những điều tốt đẹp cho cộng đồng
            </p>
        </div>

        <!-- Search and Filter -->
        <div class="events-controls" data-animate="fadeInUp">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Tìm kiếm sự kiện..." id="eventSearch">
            </div>

            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">Tất cả</button>
                <button class="filter-tab" data-filter="education">Giáo dục</button>
                <button class="filter-tab" data-filter="health">Y tế</button>
                <button class="filter-tab" data-filter="environment">Môi trường</button>
                <button class="filter-tab" data-filter="community">Cộng đồng</button>
            </div>
        </div>

        <?php if (!empty($events)): ?>
            <div class="events-grid">
                <?php foreach ($events as $index => $event): ?>
                    <div class="event-card" data-animate="fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s" data-category="charity">
                        <div class="event-image">
                            <img src="<?= $event['image'] ?? 'https://media-cdn-v2.laodong.vn/storage/newsportal/2020/10/21/847261/Images897720_DSC_008.jpg' ?>"
                                 alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
                            <div class="event-status">
                                <span class="status-badge active">Đang diễn ra</span>
                            </div>
                            <div class="event-actions">
                                <button class="action-btn favorite" title="Yêu thích">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="action-btn share" title="Chia sẻ">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="event-content">
                            <div class="event-meta">
                                <span class="event-date">
                                    <i class="fas fa-calendar"></i>
                                    <?= date('d/m/Y', strtotime($event['event_date'] ?? $event['created_at'])) ?>
                                </span>
                                <span class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($event['location'] ?? 'Toàn quốc') ?>
                                </span>
                            </div>

                            <h3 class="event-title">
                                <a href="event_detail.php?id=<?= $event['id'] ?>">
                                    <?= htmlspecialchars($event['title']) ?>
                                </a>
                            </h3>

                            <p class="event-description">
                                <?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...
                            </p>

                            <div class="event-stats">
                                <div class="stat-item">
                                    <i class="fas fa-users"></i>
                                    <span><?= number_format($event['registration_count'] ?? 0) ?> người tham gia</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <span><?= number_format($event['supporters_count'] ?? 0) ?> người ủng hộ</span>
                                </div>
                            </div>

                            <div class="event-footer">
                                <a href="event_detail.php?id=<?= $event['id'] ?>" class="btn-primary-event">
                                    <i class="fas fa-info-circle"></i>
                                    Chi tiết
                                </a>
                                <a href="#" onclick="handleEventRegister(<?= $event['id'] ?>)" class="btn-secondary-event">
                                    <i class="fas fa-heart"></i>
                                    Đăng ký ngay
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="events-footer" data-animate="fadeInUp">
                <a href="event_list.php" class="btn-view-all">
                    Xem tất cả sự kiện
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="empty-state" data-animate="fadeInUp">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="empty-title">Chưa có sự kiện nào</h3>
                <p class="empty-description">
                    Hiện tại chưa có sự kiện thiện nguyện nào. Vui lòng quay lại sau hoặc
                    <a href="donate.php" class="link-primary">quyên góp</a> để ủng hộ các hoạt động của chúng tôi.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How to Help Section -->
<section class="help-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Làm thế nào để giúp đỡ?</h2>
                <p class="lead">Có nhiều cách để bạn có thể góp phần vào các hoạt động thiện nguyện</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="text-center">
                    <div class="help-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-hands-helping fa-2x"></i>
                    </div>
                    <h4>Tham gia trực tiếp</h4>
                    <p>Đăng ký tham gia các sự kiện thiện nguyện và đóng góp sức lao động của bạn.</p>
                    <a href="event_list.php" class="btn btn-outline-primary">Xem sự kiện</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="text-center">
                    <div class="help-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-donate fa-2x"></i>
                    </div>
                    <h4>Quyên góp tiền</h4>
                    <p>Ủng hộ tài chính để chúng tôi có thể thực hiện nhiều hoạt động thiện nguyện hơn.</p>
                    <a href="donate.php" class="btn btn-outline-success">Quyên góp ngay</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="text-center">
                    <div class="help-icon bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-share-alt fa-2x"></i>
                    </div>
                    <h4>Chia sẻ</h4>
                    <p>Chia sẻ thông tin về các hoạt động thiện nguyện để nhiều người biết đến.</p>
                    <a href="#" class="btn btn-outline-info">Chia sẻ ngay</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">Có câu hỏi về hoạt động thiện nguyện?</h2>
                <p class="lead mb-4">
                    Chúng tôi luôn sẵn sàng giải đáp mọi thắc mắc của bạn về các hoạt động thiện nguyện.
                </p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                        <h5>Điện thoại</h5>
                        <p>0123 456 789</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                        <h5>Email</h5>
                        <p>charity@XAYDUNGTUONGLAI.com.vn</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                        <h5>Thời gian</h5>
                        <p>T2-T6: 8:00-17:00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern JavaScript -->
<script src="../assets/js/vintech-framework.js"></script>

<script>
// Initialize modern features for charity page
document.addEventListener('DOMContentLoaded', function() {
    // Simple animation on scroll
    const animationObserverOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const animationObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, animationObserverOptions);

    // Observe all elements with data-animate
    document.querySelectorAll('[data-animate]').forEach(el => {
        animationObserver.observe(el);
    });

    // Counter animation for stats
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);

        function updateCounter() {
            start += increment;
            if (start < target) {
                element.textContent = Math.floor(start).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString();
            }
        }
        updateCounter();
    }

    // Intersection Observer for counter animation
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const count = parseInt(element.getAttribute('data-count'));
                if (count) {
                    animateCounter(element, count);
                    counterObserver.unobserve(element);
                }
            }
        });
    }, observerOptions);

    // Observe all stat numbers
    document.querySelectorAll('[data-count]').forEach(el => {
        counterObserver.observe(el);
    });

    // Progress bar animation for new design
    const progressObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const progressFills = entry.target.querySelectorAll('.progress-fill-new');
                progressFills.forEach(progressFill => {
                    const width = progressFill.getAttribute('data-width');
                    if (width) {
                        setTimeout(() => {
                            progressFill.style.width = width;
                        }, 500);
                    }
                });
                progressObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.achievement-section').forEach(el => {
        progressObserver.observe(el);
    });

    // Events search and filter functionality
    const searchInput = document.getElementById('eventSearch');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const eventCards = document.querySelectorAll('.event-card');

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            eventCards.forEach(card => {
                const title = card.querySelector('.event-title').textContent.toLowerCase();
                const description = card.querySelector('.event-description').textContent.toLowerCase();
                const isVisible = title.includes(searchTerm) || description.includes(searchTerm);
                card.style.display = isVisible ? 'block' : 'none';
            });
        });
    }

    // Filter functionality
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filterValue = this.getAttribute('data-filter');

            eventCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                } else {
                    const category = card.getAttribute('data-category');
                    card.style.display = category === filterValue ? 'block' : 'none';
                }
            });
        });
    });

    // Favorite button functionality
    document.querySelectorAll('.favorite').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.style.color = '#e74c3c';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.style.color = '';
            }
        });
    });
});

// Handle event registration
function handleEventRegister(eventId) {
    <?php if (isset($_SESSION['user_id'])): ?>
        // User is logged in, go directly to registration page
        window.location.href = 'event_register.php?id=' + eventId;
    <?php else: ?>
        // User not logged in, go to login page with redirect
        const redirectUrl = encodeURIComponent(window.location.origin + '/bannop/public_html/charity/event_register.php?id=' + eventId);
        window.location.href = '../auth/login.php?redirect=' + redirectUrl;
    <?php endif; ?>
}

    // Smooth scroll for scroll indicator
    document.querySelector('.scroll-indicator')?.addEventListener('click', function() {
        document.querySelector('.modern-impact-section').scrollIntoView({
            behavior: 'smooth'
        });
    });

    // Button ripple effect
    document.querySelectorAll('.btn-modern').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = this.querySelector('.btn-ripple');
            if (ripple) {
                ripple.style.width = '0';
                ripple.style.height = '0';

                setTimeout(() => {
                    ripple.style.width = '300px';
                    ripple.style.height = '300px';
                }, 10);

                setTimeout(() => {
                    ripple.style.width = '0';
                    ripple.style.height = '0';
                }, 600);
            }
        });
    });

    // Enhanced card hover effects
    document.querySelectorAll('.impact-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Parallax effect for hero background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.hero-particles');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Dynamic floating animation for cards
    document.querySelectorAll('.floating-card').forEach((card, index) => {
        const delay = index * 2;
        card.style.animationDelay = `${delay}s`;

        // Add random movement
        setInterval(() => {
            const randomX = (Math.random() - 0.5) * 10;
            const randomY = (Math.random() - 0.5) * 10;
            card.style.transform = `translate(${randomX}px, ${randomY}px)`;
        }, 3000 + index * 1000);
    });

    // Add loading animation
    const loadingElements = document.querySelectorAll('[data-aos]');
    loadingElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';

        setTimeout(() => {
            el.style.transition = 'all 0.8s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100);
});

// Utility function for smooth animations
function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
}
</script>

<?php include __DIR__ . '/../../../common/components/footer.php'; ?>

