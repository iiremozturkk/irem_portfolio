<?php
// Bu dosya: Admin kullanıcı girişi, hata yönetimi ve giriş ekranı arayüzünü içerir.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Giriş formu gönderimleri için CSRF anahtarı oturumda hazır tutulur.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$username = '';
$loginSuccess = false;
$adminLang = (($_COOKIE['portfolio_lang'] ?? 'en') === 'tr') ? 'tr' : 'en';
$L = [
    'tr' => [
        'csrf_error' => 'Güvenlik doğrulaması başarısız. Lütfen sayfayı yenileyip tekrar deneyin.',
        'login_error' => 'Kimlik doğrulama başarısız. Kullanıcı adı veya gizli anahtar hatalı.',
        'aria_main' => 'NEXUS sınırlı yönetici erişimi',
        'aria_form' => 'Admin giriş formu',
        'system_online' => 'SYSTEM ONLINE',
        'encrypted' => 'IPv6 ENCRYPTED',
        'restricted' => 'Restricted',
        'entry' => 'Entry',
        'control_layer' => 'Portfolio Control Layer v4.2.1',
        'user_id' => 'KULLANICI KİMLİĞİ',
        'secret_key' => 'GİZLİ ANAHTAR',
        'show_password' => 'Şifreyi göster',
        'signal_strength' => 'SİNYAL GÜCÜ',
        'remember_terminal' => 'BU TERMİNALİ HATIRLA',
        'owner_only' => 'YALNIZCA SAHİP',
        'start_login' => 'GİRİŞİ BAŞLAT',
        'secure_connection' => 'BAĞLANTI: GÜVENLİ',
        'biometric_scan' => '◈ BİYOMETRİK TARAMA',
        'live_analysis' => 'CANLI ANALİZ',
        'success' => 'BAŞARILI',
        'owner_verified' => 'OWNER VERIFIED',
        'waiting_password' => 'ŞİFRE BEKLENİYOR • RADAR TARANIYOR...',
        'loading_aria' => 'Panel yükleniyor',
        'loading' => 'YÜKLENİYOR...',
        'marquee' => '> Kimlik doğrulandı. Projeler, mesajlar, veritabanı ve portfolyo modülleri güvenli çalışma alanına yükleniyor. <',
    ],
    'en' => [
        'csrf_error' => 'Security verification failed. Please refresh the page and try again.',
        'login_error' => 'Authentication failed. Username or secret key is incorrect.',
        'aria_main' => 'NEXUS restricted admin access',
        'aria_form' => 'Admin login form',
        'system_online' => 'SYSTEM ONLINE',
        'encrypted' => 'IPv6 ENCRYPTED',
        'restricted' => 'Restricted',
        'entry' => 'Entry',
        'control_layer' => 'Portfolio Control Layer v4.2.1',
        'user_id' => 'USER IDENTITY',
        'secret_key' => 'SECRET KEY',
        'show_password' => 'Show password',
        'signal_strength' => 'SIGNAL STRENGTH',
        'remember_terminal' => 'REMEMBER THIS TERMINAL',
        'owner_only' => 'OWNER ONLY',
        'start_login' => 'START LOGIN',
        'secure_connection' => 'CONNECTION: SECURE',
        'biometric_scan' => '◈ BIOMETRIC SCAN',
        'live_analysis' => 'LIVE ANALYSIS',
        'success' => 'SUCCESSFUL',
        'owner_verified' => 'OWNER VERIFIED',
        'waiting_password' => 'WAITING FOR PASSWORD • RADAR SCANNING...',
        'loading_aria' => 'Panel loading',
        'loading' => 'LOADING...',
        'marquee' => '> Identity verified. Projects, messages, database and portfolio modules are loading into the secure workspace. <',
    ],
];
$t = $L[$adminLang];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['admin_login_error'])) {
    $error = (string) $_SESSION['admin_login_error'];
    $username = (string) ($_SESSION['admin_login_username'] ?? '');
    unset($_SESSION['admin_login_error'], $_SESSION['admin_login_username']);
}

// Form gönderildiğinde kullanıcı adı, parola ve CSRF doğrulaması yapılır.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = $t['csrf_error'];
    } else {
        // Kullanıcı adıyla eşleşen admin kaydı güvenli sorgu ile aranır.
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            if (!empty($_POST['remember_visual'])) {
                setcookie('irem_admin_theme', 'nexus-control-layer', [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            } else {
                setcookie('irem_admin_theme', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }

            $loginSuccess = true;
        } else {
            $error = $t['login_error'];
        }
    }

    if (!$loginSuccess && $error !== '') {
        $_SESSION['admin_login_error'] = $error;
        $_SESSION['admin_login_username'] = $username;
        header('Location: login.php?login=failed');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e($adminLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('portfolioLang');
                if (!saved || (saved !== 'tr' && saved !== 'en')) return;
                var cookieLang = (document.cookie.match(/(?:^|; )portfolio_lang=([^;]+)/) || [])[1];
                if (cookieLang !== saved && !sessionStorage.getItem('adminLangSynced')) {
                    document.cookie = 'portfolio_lang=' + saved + '; path=/; max-age=31536000; samesite=lax';
                    sessionStorage.setItem('adminLangSynced', '1');
                    location.reload();
                }
            } catch (e) {}
        })();
    </script>
    <title>Restricted Entry — Portfolio Control Layer</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css?v=login-hotfix-20260511">
</head>
<body class="nexus-body<?= $error ? ' has-login-error' : '' ?>" data-login-success="<?= $loginSuccess ? '1' : '0' ?>" data-login-error="<?= $error ? '1' : '0' ?>">
    <div class="cursor-glow" id="cursorGlow" aria-hidden="true"></div>

    <!-- Giriş ekranı: form paneli ve görsel güvenlik animasyonları iki sütunda yer alır. -->
    <main class="nexus-layout" aria-label="<?= e($t['aria_main']) ?>">
        <!-- Sol panel: admin kullanıcı adı, parola ve beni hatırla alanları. -->
        <section class="nexus-left" aria-label="<?= e($t['aria_form']) ?>">
            <div class="status-bar">
                <span><span class="status-dot"></span><?= e($t['system_online']) ?></span>
                <span id="clock">--:--:--</span>
                <span><?= e($t['encrypted']) ?></span>
            </div>

            <div class="logo-block restricted-brand" aria-label="Restricted Entry portfolio control layer">
                <h1 class="restricted-title" aria-label="Restricted Entry">
                    <span class="restricted-title__white" data-text="<?= e($t['restricted']) ?>"><?= e($t['restricted']) ?></span>
                    <span class="restricted-title__red" data-text="<?= e($t['entry']) ?>"><?= e($t['entry']) ?></span>
                </h1>
                <div class="logo-sub"><?= e($t['control_layer']) ?></div>
            </div>

            <form method="POST" class="nexus-form" id="ownerLoginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="field">
                    <label class="field-label" for="username"><?= e($t['user_id']) ?></label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username" autocomplete="username" spellcheck="false" value="<?= e($username) ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="password"><?= e($t['secret_key']) ?></label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••••••" autocomplete="current-password" spellcheck="false" required>
                        <button type="button" class="input-icon" id="togglePass" aria-label="<?= e($t['show_password']) ?>"><span class="eye-icon" aria-hidden="true"></span></button>
                    </div>
                </div>

                <div class="strength-wrap" aria-label="Password signal strength">
                    <div class="strength-label">
                        <span><?= e($t['signal_strength']) ?></span>
                        <span id="strengthPct">0%</span>
                    </div>
                    <div class="strength-track">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                </div>

                <label class="check-row" id="rememberRow">
                    <input type="checkbox" name="remember_visual" value="1" class="remember-input" id="rememberInput">
                    <span class="check-box" id="checkBox" aria-hidden="true"></span>
                    <span><?= e($t['remember_terminal']) ?></span>
                    <span class="owner-only"><?= e($t['owner_only']) ?></span>
                </label>

                <button class="btn-login" id="loginBtn" type="submit">
                    <span><?= e($t['start_login']) ?></span>
                </button>

                <div class="err-msg" id="errMsg" role="alert" aria-live="polite"><?= $error ? e('⛔ ' . $error) : '' ?></div>
            </form>

            <div class="bottom-status">
                <span>NEXUS-PORTFOLIO-2026</span>
                <span id="connStatus"><?= e($t['secure_connection']) ?></span>
            </div>
        </section>

        <!-- Sağ panel: giriş deneyimini destekleyen radar ve biyometrik animasyonlar. -->
        <section class="nexus-right" aria-label="Biometric analysis panel">
            <div class="hud-corner hud-tl" aria-hidden="true"></div>
            <div class="hud-corner hud-tr" aria-hidden="true"></div>
            <div class="hud-corner hud-bl" aria-hidden="true"></div>
            <div class="hud-corner hud-br" aria-hidden="true"></div>

            <div class="hud-label hud-tl-label"><?= e($t['biometric_scan']) ?></div>
            <div class="hud-label hud-tr-label"><?= e($t['live_analysis']) ?></div>
            <div class="scan-beam" aria-hidden="true"></div>

            <div class="data-stream data-stream--one" aria-hidden="true">01101001
10010110
11000101
01011010
10101100
11110001
00110110
10011001</div>
            <div class="radar-wrap">
                <svg class="radar-svg" viewBox="0 0 380 380" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <defs>
                        <radialGradient id="radarGrad" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#ff0033" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#ff0033" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                    <path d="M190 190 L190 20 A170 170 0 0 1 360 190 Z" fill="url(#radarGrad)"/>
                </svg>

                <svg class="radar-static" viewBox="0 0 380 380" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="190" cy="190" r="170" fill="none" stroke="rgba(255,0,51,0.15)" stroke-width="1"/>
                    <circle cx="190" cy="190" r="127" fill="none" stroke="rgba(255,0,51,0.12)" stroke-width="1"/>
                    <circle cx="190" cy="190" r="85" fill="none" stroke="rgba(255,0,51,0.12)" stroke-width="1"/>
                    <circle cx="190" cy="190" r="42" fill="none" stroke="rgba(255,0,51,0.15)" stroke-width="1"/>
                    <circle cx="190" cy="190" r="10" fill="rgba(255,0,51,0.3)" stroke="rgba(255,0,51,0.6)" stroke-width="1"/>
                    <line x1="190" y1="20" x2="190" y2="360" stroke="rgba(255,0,51,0.1)" stroke-width="1"/>
                    <line x1="20" y1="190" x2="360" y2="190" stroke="rgba(255,0,51,0.1)" stroke-width="1"/>

                    <rect x="135" y="135" width="110" height="110" fill="none" stroke="rgba(255,0,51,0.36)" stroke-width="1" stroke-dasharray="4 5"/>
                    <line x1="150" y1="135" x2="150" y2="124" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="230" y1="135" x2="230" y2="124" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="150" y1="245" x2="150" y2="256" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="230" y1="245" x2="230" y2="256" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="135" y1="150" x2="124" y2="150" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="135" y1="230" x2="124" y2="230" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="245" y1="150" x2="256" y2="150" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>
                    <line x1="245" y1="230" x2="256" y2="230" stroke="rgba(255,0,51,0.9)" stroke-width="3"/>

                    <g class="svg-user-icon">
                        <circle cx="190" cy="183" r="14" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.4)" stroke-width="1.5"/>
                        <circle cx="190" cy="178" r="7" fill="rgba(255,255,255,0.5)"/>
                        <path d="M165 206 Q190 194 215 206" fill="rgba(255,255,255,0.5)" stroke="none"/>
                    </g>
                </svg>

                <figure class="success-portrait" aria-hidden="true">
                    <span class="success-portrait__badge"><?= e($t['success']) ?></span>
                    <img src="../assets/images/admin-success-portrait.png" alt="Portfolio owner portrait">
                    <figcaption><?= e($t['owner_verified']) ?></figcaption>
                </figure>

                <div class="blip blip-1" aria-hidden="true"></div>
                <div class="blip blip-2" aria-hidden="true"></div>
                <div class="blip blip-3" aria-hidden="true"></div>
            </div>

            <div class="success-stamp" aria-hidden="true"><?= e($t['success']) ?></div>

            <div class="hud-label hud-bot" id="radarStatus">
                <?= e($t['waiting_password']) ?>
            </div>
        </section>
    </main>

    <!-- Başarılı girişten sonra kısa geçiş animasyonu için kullanılan çekmece. -->
    <section class="dashboard-drawer portal-drawer sweep-loader" id="dashboardDrawer" aria-hidden="true">
        <div class="sweep-speedlines" aria-hidden="true"></div>
        <div class="sweep-loader__inner">
            <div class="boot-loader" role="progressbar" aria-label="<?= e($t['loading_aria']) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="boot-loader__meta">
                    <span id="bootStatusText"><?= e($t['loading']) ?></span>
                    <strong id="bootProgressText">0%</strong>
                </div>
                <div class="boot-loader__track">
                    <span class="boot-loader__fill" id="bootProgressFill"></span>
                    <em></em>
                </div>
            </div>

            <div class="sweep-marquee" aria-label="<?= e($t['loading_aria']) ?>">
                <span><?= e($t['marquee']) ?></span>
            </div>
        </div>
    </section>

    <script>
        window.ADMIN_LOGIN_SUCCESS = <?= $loginSuccess ? 'true' : 'false' ?>;
        window.ADMIN_LOGIN_LANG = <?= json_encode($adminLang) ?>;
    </script>
    <script src="../assets/js/admin-login.js"></script>
</body>
</html>
