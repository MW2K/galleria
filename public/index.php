<?php
require_once '../private/auth.php';
require_login();
require_once '../private/db.php';

$where = "";
$params = [];
if (!empty($_GET['tag'])) {
    $where = "JOIN image_tags it ON i.id = it.image_id JOIN tags t ON it.tag_id = t.id WHERE t.name = ?";
    $params[] = $_GET['tag'];
}

$sort = in_array($_GET['sort'] ?? '', ['uploaded_at', 'title']) ? $_GET['sort'] : 'uploaded_at';
$page = max((int)($_GET['page'] ?? 1), 1);
$per_page = 5;
$offset = ($page - 1) * $per_page;

$total = $pdo->prepare("SELECT COUNT(*) FROM images i $where");
$total->execute($params);
$count = $total->fetchColumn();

$stmt = $pdo->prepare("SELECT i.* FROM images i $where ORDER BY i.$sort DESC LIMIT ? OFFSET ?");
foreach ($params as $k => $v) $stmt->bindValue($k+1, $v);
$stmt->bindValue(count($params)+1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(count($params)+2, $offset, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll();
?>
<?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == true): ?>
<a href="admin/index.php">Admin Panel</a> |
<?php endif ?>
<a href="upload.php">Upload</a> | <a href="logout.php">Logout</a>

<form method="get">
  <input name="tag" placeholder="Search by tag" value="<?= htmlspecialchars($_GET['tag'] ?? '') ?>">
  <button>Search</button>
</form>

<form method="get">
  Sort:
  <select name="sort" onchange="this.form.submit()">
    <option value="uploaded_at" <?= $sort == 'uploaded_at' ? 'selected' : '' ?>>Newest</option>
    <option value="title" <?= $sort == 'title' ? 'selected' : '' ?>>Title</option>
  </select>
</form>

<?php foreach ($images as $img): ?>
  <div>
    <h3><?= htmlspecialchars($img['title']) ?></h3>
    <img src="<?= THUMBNAIL_DIR . '/' . htmlspecialchars($img['filename']) ?>"
    alt="Thumbnail"
    onclick="showImage('<?= UPLOAD_DIR . '/' . htmlspecialchars($img['filename']) ?>')">

    <div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.8); text-align:center;">
    <img id="fullImage" src="" style="max-width:90%; max-height:90%; margin-top:5%;">
    <button onclick="document.getElementById('imageModal').style.display='none'">Close</button>
    </div>

    <script>
    function showImage(src) {
      document.getElementById('fullImage').src = src;
      document.getElementById('imageModal').style.display = 'block';
    }
    </script>
    </div>
<?php endforeach ?>

<div>
<?php for ($i=1; $i <= ceil($count/$per_page); $i++): ?>
  <a href="?page=<?= $i ?>&sort=<?= $sort ?>&tag=<?= urlencode($_GET['tag'] ?? '') ?>"><?= $i ?></a>
<?php endfor ?>
</div>


