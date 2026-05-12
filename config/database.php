<?php
// Bu dosya: MySQL bağlantısını PDO ile kuran ortak veritabanı ayar dosyası.

// InfinityFree veritabanı bağlantı bilgileri
$host = 'sql200.infinityfree.com';
$dbname = 'if0_41894637_portfolio';
$username = 'if0_41894637';
$password = 'irem8997302';
$charset = 'utf8mb4';

// PDO için DSN bilgisi
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO seçenekleri
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Veritabanı bağlantısını kur
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please check config/database.php.'
    ]));
}
?>