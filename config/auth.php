<?php
// Bu dosya: Oturum, CSRF güvenliği ve güvenli çıktı üretimi için ortak yardımcı fonksiyonlar.
// Oturum henüz başlamadıysa güvenli çerez ayarlarıyla başlatılır.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Admin olmayan kullanıcıların korumalı sayfalara erişmesini engeller.
function require_admin(): void
{
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Form güvenliği için oturum bazlı CSRF anahtarı üretir veya mevcut anahtarı döndürür.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Formdan gelen CSRF anahtarının oturumdaki değerle eşleşip eşleşmediğini kontrol eder.
function verify_csrf_token(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// PHP formlarına eklenmek üzere gizli CSRF input alanını üretir.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// CSRF doğrulaması başarısız olduğunda isteği güvenli şekilde sonlandırır.
function reject_invalid_csrf(): void
{
    http_response_code(400);
    exit('Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.');
}

// Ekrana basılacak değerleri XSS riskine karşı HTML olarak kaçışlar.
function e($value): string
{
    if (is_array($value)) {
        $value = implode(', ', array_map(static function ($item) {
            return is_scalar($item) ? (string)$item : json_encode($item, JSON_UNESCAPED_UNICODE);
        }, $value));
    } elseif (is_object($value)) {
        $value = method_exists($value, '__toString') ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
