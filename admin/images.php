<?php
require_once '../private/db.php';
require_once '../private/auth.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete'])) {
    // Validate ID as an integer
    $deleteId = filter_var($_GET['delete'], FILTER_VALIDATE_INT);

    $deleteId = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($deleteId) {
        // Delete associated tags first
        $stmt = $pdo->prepare("DELETE FROM image_tags WHERE image_id = ?");
        $stmt->execute([$deleteId]);

        // Now delete the image
        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$deleteId]);
        $file = $stmt->fetchColumn();

        if ($file) {
            // Delete files safely
            @unlink(UPLOAD_DIR . '/' . $file);
            @unlink(THUMBNAIL_DIR . '/' . $file);

            // Execute DELETE query
            $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
            if ($stmt->execute([$deleteId])) {
                echo "<p>Image deleted successfully.</p>";
            } else {
                echo "<p>Error deleting image.</p>";
            }
        }
    }
}

// Fetch images
$images = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Images</h2>
<table border="1">
<tr><th>ID</th><th>Title</th><th>Uploaded</th><th>Action</th></tr>
<?php foreach ($images as $img): ?>
<tr>
<td><?= htmlspecialchars($img['id']) ?></td>
<td><?= htmlspecialchars($img['title']) ?></td>
<td><?= htmlspecialchars($img['uploaded_at']) ?></td>
<td>
<a href="?delete=<?= $img['id'] ?>" onclick="return confirm('Delete image?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>

<a href="index.php">Back to Admin</a>
