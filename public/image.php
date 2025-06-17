<?php
require_once '../private/db.php';
require_once '../private/auth.php';
require_login();

if (!isset($_GET['id'])) die("Image ID missing");

$stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
$stmt->execute([$_GET['id']]);
$image = $stmt->fetch();

if (!$image) die("Image not found");
?>

<h2><?= htmlspecialchars($image['title']) ?></h2>
<img src="uploads/<?= htmlspecialchars($image['filename']) ?>" style="max-width:500px;">

<h3>Metadata:</h3>
<ul>
  <li>MIME: <?= htmlspecialchars($image['mime']) ?></li>
  <li>Size: <?= $image['width'] ?> x <?= $image['height'] ?></li>
  <?php if ($image['exif']):
      $exif = json_decode($image['exif'], true);
      foreach ($exif as $k => $v): ?>
        <li><?= htmlspecialchars($k) ?>: <?= is_array($v) ? htmlspecialchars(json_encode($v)) : htmlspecialchars($v) ?></li>
  <?php endforeach; endif ?>
</ul>

<a href="index.php">Back</a>
