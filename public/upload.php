<?php
require_once '../private/auth.php';
require_login();
require_once '../private/db.php';
require_once '../private/functions.php';
require_once '../private/csrf.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload failed. Error code: " . $_FILES['image']['error'];
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        if (!isset($allowedTypes[$mime])) {
            $errors[] = "Invalid file type.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "File too large (max 5MB).";
        } else {
            $ext = $allowedTypes[$mime];
            $newName = bin2hex(random_bytes(16)) . ".$ext";
            $targetPath = UPLOAD_DIR . '/' . $newName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $errors[] = "Failed to move uploaded file.";
            } else {
                resize_image($targetPath, THUMBNAIL_DIR . '/' . $newName, 300, 300);
                [$width, $height] = getimagesize($targetPath);

                $exif_data = null;
                if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
                    $exif_data = @exif_read_data($targetPath);
                }

                $stmt = $pdo->prepare("INSERT INTO images
                (filename, title, mime, width, height, exif)
                VALUES (?, ?, ?, ?, ?, ?)");

                $stmt->execute([
                    $newName,
                    trim($_POST['title']),
                               $mime,
                               $width,
                               $height,
                               $exif_data ? json_encode($exif_data) : null
                ]);

                $image_id = $pdo->lastInsertId();

                if (!empty($_POST['tags'])) {
                    $tags = array_map('trim', explode(',', $_POST['tags']));
                    foreach ($tags as $tag) {
                        $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tag]);
                        $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                        $stmt->execute([$tag]);
                        $tag_id = $stmt->fetchColumn();
                        $pdo->prepare("INSERT IGNORE INTO image_tags (image_id, tag_id) VALUES (?, ?)")->execute([$image_id, $tag_id]);
                    }
                }
                header("Location: index.php");
                exit;
            }
        }
    }
}
?>
<form method="post" enctype="multipart/form-data">
Title: <input name="title"><br>
Tags (comma separated): <input name="tags"><br>
<input type="file" name="image"><br>
<button>Upload</button>
</form>
