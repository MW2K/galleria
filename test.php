<?php
// run once to create user
require 'private/db.php';
$hash = password_hash("password123", PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->execute(["admin", $hash]);
?>
