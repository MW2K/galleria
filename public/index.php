<?php
require_once '../private/auth.php';
require_login();
require_once '../private/db.php';

// Use named parameter for tag filtering and sanitize the input when reading it.
$where = "";
$params = [];
$tagInput = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING);
if (!empty($tagInput)) {
  $where = "JOIN image_tags it ON i.id = it.image_id JOIN tags t ON it.tag_id = t.id WHERE t.name = :tag";
  $params['tag'] = $tagInput;
}

// Secure sorting by whitelisting valid values.
$valid_sorts = ['uploaded_at', 'title'];
$sortInput = $_GET['sort'] ?? '';
$sort = in_array($sortInput, $valid_sorts) ? $sortInput : 'uploaded_at';

// Validate pagination
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Secure count query.
$total = $pdo->prepare("SELECT COUNT(*) FROM images i $where");
$total->execute($params);
$count = $total->fetchColumn();

// Secure image query (using only named parameters).
$query = "SELECT i.* FROM images i ";
if ($where) {
  $query .= $where;
}
$query .= " ORDER BY i.$sort DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
  $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
<a href="admin/index.php">Admin Panel</a> |
<?php endif ?>
<a href="upload.php">Upload</a> | <a href="logout.php">Logout</a>

<form method="get">
<input name="tag" placeholder="Search by tag" value="<?= htmlspecialchars($tagInput) ?>">
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
<!-- Thumbnail image that calls a function to show the full image -->
<img src="<?= THUMBNAIL_DIR . '/' . htmlspecialchars($img['filename']) ?>"
alt="Thumbnail"
onclick="showImage('<?= UPLOAD_DIR . '/' . htmlspecialchars($img['filename']) ?>')">
</div>

<div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); text-align:center;">
<img id="fullImage" src="" style="max-width:90%; max-height:90%; margin-top:5%;">
<button onclick="document.getElementById('imageModal').style.display='none'">Close</button>
</div>

<script>
function showImage(src) {
  document.getElementById('fullImage').src = src;
  document.getElementById('imageModal').style.display = 'block';
}
</script>
<?php endforeach ?>

<div>
<?php for ($i = 1; $i <= ceil($count / $per_page); $i++): ?>
<?php
// $tagInput is already sanitized. Use urlencode() for URL parameters.
// No need to wrap urlencode() output in htmlspecialchars() because urlencode() returns a URL-safe string.
$tagEncoded = urlencode($tagInput);
?>
<a href="?page=<?= $i ?>&sort=<?= urlencode($sort) ?>&tag=<?= $tagEncoded ?>"><?= $i ?></a>
<?php endfor ?>
</div>
