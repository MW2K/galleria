<?php
require_once '../private/auth.php';
//require_login();
//require_admin();
if (!isset($_SESSION['is_admin'])) {
  // If not logged in, redirect to the login page
  header("Location: login.php");
  exit(); // Ensure no further code is executed
}
?>

<h1>Admin Panel</h1>
<ul>
  <li><a href="users.php">Manage Users</a></li>
  <li><a href="images.php">Manage Images</a></li>
  <li><a href="../public/index.php">Back to Gallery</a></li>
</ul>
