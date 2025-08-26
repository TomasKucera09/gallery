<?php
/**
 * PhotoGallery CMS - Homepage
 * 
 * Modern homepage with language support and configuration
 */

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/Database.php';

// Initialize language
$lang = $_GET['lang'] ?? APP_LANG;
$langFile = __DIR__ . "/lang/{$lang}.php";
if (!file_exists($langFile)) {
    $lang = 'en';
    $langFile = __DIR__ . "/lang/en.php";
}
$translations = include $langFile;

// Load appearance configuration
$appearance = include __DIR__ . '/config/appearance.php';

// Check if page is enabled
if (!$appearance['enabled_pages']['index']) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Get background image
$backgroundImage = $appearance['background_image'] ?? 'data/purple.webp';
$logoPath = $appearance['logo'] ?? 'data/logo.png';

// Function to translate text
function t($key, $params = []) {
    global $translations;
    $text = $translations[$key] ?? $key;
    
    foreach ($params as $param => $value) {
        $text = str_replace("{{$param}}", $value, $text);
    }
    
    return $text;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars(t('home_title')) ?> - <?= htmlspecialchars(t('app_name')) ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($appearance['favicon']) ?>" />
    
    <style>
        .font-signature {
            font-family: 'Great Vibes', cursive;
        }
        
        .font-primary {
            font-family: 'Inter', system-ui, sans-serif;
        }
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        .navbar-<?= $appearance['navbar_style'] ?> {
            <?php if ($appearance['navbar_style'] === 'transparent'): ?>
                background: <?= $appearance['navbar_background'] ?>;
                backdrop-filter: blur(10px);
            <?php elseif ($appearance['navbar_style'] === 'glass'): ?>
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            <?php else: ?>
                background: <?= $appearance['navbar_background'] ?>;
            <?php endif; ?>
        }
        
        .theme-<?= $appearance['theme'] ?> {
            <?php if ($appearance['theme'] === 'dark'): ?>
                --bg-color: #1f2937;
                --text-color: #f9fafb;
                --primary-color: <?= $appearance['primary_color'] ?>;
            <?php else: ?>
                --bg-color: #ffffff;
                --text-color: #1f2937;
                --primary-color: <?= $appearance['primary_color'] ?>;
            <?php endif; ?>
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }
        
        .slide-up {
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Custom CSS from configuration */
        <?= $appearance['custom_css'] ?>
    </style>
</head>

<body class="theme-<?= $appearance['theme'] ?> m-0 p-0 bg-black text-white relative font-primary">
    <!-- Header -->
    <header class="navbar-<?= $appearance['navbar_style'] ?> absolute top-0 left-0 w-full z-50 px-4 sm:px-6 py-4 flex justify-between items-center transition-all duration-300">
        <a href="index.php" class="select-none fade-in">
            <img src="<?= htmlspecialchars($logoPath) ?>" 
                 alt="<?= htmlspecialchars(t('app_name')) ?>" 
                 class="w-28 sm:w-36 md:w-44 object-contain h-auto"
                 style="max-width: <?= $appearance['logo_width'] ?>; max-height: <?= $appearance['logo_height'] ?>;" />
        </a>

        <!-- Language Switcher -->
        <div class="flex items-center space-x-4">
            <div class="flex space-x-2">
                <a href="?lang=cs" class="text-sm px-2 py-1 rounded <?= $lang === 'cs' ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' ?> transition-colors">
                    CZ
                </a>
                <a href="?lang=en" class="text-sm px-2 py-1 rounded <?= $lang === 'en' ? 'bg-white bg-opacity-20' : 'hover:bg-white hover:bg-opacity-10' ?> transition-colors">
                    EN
                </a>
            </div>

            <!-- Mobile menu button -->
            <button id="menu-toggle" class="md:hidden text-white text-2xl focus:outline-none hover:opacity-80 transition-opacity">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Desktop navigation -->
            <nav class="hidden md:flex space-x-6 text-sm sm:text-base font-light">
                <?php if ($appearance['enabled_pages']['photogallery']): ?>
                    <a class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>" href="photogallery.php">
                        <?= htmlspecialchars(t('nav_best_photos')) ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($appearance['enabled_pages']['gallery']): ?>
                    <a class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>" href="gallery.php">
                        <?= htmlspecialchars(t('nav_all_photos')) ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($appearance['enabled_pages']['about']): ?>
                    <a class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>" href="about.php">
                        <?= htmlspecialchars(t('nav_about')) ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($appearance['enabled_pages']['contact']): ?>
                    <a class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>" href="contact.php">
                        <?= htmlspecialchars(t('nav_contact')) ?>
                    </a>
                <?php endif; ?>
                
                <!-- Social Media Links -->
                <?php foreach ($appearance['social_links'] as $platform => $url): ?>
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" 
                           aria-label="<?= htmlspecialchars(t("social_{$platform}")) ?>" 
                           class="hover:text-<?= $appearance['primary_color'] ?> transition-colors duration-200">
                            <i class="fab fa-<?= $platform ?> text-lg"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden fixed inset-0 bg-black bg-opacity-95 flex flex-col items-center justify-center space-y-6 text-xl z-50">
        <button id="menu-close" class="absolute top-6 right-6 text-3xl hover:opacity-80 transition-opacity">
            <i class="fas fa-times"></i>
        </button>
        
        <nav class="flex flex-col items-center gap-6">
            <?php if ($appearance['enabled_pages']['photogallery']): ?>
                <a href="photogallery.php" class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>">
                    <?= htmlspecialchars(t('nav_best_photos')) ?>
                </a>
            <?php endif; ?>
            
            <?php if ($appearance['enabled_pages']['gallery']): ?>
                <a href="gallery.php" class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>">
                    <?= htmlspecialchars(t('nav_all_photos')) ?>
                </a>
            <?php endif; ?>
            
            <?php if ($appearance['enabled_pages']['about']): ?>
                <a href="about.php" class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>">
                    <?= htmlspecialchars(t('nav_about')) ?>
                </a>
            <?php endif; ?>
            
            <?php if ($appearance['enabled_pages']['contact']): ?>
                <a href="contact.php" class="hover:underline transition-all duration-200 hover:text-<?= $appearance['primary_color'] ?>">
                    <?= htmlspecialchars(t('nav_contact')) ?>
                </a>
            <?php endif; ?>
            
            <!-- Social Media Links in Mobile Menu -->
            <div class="flex space-x-6 mt-4">
                <?php foreach ($appearance['social_links'] as $platform => $url): ?>
                    <?php if ($url): ?>
                        <a href="<?= htmlspecialchars($url) ?>" 
                           aria-label="<?= htmlspecialchars(t("social_{$platform}")) ?>" 
                           class="hover:text-<?= $appearance['primary_color'] ?> transition-colors duration-200">
                            <i class="fab fa-<?= $platform ?> text-2xl"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </nav>
    </div>

    <!-- Full-page Background -->
    <section class="relative w-full h-full">
        <img src="<?= htmlspecialchars($backgroundImage) ?>"
             alt="<?= htmlspecialchars(t('home_subtitle')) ?>"
             class="absolute inset-0 w-full h-full object-cover z-0" />
        <div class="absolute inset-0 bg-black/30 z-10"></div>

        <div class="relative z-20 h-full flex flex-col justify-center items-start px-6 sm:px-10 md:px-20 lg:px-32 xl:px-40">
            <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-[#e0e0e0] leading-tight max-w-md slide-up">
                <?= htmlspecialchars(t('home_title')) ?>
            </h1>
            
            <p class="text-lg sm:text-xl md:text-2xl text-gray-300 mt-4 max-w-lg slide-up" style="animation-delay: 0.2s;">
                <?= htmlspecialchars(t('home_subtitle')) ?>
            </p>
            
            <?php if ($appearance['enabled_pages']['photogallery']): ?>
                <button onclick="window.location.href='photogallery.php'"
                        class="mt-6 w-36 sm:w-40 h-12 sm:h-14 border border-white rounded-full text-white font-medium text-base hover:bg-white hover:text-black transition-all duration-300 transform hover:scale-105 slide-up"
                        style="animation-delay: 0.4s;">
                    <?= htmlspecialchars(t('home_gallery_button')) ?>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <!-- GDPR Cookie Notice -->
    <div id="cookie-notice" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50 transform translate-y-full transition-transform duration-300">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex-1">
                <p class="text-sm">
                    <?= htmlspecialchars(t('gdpr_cookie_notice')) ?>
                    <a href="privacy.php" class="underline hover:no-underline">
                        <?= htmlspecialchars(t('gdpr_cookie_more_info')) ?>
                    </a>
                </p>
            </div>
            <div class="flex space-x-2">
                <button id="cookie-accept" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    <?= htmlspecialchars(t('gdpr_cookie_accept')) ?>
                </button>
                <button id="cookie-decline" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
                    <?= htmlspecialchars(t('gdpr_cookie_decline')) ?>
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuClose = document.getElementById('menu-close');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        menuClose.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            document.body.style.overflow = '';
        });

        // Close mobile menu when clicking outside
        mobileMenu.addEventListener('click', (e) => {
            if (e.target === mobileMenu) {
                mobileMenu.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });

        // Cookie notice functionality
        const cookieNotice = document.getElementById('cookie-notice');
        const cookieAccept = document.getElementById('cookie-accept');
        const cookieDecline = document.getElementById('cookie-decline');

        // Check if user has already made a choice
        if (!localStorage.getItem('cookieChoice')) {
            setTimeout(() => {
                cookieNotice.classList.remove('translate-y-full');
            }, 1000);
        }

        cookieAccept.addEventListener('click', () => {
            localStorage.setItem('cookieChoice', 'accepted');
            cookieNotice.classList.add('translate-y-full');
        });

        cookieDecline.addEventListener('click', () => {
            localStorage.setItem('cookieChoice', 'declined');
            cookieNotice.classList.add('translate-y-full');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('bg-black', 'bg-opacity-90');
            } else {
                header.classList.remove('bg-black', 'bg-opacity-90');
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.slide-up, .fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
