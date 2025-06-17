<?php
require_once '../private/auth.php';
session_destroy();
header("Location: login.php");
exit();
