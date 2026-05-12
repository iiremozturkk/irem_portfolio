<?php
// Bu dosya: MySQL bağlantısını PDO ile kuran ortak veritabanı ayar dosyası.
 
 

// Veritabanı bağlantı bilgileri yerel geliştirme ortamına göre tanımlanır.
$host = 'localhost';
$dbname = 'irem_portfolio';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// PDO için kullanılacak DSN bilgisi karakter setiyle birlikte hazırlanır.
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO seçenekleri hata yakalama ve güvenli sorgu davranışını standartlaştırır.
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Bağlantı kurulamazsa kullanıcıya teknik detay göstermeden kontrollü hata döndürülür.
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
