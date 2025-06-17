<?php
require_once '../private/db.php';
require_once '../private/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: index.php");
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: ../admin/index.php");
        exit();
    }
    else {
        $error = "Invalid login.";
    }
}
?>
<form method="post">
  <input name="username" placeholder="Username"><br>
  <input name="password" type="password" placeholder="Password"><br>
  <button type="submit">Login</button>
  <?php if (isset($error)) echo "<p>$error</p>"; ?>
</form>
