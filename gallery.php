<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet"/>
    <style>
        .signature-font {
            font-family: "Great Vibes", cursive;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-300 min-h-screen flex flex-col">
  <header class="sticky top-0 z-50 bg-gray-900 bg-opacity-80 backdrop-blur-sm shadow-md flex justify-between items-center px-6 py-4">

    <a href="index.html">
      <img src="data/logo.png" alt="Logo" class="h-10 w-auto">
    </a>
    
    <!-- Mobile menu button -->
    <button id="menu-toggle" class="md:hidden text-white text-2xl focus:outline-none">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Desktop navigation -->
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

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden fixed inset-0 bg-black bg-opacity-95 flex flex-col items-center justify-center space-y-6 text-xl z-50">
    <button id="menu-close" class="absolute top-6 right-6 text-3xl">
      <i class="fas fa-times"></i>
    </button>
    <a href="/music/" class="hover:underline">Music</a>
    <a href="photogallery.php" class="hover:underline">Best Photos</a>
    <a href="gallery.php" class="hover:underline">All Photos</a>
    <a href="https://apps.sincore.eu" class="hover:underline">Apps</a>
    <a href="https://www.instagram.com/tomas_kucera09/#" aria-label="Instagram" class="hover:text-gray-400">
      <i class="fab fa-instagram text-2xl"></i>
    </a>
  </div>
<!-- Main -->
<main class="max-w-7xl mx-auto px-6 py-10 w-full">
    <h1 class="text-center font-extrabold text-5xl mb-10 text-white">My gallery</h1>
    <div id="gallery" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        require_once __DIR__ . '/db.php';
        $img_dir = 'img/';

        // List actual files present in the folder
        $image_files = glob($img_dir . '*.{jpg,jpeg,png,gif,webp,bmp}', GLOB_BRACE);

        // Optional: ensure DB has metadata rows for any file not yet recorded
        if (!empty($image_files)) {
            $stmt = $con->prepare("INSERT IGNORE INTO images (filename) VALUES (?)");
            foreach ($image_files as $image_file) {
                $filename = basename($image_file);
                $stmt->bind_param('s', $filename);
                $stmt->execute();
            }
            $stmt->close();
        }

        foreach ($image_files as $image_file):
            $image = basename($image_file);
            ?>
            <img src="<?= htmlspecialchars($img_dir . $image) ?>"
                 class="gallery-img rounded-3xl w-full object-cover cursor-pointer"
                 alt="<?= htmlspecialchars($image) ?>">
        <?php endforeach; ?>
    </div>
</main>
<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center">
    <button onclick="closeLightbox()" class="absolute top-5 right-5 text-white text-3xl z-50"><i
                class="fas fa-times"></i></button>
    <button onclick="prevImage()" class="absolute left-5 text-white text-3xl z-50"><i
                class="fas fa-chevron-left"></i></button>
    <img id="lightbox-img" src="" alt="" class="max-w-4xl max-h-[90vh] rounded-xl shadow-lg"/>
    <button onclick="nextImage()" class="absolute right-5 text-white text-3xl z-50"><i
                class="fas fa-chevron-right"></i></button>
</div>
<!-- Footer -->
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
<!-- Lightbox Script -->
<script>
    const lightbox = document.getElementById("lightbox");
    const lightboxImg = document.getElementById("lightbox-img");
    const galleryImages = document.querySelectorAll(".gallery-img");
    let currentIndex = 0;

    function showImage(index) {
        const img = galleryImages[index];
        lightboxImg.src = img.src;
        lightbox.classList.remove("hidden");
        lightbox.classList.add("flex");
        currentIndex = index;
    }

    function closeLightbox() {
        lightbox.classList.add("hidden");
        lightbox.classList.remove("flex");
    }

    function nextImage() {
        currentIndex = (currentIndex + 1) % galleryImages.length;
        showImage(currentIndex);
    }

    function prevImage() {
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        showImage(currentIndex);
    }

    galleryImages.forEach((img, index) => {
        img.addEventListener("click", () => showImage(index));
    });

    // Close when clicking outside the image
    lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
        if (!lightbox.classList.contains("flex")) return;
        if (e.key === "ArrowRight") nextImage();
        if (e.key === "ArrowLeft") prevImage();
        if (e.key === "Escape") closeLightbox();
    });
      const menuToggle = document.getElementById('menu-toggle');
      const mobileMenu = document.getElementById('mobile-menu');
      const menuClose = document.getElementById('menu-close');

      menuToggle.addEventListener('click', () => {
        mobileMenu.classList.remove('hidden');
      });

      menuClose.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
      });
</script>
</body>
</html>
