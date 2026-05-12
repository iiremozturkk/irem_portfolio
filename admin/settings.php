<?php
// Bu dosya: Admin ayarlarını veritabanından okuyup güncelleyen sayfa.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$unreadCount = 0;
try {
    $unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
} catch (Throwable $e) {
    $unreadCount = 0;
}

$defaults = [
    'panel_title' => 'İrem Öztürk Control Center',
    'email' => 'admin@example.com',
    'view' => 'Dashboard',
    'theme' => 'Crimson HUD',
    'signature' => 'İrem Öztürk',
    'items_per_page' => '10',
    'quick_note' => 'Portfolio kontrol paneli aktif.',
];

$settings = $defaults;
$notice = '';
$error = '';

// Ayar formundan gelen metinleri temizler; boşsa mevcut değeri korur.
function setting_post_string(string $key, string $fallback): string
{
    $value = $_POST[$key] ?? $fallback;
    if (is_array($value)) {
        $value = reset($value);
    }
    return trim((string)$value);
}

// Ayarlar tablosu yoksa admin paneli açılırken otomatik oluşturulur.
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_settings (
        setting_key VARCHAR(80) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Ayar formu gönderildiğinde yeni değerler doğrulanıp veritabanına yazılır.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }
        if (isset($_POST['reset_settings'])) {
            $pdo->exec('DELETE FROM admin_settings');
            $notice = 'Ayarlar varsayılan değerlere döndürüldü.';
        } else {
            $viewOptions = ['Dashboard', 'Projeler', 'Mesajlar', 'Analitik', 'Takvim'];
            $themeOptions = ['Crimson HUD', 'Dark Neon'];
            $view = setting_post_string('view', $defaults['view']);
            $theme = setting_post_string('theme', $defaults['theme']);
            $items = (int)setting_post_string('items_per_page', $defaults['items_per_page']);
            $items = max(5, min(50, $items));

            $incoming = [
                'panel_title' => setting_post_string('panel_title', $defaults['panel_title']),
                'email' => filter_var(setting_post_string('email', $defaults['email']), FILTER_VALIDATE_EMAIL) ?: $defaults['email'],
                'view' => in_array($view, $viewOptions, true) ? $view : $defaults['view'],
                'theme' => in_array($theme, $themeOptions, true) ? $theme : $defaults['theme'],
                'signature' => setting_post_string('signature', $defaults['signature']),
                'items_per_page' => (string)$items,
                'quick_note' => setting_post_string('quick_note', $defaults['quick_note']),
            ];

            $stmt = $pdo->prepare('INSERT INTO admin_settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($incoming as $key => $value) {
                $stmt->execute([':k' => $key, ':v' => $value]);
            }
            $notice = 'Ayarlar kaydedildi.';
        }
    }

    $rows = $pdo->query('SELECT setting_key, setting_value FROM admin_settings')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $key = (string)($row['setting_key'] ?? '');
        if (array_key_exists($key, $settings)) {
            $settings[$key] = is_array($row['setting_value']) ? implode(', ', $row['setting_value']) : (string)$row['setting_value'];
        }
    }
} catch (Throwable $e) {
    $error = 'Ayarlar yüklenirken bir sorun oluştu. Veritabanı bağlantısını kontrol edin.';
}

$currentPage = 'settings';
$headerTitle = 'Ayarlar';
$headerSubtitle = 'Panel tercihleri ve hızlı yapılandırma';
$pageTitle = 'Settings';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Ayarlar sayfası: site başlığı, motto ve iletişim bilgileri düzenlenir. -->
<main class="cc-content">
    <article class="cc-card form-card settings-panel-card">
        <div class="cc-card__head small-head">
            <div>
                <h2>Panel Ayarları</h2>
                <p class="settings-muted">Yönetici panelinin başlık, bildirim ve kullanım tercihlerini buradan düzenleyebilirsin.</p>
            </div>
        </div>

        <?php if ($notice): ?><p class="save-notice"><?= e($notice) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="save-notice error-notice"><?= e($error) ?></p><?php endif; ?>

        <div class="settings-summary-grid">
            <div><span>Panel</span><strong><?= e($settings['panel_title']) ?></strong></div>
            <div><span>Bildirim</span><strong><?= e($settings['email']) ?></strong></div>
            <div><span>Tema</span><strong><?= e($settings['theme']) ?></strong></div>
        </div>

        <form method="POST" class="cc-form cc-form--grid settings-form">
            <?= csrf_field() ?>
            <label>Panel Başlığı<input name="panel_title" value="<?= e($settings['panel_title']) ?>" maxlength="120"></label>
            <label>Bildirim E-postası<input type="email" name="email" value="<?= e($settings['email']) ?>" maxlength="160"></label>
            <label>Varsayılan Görünüm
                <select name="view">
                    <?php foreach (['Dashboard', 'Projeler', 'Mesajlar', 'Analitik', 'Takvim'] as $view): ?>
                        <option value="<?= e($view) ?>" <?= $settings['view'] === $view ? 'selected' : '' ?>><?= e($view) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tema
                <select name="theme">
                    <?php foreach (['Crimson HUD', 'Dark Neon'] as $theme): ?>
                        <option value="<?= e($theme) ?>" <?= $settings['theme'] === $theme ? 'selected' : '' ?>><?= e($theme) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Liste Sayısı
                <input type="number" name="items_per_page" value="<?= e($settings['items_per_page']) ?>" min="5" max="50">
            </label>
            <label>İmza<input name="signature" value="<?= e($settings['signature']) ?>" maxlength="120"></label>
            <label class="wide">Hızlı Not<textarea name="quick_note" rows="4"><?= e($settings['quick_note']) ?></textarea></label>
            <div class="settings-actions wide">
                <button class="cc-primary-btn" type="submit">Kaydet</button>
                <button class="cc-secondary-btn" type="submit" name="reset_settings" value="1">Varsayılana Döndür</button>
            </div>
        </form>
    </article>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
