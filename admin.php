<?php
// Initialize the session
session_start();

if (!isset($_SESSION["account_loggedin"]) || $_SESSION["account_loggedin"] !== true) {
	header("location: login.php");
	exit;
}

require_once __DIR__ . '/db.php';

$img_dir = 'img/'; // Image directory

// Ensure images table exists
$con->query("CREATE TABLE IF NOT EXISTS images (
	id INT AUTO_INCREMENT PRIMARY KEY,
	filename VARCHAR(255) NOT NULL,
	original_name VARCHAR(255) DEFAULT NULL,
	description TEXT DEFAULT NULL,
	favorite TINYINT(1) NOT NULL DEFAULT 0,
	super_promoted TINYINT(1) NOT NULL DEFAULT 0,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY unique_filename (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Helper to fetch all images metadata keyed by filename
function fetch_images_assoc(mysqli $con): array {
	$images = [];
	$res = $con->query("SELECT filename, original_name, favorite, super_promoted, COALESCE(description,'') AS description FROM images");
	if ($res) {
		while ($row = $res->fetch_assoc()) {
			$images[$row['filename']] = [
				'original_name' => (string)($row['original_name'] ?? ''),
				'favorite' => (bool)$row['favorite'],
				'super_promoted' => (bool)$row['super_promoted'],
				'description' => (string)$row['description'],
			];
		}
		$res->free();
	}
	return $images;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['upload'])) {
		// Handle image upload
		$file = $_FILES['image'] ?? null;
		$new_filename = trim($_POST['image_name'] ?? '');
		$description = trim($_POST['image_description'] ?? '');
		if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
			$upload_message = "Error uploading image.";
		} elseif ($new_filename === '') {
			$upload_message = "Please enter a name for the image.";
		} else {
			$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
			$new_filename_with_extension = $new_filename . '.' . $file_extension;
			$target_path = $img_dir . $new_filename_with_extension;

			if (move_uploaded_file($file['tmp_name'], $target_path)) {
				$stmt = $con->prepare("INSERT INTO images (filename, original_name, description, favorite, super_promoted) VALUES (?, ?, ?, 0, 0)");
				$orig = $file['name'];
				$stmt->bind_param('sss', $new_filename_with_extension, $orig, $description);
				if ($stmt->execute()) {
					$upload_message = "Image uploaded successfully!";
				} else {
					$upload_message = "Saved file but failed DB insert: " . htmlspecialchars($stmt->error);
				}
				$stmt->close();
			} else {
				$upload_message = "Error moving uploaded file.";
			}
		}
	} elseif (isset($_POST['delete'])) {
		// Handle image deletion
		$filename = $_POST['delete'];
		$file_path = $img_dir . $filename;

		$ok = true;
		if (is_file($file_path)) {
			$ok = unlink($file_path);
		}
		if ($ok) {
			$stmt = $con->prepare("DELETE FROM images WHERE filename = ?");
			$stmt->bind_param('s', $filename);
			if ($stmt->execute()) {
				$delete_message = "Image deleted successfully!";
			} else {
				$delete_message = "DB delete error.";
			}
			$stmt->close();
		} else {
			$delete_message = "Error deleting image file.";
		}
	} elseif (isset($_POST['favorite'])) {
		$filename = $_POST['favorite'];
		// Toggle favorite
		$con->query("UPDATE images SET favorite = 1 - favorite WHERE filename = '" . $con->real_escape_string($filename) . "'");
	} elseif (isset($_POST['super_promoted'])) {
		$filename = $_POST['super_promoted'];
		// Toggle super_promoted
		$con->query("UPDATE images SET super_promoted = 1 - super_promoted WHERE filename = '" . $con->real_escape_string($filename) . "'");
	} elseif (isset($_POST['rename'])) {
		// Handle image renaming
		$old_filename = $_POST['rename'];
		$new_filename = trim($_POST['new_name'] ?? '');
		$file_extension = pathinfo($old_filename, PATHINFO_EXTENSION);
		$new_filename_with_extension = $new_filename . '.' . $file_extension;
		$old_path = $img_dir . $old_filename;
		$new_path = $img_dir . $new_filename_with_extension;

		if ($new_filename === '') {
			$rename_message = "Please enter a new name.";
		} elseif (file_exists($old_path) && rename($old_path, $new_path)) {
			$stmt = $con->prepare("UPDATE images SET filename = ? WHERE filename = ?");
			$stmt->bind_param('ss', $new_filename_with_extension, $old_filename);
			if ($stmt->execute()) {
				$rename_message = "Image renamed successfully!";
			} else {
				$rename_message = "DB update failed.";
			}
			$stmt->close();
		} else {
			$rename_message = "Error renaming image.";
		}
	} elseif (isset($_POST['update_description'])) {
		$filename = $_POST['update_description'];
		$new_description = trim($_POST['new_description'] ?? '');
		$stmt = $con->prepare("UPDATE images SET description = ? WHERE filename = ?");
		$stmt->bind_param('ss', $new_description, $filename);
		if ($stmt->execute()) {
			$description_message = "Description updated successfully!";
		} else {
			$description_message = "DB update failed.";
		}
		$stmt->close();
	}
}

// Get list of images from the img directory
$images = [];
if (is_dir($img_dir)) {
	$files = scandir($img_dir);
	foreach ($files as $file) {
		if ($file !== '.' && $file !== '..') {
			$images[] = $file;
		}
	}
}

// Load image metadata from DB
$image_data = fetch_images_assoc($con);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-300 font-sans">

<header class="bg-gray-800 shadow-md flex justify-between items-center px-4 sm:px-6 py-4">
    <h1 class="text-xl font-bold text-white">Admin Panel</h1>
    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">Odhlásit se</a>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <?php if (isset($upload_message)): ?>
        <div class="bg-green-900 border border-green-600 text-green-300 px-4 py-3 rounded-md relative mb-4" role="alert"><?= htmlspecialchars($upload_message) ?></div>
    <?php endif; ?>

    <?php if (isset($delete_message)): ?>
        <div class="bg-green-900 border border-green-600 text-green-300 px-4 py-3 rounded-md relative mb-4" role="alert"><?= htmlspecialchars($delete_message) ?></div>
    <?php endif; ?>

    <?php if (isset($rename_message)): ?>
        <div class="bg-green-900 border border-green-600 text-green-300 px-4 py-3 rounded-md relative mb-4" role="alert"><?= htmlspecialchars($rename_message) ?></div>
    <?php endif; ?>

    <?php if (isset($description_message)): ?>
        <div class="bg-green-900 border border-green-600 text-green-300 px-4 py-3 rounded-md relative mb-4" role="alert"><?= htmlspecialchars($description_message) ?></div>
    <?php endif; ?>

    <section class="bg-gray-800 p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold text-white mb-4">Upload Image</h2>
        <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-4">
            <input type="file" name="image" required class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
            <input type="text" name="image_name" placeholder="Image Name" required class="bg-gray-700 border border-gray-600 text-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            <textarea name="image_description" placeholder="Image Description" class="bg-gray-700 border border-gray-600 text-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
            <button type="submit" name="upload" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-md focus:outline-none focus:shadow-outline w-full sm:w-auto self-start">Upload</button>
        </form>
    </section>

    <section>
        <h2 class="text-xl font-semibold text-white mb-4">Manage Images</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($images as $image): ?>
                <div class="bg-gray-800 shadow-md rounded-lg p-4 flex flex-col gap-4">
                    <img src="<?= htmlspecialchars($img_dir . $image) ?>" alt="<?= htmlspecialchars($image) ?>" class="w-full h-48 object-cover rounded-md">
                    
                    <div>
                        <p class="text-sm text-gray-400 break-words"><strong class="text-gray-300">Original Name:</strong> <?= htmlspecialchars($image_data[$image]['original_name'] ?? '') ?></p>
                        <p class="text-sm text-gray-400 break-words"><strong class="text-gray-300">New Name:</strong> <?= htmlspecialchars(pathinfo($image, PATHINFO_FILENAME)) ?></p>
                    </div>

                    <form method="post" class="flex items-center gap-2">
                        <input type="hidden" name="rename" value="<?= htmlspecialchars($image) ?>">
                        <input type="text" name="new_name" placeholder="New Name" class="bg-gray-700 border border-gray-600 text-gray-300 rounded-md py-1 px-2 text-sm w-full">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded-md text-xs whitespace-nowrap">Rename</button>
                    </form>

                    <form method="post" class="flex items-start gap-2">
                        <input type="hidden" name="update_description" value="<?= htmlspecialchars($image) ?>">
                        <textarea name="new_description" placeholder="New Description" class="bg-gray-700 border border-gray-600 text-gray-300 rounded-md py-1 px-2 text-sm w-full h-20 resize-none"><?= htmlspecialchars($image_data[$image]['description'] ?? '') ?></textarea>
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded-md text-xs whitespace-nowrap">Update</button>
                    </form>

                    <div class="flex flex-wrap justify-between items-center gap-2 mt-auto pt-4 border-t border-gray-700">
                        <form method="post">
                            <input type="hidden" name="delete" value="<?= htmlspecialchars($image) ?>">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-1 px-3 rounded-md text-xs inline-flex items-center gap-1.5"><i class="fas fa-trash-alt"></i> Delete</button>
                        </form>

                        <div class="flex gap-2">
                            <form method="post">
                                <input type="hidden" name="favorite" value="<?= htmlspecialchars($image) ?>">
                                <?php
                                $is_favorite = !empty($image_data[$image]['favorite']);
                                $favorite_class = $is_favorite 
                                    ? 'bg-yellow-500 bg-opacity-20 text-yellow-400 hover:bg-opacity-30' 
                                    : 'bg-gray-600 hover:bg-gray-700 text-gray-300';
                                ?>
                                <button type="submit" class="font-semibold py-1 px-3 rounded-md text-xs inline-flex items-center gap-1.5 transition-colors <?= $favorite_class ?>"><i class="fas fa-star"></i> Favorite</button>
                            </form>

                            <form method="post">
                                <input type="hidden" name="super_promoted" value="<?= htmlspecialchars($image) ?>">
                                <?php
                                $is_super_promoted = !empty($image_data[$image]['super_promoted']);
                                $super_promoted_class = $is_super_promoted 
                                    ? 'bg-green-500 bg-opacity-20 text-green-400 hover:bg-opacity-30' 
                                    : 'bg-gray-600 hover:bg-gray-700 text-gray-300';
                                ?>
                                <button type="submit" class="font-semibold py-1 px-3 rounded-md text-xs inline-flex items-center gap-1.5 transition-colors <?= $super_promoted_class ?>"><i class="fas fa-rocket"></i> Promoted</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

</body>
</html>
