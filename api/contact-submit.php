<?php
// Bu dosya: İletişim formundan gelen AJAX isteklerini doğrulayıp veritabanına kaydeden API uç noktası.
// API yanıtları tarayıcıya JSON ve UTF-8 olarak bildirilir.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

// Bu uç nokta yalnızca form gönderimlerini kabul eder.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed.']);
    exit;
}

// mbstring eklentisi varsa UTF-8 uyumlu uzunluk ölçümü yapar.
function safe_length(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

// Honeypot alanı bot gönderimlerini yakalamak için görünmez bırakılır.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid form submission.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Her doğrulama hatası tek listede toplanarak kullanıcıya birlikte döndürülür.
$errors = [];

if (safe_length($name) < 2) {
    $errors[] = 'Name must be at least 2 characters.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (safe_length($subject) < 3) {
    $errors[] = 'Subject must be at least 3 characters.';
}

if (safe_length($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
}

if (safe_length($message) > 1000) {
    $errors[] = 'Message cannot be longer than 1000 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Doğrulanan mesaj prepared statement ile veritabanına kaydedilir.
try {
    $stmt = $pdo->prepare(
        'INSERT INTO messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)'
    );

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message,
    ]);

    echo json_encode([
        'success' => true,
        'message' => '✓ Message saved to İrem’s database. Thank you!'
    ]);
} catch (PDOException $e) {
    error_log('Contact form DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error. Please try again later.'
    ]);
}
?>
