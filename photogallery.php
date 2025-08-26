<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tomas Kucera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet" />
    <style>
        .font-signature {
            font-family: "Great Vibes", cursive;
        }

        .fullscreen-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .fullscreen-slider img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-300 min-h-screen flex flex-col overflow-x-hidden">
<header class="sticky top-0 z-50 bg-gray-900 bg-opacity-80 backdrop-blur-sm shadow-md flex justify-between items-center px-6 py-4">
    <a href="index.html">
        <img src="data/logo.png" alt="Logo" class="h-10 w-auto">
    </a>
    <button id="menu-toggle" class="md:hidden text-white text-2xl focus:outline-none">
        <i class="fas fa-bars"></i>
    </button>
    <nav class="hidden md:flex space-x-6 text-sm sm:text-base font-light">
        <a class="hover:underline" href="/music/">Music</a>
        <a class="hover:underline" href="photogallery.php">Best Photos</a>
        <a class="hover:underline" href="gallery.php">All Photos</a>
        <a class="hover:underline" href="https://apps.sincore.eu">Apps</a>
        <a href="https://www.instagram.com/tomas_kucera09/#" aria-label="Instagram" class="hover:text-gray-400">
            <i class="fab fa-instagram text-lg"></i>
        </a>
    </nav>
</header>
<div id="mobile-menu" class="hidden fixed inset-0 bg-black bg-opacity-95 flex-col items-center justify-center space-y-6 text-xl z-50">
    <button id="menu-close" class="absolute top-6 right-6 text-3xl">
        <i class="fas fa-times"></i>
    </button>
    <nav class="flex flex-col items-center gap-6">
        <a href="/music/" class="hover:underline">Music</a>
        <a href="photogallery.php" class="hover:underline">Best Photos</a>
        <a href="gallery.php" class="hover:underline">All Photos</a>
        <a href="https://apps.sincore.eu" class="hover:underline">Apps</a>
        <a href="https://www.instagram.com/tomas_kucera09/#" aria-label="Instagram" class="hover:text-gray-400">
            <i class="fab fa-instagram text-2xl"></i>
        </a>
    </nav>
</div>
<main class="px-4 w-full flex flex-col gap-10 overflow-hidden">
  <?php
    require_once __DIR__ . '/db.php';
    $img_dir = 'img/';

    // Fetch super promoted images
    $super_promoted_images = [];
    if ($res = $con->query("SELECT filename, COALESCE(description, '') AS description FROM images WHERE super_promoted = 1 ORDER BY created_at DESC")) {
        while ($row = $res->fetch_assoc()) {
            $super_promoted_images[] = [
                'filename' => $row['filename'],
                'description' => $row['description'],
            ];
        }
        $res->free();
    }

    // Fetch favorite images (filenames only)
    $favorite_images_filenames = [];
    if ($res = $con->query("SELECT filename FROM images WHERE favorite = 1 ORDER BY created_at DESC")) {
        while ($row = $res->fetch_assoc()) {
            $favorite_images_filenames[] = $row['filename'];
        }
        $res->free();
    }
  ?>

    <?php if (!empty($super_promoted_images)): ?>
        <section class="flex flex-col md:flex-row md:items-center md:gap-10 max-w-screen-xl mx-auto w-full">
        <h2 class="text-white font-bold text-lg mb-2">Fotogalerie</h2>
            <?php foreach ($super_promoted_images as $super_promoted_image): ?>
                <img alt="<?= htmlspecialchars($super_promoted_image['description']) ?>"
                     class="rounded-xl w-full max-w-md object-cover" height="250"
                     src="<?= htmlspecialchars($img_dir . $super_promoted_image['filename']) ?>" width="400"/>
                <div class="mt-6 md:mt-0 max-w-md">
                    <h2 class="font-bold text-white text-lg"><?= htmlspecialchars(pathinfo($super_promoted_image['filename'], PATHINFO_FILENAME)) ?></h2>
                    <p class="text-gray-400 mt-1 text-sm"><?= htmlspecialchars($super_promoted_image['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="flex flex-col items-center gap-6 w-full">
        
        <?php if (!empty($favorite_images_filenames)): ?>
            <div class="relative w-full max-w-screen-xl overflow-hidden rounded-lg">
                <div class="flex transition-transform duration-500 ease-in-out" id="slider1">
                    <?php foreach ($favorite_images_filenames as $index => $image_filename): ?>
                        <?php $description = pathinfo($image_filename, PATHINFO_FILENAME); ?>
                        <div class="relative w-full flex-shrink-0">
                            <img src="<?= htmlspecialchars($img_dir . $image_filename) ?>"
                                 class="w-full h-[500px] object-cover rounded-lg" alt="<?= htmlspecialchars($description) ?>">
                            <div class="absolute bottom-0 bg-black bg-opacity-50 text-white text-sm p-2 w-full text-center"><?= htmlspecialchars(pathinfo($image_filename, PATHINFO_FILENAME)) ?></div>
                            <button class="absolute top-2 right-2 bg-white bg-opacity-20 p-1 rounded hover:bg-opacity-40"
                                    title="Zobrazit na celou obrazovku"
                                    onclick="openFullscreenSlider(<?= $index ?>)">
                                <i class="fas fa-expand text-white"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="absolute top-1/2 left-2 -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white rounded-full p-1"
                        id="prevBtn1" title="Předchozí"><i class="fas fa-chevron-left"></i></button>
                <button class="absolute top-1/2 right-2 -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white rounded-full p-1"
                        id="nextBtn1" title="Další"><i class="fas fa-chevron-right"></i></button>
            </div>
        <?php endif; ?>
</main>
<footer class="mt-auto py-6 text-center text-xs text-gray-500 space-y-2">
    <nav class="space-x-4">
        <a class="hover:text-white transition" href="music/">Music</a>
        <a class="hover:text-white transition" href="https://apps.sincore.eu">Apps</a>
        <a class="hover:text-white transition" href="https://www.instagram.com/tomas_kucera09/#">
            <i class="fab fa-instagram"></i>
        </a>
    </nav>
    <div>© Copyright 2025 Tomas Kucera. All Rights Reserved.</div>
</footer>
<div id="fullscreenSliderElement" class="fullscreen-slider">
    <div class="relative w-full h-full flex items-center justify-center">
        <button class="absolute left-4 sm:left-8 text-white text-3xl z-10 hover:opacity-75" onclick="changeFullscreenSlide(-1)" title="Předchozí">
            <i class="fas fa-chevron-left"></i>
        </button>
        <img id="fullscreenImageElement" src="" alt="Fullscreen fotografie" />
        <button class="absolute right-4 sm:right-8 text-white text-3xl z-10 hover:opacity-75" onclick="changeFullscreenSlide(1)" title="Další">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
    <button class="absolute top-4 right-4 text-white text-2xl bg-gray-800 bg-opacity-70 p-2 rounded-full hover:bg-opacity-90" onclick="closeFullscreenSlider()" title="Zavřít fullscreen">
        <i class="fas fa-times"></i>
    </button>
</div>
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuClose = document.getElementById('menu-close');

    menuToggle.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.add('flex');
    });

    menuClose.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
        mobileMenu.classList.remove('flex');
    });

    let currentFullscreenIndex = 0;
    const fullscreenSliderElement = document.getElementById("fullscreenSliderElement");
    const fullscreenImageElement = document.getElementById("fullscreenImageElement");
    const favoriteImageUrlsForFullscreen = [];

    <?php if (!empty($favorite_images_filenames)): ?>
      <?php foreach ($favorite_images_filenames as $filename): ?>
        favoriteImageUrlsForFullscreen.push("<?= htmlspecialchars($img_dir . $filename) ?>");
      <?php endforeach; ?>
    <?php endif; ?>

    function openFullscreenSlider(index) {
        if (index >= 0 && index < favoriteImageUrlsForFullscreen.length) {
            currentFullscreenIndex = index;
            fullscreenImageElement.src = favoriteImageUrlsForFullscreen[currentFullscreenIndex];
            fullscreenSliderElement.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeFullscreenSlider() {
        fullscreenSliderElement.style.display = 'none';
        document.body.style.overflow = '';
    }

    function changeFullscreenSlide(direction) {
        currentFullscreenIndex = (currentFullscreenIndex + direction + favoriteImageUrlsForFullscreen.length) % favoriteImageUrlsForFullscreen.length;
        fullscreenImageElement.src = favoriteImageUrlsForFullscreen[currentFullscreenIndex];
    }
</script>
</body>
</html>
