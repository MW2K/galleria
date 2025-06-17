<?php
require_once '../private/db.php';
require_once '../private/auth.php';
if (!isset($_SESSION['is_admin'])) {
  // If not logged in, redirect to the login page
  header("Location: login.php");
  exit(); // Ensure no further code is executed
}

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        isset($_POST['is_admin']) ? 1 : 0
    ]);
}

// Delete user
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND username != ?");
    $stmt->execute([$_GET['delete'], 'admin']); // cannot delete admin account
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<h2>Users</h2>
<table border="1">
  <tr><th>ID</th><th>Username</th><th>Admin</th><th>Action</th></tr>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><?= $u['id'] ?></td>
    <td><?= htmlspecialchars($u['username']) ?></td>
    <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
    <td><?php if ($u['username'] != 'admin'): ?>
        <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
    <?php endif; ?></td>
  </tr>
  <?php endforeach ?>
</table>

<h3>Add User</h3>
<form method="post">
  Username: <input name="username" required><br>
  Password: <input name="password" type="password" required><br>
  Admin? <input type="checkbox" name="is_admin"><br>
  <button>Add User</button>
</form>

<a href="index.php">Back to Admin</a>
