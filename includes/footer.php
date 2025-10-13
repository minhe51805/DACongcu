    </main>

    <!-- Footer VinTech -->
    <footer class="vintech-footer">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="footer-brand">
                        <div class="d-flex align-items-center mb-3">
                        </div>
                        <p class="footer-desc">
                            Tổ chức thiện nguyện phi lợi nhuận, hướng tới một cộng đồng tốt đẹp hơn. 
                            Chúng tôi tin rằng mỗi hành động nhỏ đều có thể tạo nên sự thay đổi lớn.
                        </p>
                        <div class="vintech-quote">
                            <div class="quote-mark"></div>
                            <small>"Kết nối yêu thương - Lan tỏa hy vọng"</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Liên hệ</h5>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <span>123 Đường ABC, Quận XYZ, TP.HCM</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <span>0123 456 789</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <span>info@xaydungtuonglai.com.vn</span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <span>T2-T6: 8:00 - 17:00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Kết nối với chúng tôi</h5>
                    <p class="footer-desc mb-4">Theo dõi các hoạt động và cập nhật mới nhất từ XAYDUNGTUONGLAI</p>
                    <div class="vintech-social-links">
                        <a href="#" class="vintech-social-link" data-tooltip="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="vintech-social-link" data-tooltip="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="vintech-social-link" data-tooltip="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="vintech-social-link" data-tooltip="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="vintech-divider"></div>
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="footer-copyright">&copy; <?= date('Y') ?> XAYDUNGTUONGLAI. Tất cả quyền được bảo lưu.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-powered">
                        <span>Được phát triển với</span>
                        <i class="fas fa-heart pulse"></i>
                        <span>tại Việt Nam</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- VinTech Decorative elements -->
        <div class="vintech-particles">
            <div class="particle particle-1"></div>
            <div class="particle particle-2"></div>
            <div class="particle particle-3"></div>
        </div>
    </footer>
    
    
    <style>
        .vintech-footer {
            background: linear-gradient(135deg, 
                var(--vintech-primary) 0%, 
                var(--vintech-secondary) 100%);
            color: white;
            padding: 80px 0 30px;
            position: relative;
            overflow: hidden;
        }

        .footer-brand .vintech-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--vintech-accent), var(--vintech-primary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .footer-brand .vintech-badge {
            font-size: 10px;
            background: linear-gradient(45deg, var(--vintech-accent), #6fbb6b);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--vintech-accent), transparent);
            border-radius: 2px;
        }

        .footer-desc {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .vintech-quote {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            border-left: 4px solid var(--vintech-accent);
            backdrop-filter: blur(10px);
        }

        .quote-mark {
            width: 4px;
            height: 30px;
            background: linear-gradient(to bottom, var(--vintech-accent), var(--vintech-primary));
            border-radius: 2px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
        }

        .contact-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--vintech-accent), var(--vintech-primary));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .contact-text span {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }

        .vintech-social-links {
            display: flex;
            gap: 12px;
        }

        .vintech-social-link {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .vintech-social-link::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--vintech-accent), var(--vintech-primary));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .vintech-social-link:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .vintech-social-link:hover::before {
            opacity: 1;
        }

        .vintech-social-link i {
            position: relative;
            z-index: 1;
        }

        .vintech-divider {
            height: 1px;
            background: linear-gradient(to right, 
                transparent, 
                rgba(255, 255, 255, 0.3), 
                transparent);
            margin: 50px 0 30px;
        }

        .footer-copyright {
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            font-weight: 500;
        }

        .footer-powered {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .footer-powered .pulse {
            color: var(--vintech-accent);
            animation: pulse 2s infinite;
        }

        .footer-powered .vintech-badge {
            font-size: 10px;
            background: linear-gradient(45deg, var(--vintech-accent), #6fbb6b);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .vintech-particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05), transparent);
            border-radius: 50%;
        }

        .particle-1 {
            top: 10%;
            right: 5%;
            animation: float 6s ease-in-out infinite;
        }

        .particle-2 {
            bottom: 15%;
            left: 8%;
            width: 80px;
            height: 80px;
            animation: float 8s ease-in-out infinite reverse;
        }

        .particle-3 {
            top: 50%;
            right: 15%;
            width: 60px;
            height: 60px;
            animation: float 10s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .vintech-footer {
                padding: 60px 0 30px;
            }
            
            .vintech-social-links {
                justify-content: center;
            }
            
            .footer-powered {
                justify-content: center;
                margin-top: 1rem;
            }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
