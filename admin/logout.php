<?php
// Bu dosya: Admin oturumunu kapatıp giriş sayfasına yönlendirir.
require_once __DIR__ . '/../config/auth.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
?>
