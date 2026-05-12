<?php
// Bu dosya: Harici servis entegrasyonlarının durumlarını yöneten admin sayfası.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'integrations';
$headerTitle = 'Entegrasyonlar';
$headerSubtitle = 'Portfolyo servislerini bağla ve izle';
$searchPlaceholder = 'Entegrasyonlarda ara...';
$pageTitle = 'Entegrasyonlar';
$notice = null;
$errorNotice = null;

$defaultIntegrations = [
    'portfolio_api' => [
        'name_tr' => 'Portfolyo API', 'name_en' => 'Portfolio API',
        'description_tr' => 'Dinamik projeleri ve herkese açık portfolyo verilerini yükler.',
        'description_en' => 'Loads dynamic projects and public portfolio data.',
        'endpoint' => '../api/get-projects.php', 'status' => 'connected'
    ],
    'contact_inbox' => [
        'name_tr' => 'İletişim Gelen Kutusu', 'name_en' => 'Contact Inbox',
        'description_tr' => 'İletişim formu mesajlarını MySQL veritabanına kaydeder.',
        'description_en' => 'Stores contact form messages in MySQL.',
        'endpoint' => '../api/contact-submit.php', 'status' => 'connected'
    ],
    'visitor_analytics' => [
        'name_tr' => 'Ziyaretçi Analitiği', 'name_en' => 'Visitor Analytics',
        'description_tr' => 'Ana sayfa ziyaretlerini takip eder ve analitik panelini besler.',
        'description_en' => 'Tracks homepage visits and feeds the analytics dashboard.',
        'endpoint' => 'analytics.php', 'status' => 'connected'
    ],
    'cv_exports' => [
        'name_tr' => 'CV Dışa Aktarımları', 'name_en' => 'CV Exports',
        'description_tr' => 'Türkçe ve İngilizce CV dosyalarını indirmeye hazır tutar.',
        'description_en' => 'Keeps Turkish and English CV files available for download.',
        'endpoint' => '../assets/cv/irem-ozturk-cv-en.pdf', 'status' => 'connected'
    ],
    'email_notifications' => [
        'name_tr' => 'E-posta Bildirimleri', 'name_en' => 'Email Notifications',
        'description_tr' => 'Gelecekteki mesaj bildirimleri için ayrılmış kanal.',
        'description_en' => 'Reserved channel for future message notifications.',
        'endpoint' => '#', 'status' => 'standby'
    ],
];

// Entegrasyon durumlarını sıralamada kullanılacak öncelik değerine çevirir.
function integration_status_rank(string $status): int {
    return ['connected' => 0, 'standby' => 1, 'disabled' => 2][$status] ?? 3;
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS integration_settings (
        integration_key VARCHAR(80) PRIMARY KEY,
        integration_name VARCHAR(120) NOT NULL,
        description TEXT NULL,
        endpoint VARCHAR(255) NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'standby',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $insert = $pdo->prepare("INSERT INTO integration_settings (integration_key, integration_name, description, endpoint, status)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE integration_name = VALUES(integration_name), description = VALUES(description), endpoint = VALUES(endpoint)");
    foreach ($defaultIntegrations as $key => $item) {
        $insert->execute([$key, $item['name_en'], $item['description_en'], $item['endpoint'], $item['status']]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }
        $key = $_POST['integration_key'] ?? '';
        $status = $_POST['status'] ?? '';
        if (isset($defaultIntegrations[$key]) && in_array($status, ['connected','standby','disabled'], true)) {
            $update = $pdo->prepare('UPDATE integration_settings SET status = ? WHERE integration_key = ?');
            $update->execute([$status, $key]);
            $notice = 'Entegrasyon durumu güncellendi.';
        }
    }

    $integrations = $pdo->query('SELECT * FROM integration_settings')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $integrations = [];
    foreach ($defaultIntegrations as $key => $item) {
        $integrations[] = [
            'integration_key' => $key,
            'integration_name' => $item['name_en'],
            'description' => $item['description_en'],
            'endpoint' => $item['endpoint'],
            'status' => $item['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
    $errorNotice = 'Veritabanı bağlantısı kurulamadı; demo entegrasyon listesi gösteriliyor.';
}

// Entegrasyon kartları önce duruma, sonra başlığa göre sıralanır.
usort($integrations, function ($a, $b) {
    $rank = integration_status_rank($a['status'] ?? 'standby') <=> integration_status_rank($b['status'] ?? 'standby');
    return $rank !== 0 ? $rank : strcasecmp($a['integration_name'] ?? '', $b['integration_name'] ?? '');
});

$connectedCount = count(array_filter($integrations, fn($item) => ($item['status'] ?? '') === 'connected'));
$standbyCount = count(array_filter($integrations, fn($item) => ($item['status'] ?? '') === 'standby'));
$disabledCount = count(array_filter($integrations, fn($item) => ($item['status'] ?? '') === 'disabled'));
$statusLabels = [
    'connected' => ['tr' => 'Bağlı', 'en' => 'Connected'],
    'standby' => ['tr' => 'Beklemede', 'en' => 'Standby'],
    'disabled' => ['tr' => 'Devre Dışı', 'en' => 'Disabled'],
];

include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Entegrasyon sayfası: servis bağlantılarının hazır, aktif veya beklemede durumlarını gösterir. -->
<main class="cc-content integrations-page">
    <?php if ($notice): ?><div class="cc-alert cc-alert--success" data-i18n-tr="<?= e($notice) ?>" data-i18n-en="Integration status updated."><?= e($notice) ?></div><?php endif; ?>
    <?php if ($errorNotice): ?><div class="cc-alert" data-i18n-tr="<?= e($errorNotice) ?>" data-i18n-en="Database connection could not be established; the demo integration list is shown."><?= e($errorNotice) ?></div><?php endif; ?>

    <section class="cc-grid simple-grid-3">
        <article class="cc-card"><h2 data-i18n-tr="Bağlı" data-i18n-en="Connected">Bağlı</h2><p class="stat-big"><?= e((string)$connectedCount) ?></p><p data-i18n-tr="Şu anda aktif servisler" data-i18n-en="Services currently active">Şu anda aktif servisler</p></article>
        <article class="cc-card"><h2 data-i18n-tr="Beklemede" data-i18n-en="Standby">Beklemede</h2><p class="stat-big"><?= e((string)$standbyCount) ?></p><p data-i18n-tr="Gelecekte etkinleştirmeye hazır" data-i18n-en="Ready for future activation">Gelecekte etkinleştirmeye hazır</p></article>
        <article class="cc-card"><h2 data-i18n-tr="Devre Dışı" data-i18n-en="Disabled">Devre Dışı</h2><p class="stat-big"><?= e((string)$disabledCount) ?></p><p data-i18n-tr="Duraklatılmış entegrasyonlar" data-i18n-en="Paused integrations">Duraklatılmış entegrasyonlar</p></article>
    </section>

    <section class="cc-card integrations-board">
        <div class="cc-card__head">
            <div>
                <h2 data-i18n-tr="Entegrasyon Merkezi" data-i18n-en="Integration Hub">Entegrasyon Merkezi</h2>
                <p data-i18n-tr="Portfolyo panelini çalıştıran servisleri buradan yönet." data-i18n-en="Manage the services that power your portfolio dashboard.">Portfolyo panelini çalıştıran servisleri buradan yönet.</p>
            </div>
        </div>

        <div class="integration-grid">
            <?php foreach ($integrations as $integration): ?>
                <?php
                    $key = $integration['integration_key'];
                    $defaults = $defaultIntegrations[$key] ?? null;
                    $status = $integration['status'] ?? 'standby';
                    $nameTr = $defaults['name_tr'] ?? $integration['integration_name'];
                    $nameEn = $defaults['name_en'] ?? $integration['integration_name'];
                    $descTr = $defaults['description_tr'] ?? $integration['description'];
                    $descEn = $defaults['description_en'] ?? $integration['description'];
                    $endpoint = $integration['endpoint'] ?: 'Ayarlanmamış';
                    $endpointEn = $integration['endpoint'] ?: 'Not configured';
                    $statusTr = $statusLabels[$status]['tr'] ?? ucfirst($status);
                    $statusEn = $statusLabels[$status]['en'] ?? ucfirst($status);
                ?>
                <article class="integration-tile" data-search-item="<?= e($nameTr . ' ' . $nameEn . ' ' . $descTr . ' ' . $descEn . ' ' . $statusTr . ' ' . $statusEn) ?>">
                    <div class="integration-tile__top">
                        <span class="integration-pulse integration-pulse--<?= e($status) ?>"></span>
                        <strong data-i18n-tr="<?= e($nameTr) ?>" data-i18n-en="<?= e($nameEn) ?>"><?= e($nameTr) ?></strong>
                        <em data-i18n-tr="<?= e($statusTr) ?>" data-i18n-en="<?= e($statusEn) ?>"><?= e($statusTr) ?></em>
                    </div>
                    <p data-i18n-tr="<?= e($descTr) ?>" data-i18n-en="<?= e($descEn) ?>"><?= e($descTr) ?></p>
                    <small><span data-i18n-tr="Uç nokta" data-i18n-en="Endpoint">Uç nokta</span>: <span data-i18n-tr="<?= e($endpoint) ?>" data-i18n-en="<?= e($endpointEn) ?>"><?= e($endpoint) ?></span></small>
                    <form method="post" class="integration-actions">
                        <input type="hidden" name="integration_key" value="<?= e($key) ?>">
                        <?= csrf_field() ?>
                        <select class="cc-select" name="status" aria-label="Entegrasyon durumu">
                            <option value="connected" <?= $status === 'connected' ? 'selected' : '' ?> data-i18n-tr="Bağlı" data-i18n-en="Connected">Bağlı</option>
                            <option value="standby" <?= $status === 'standby' ? 'selected' : '' ?> data-i18n-tr="Beklemede" data-i18n-en="Standby">Beklemede</option>
                            <option value="disabled" <?= $status === 'disabled' ? 'selected' : '' ?> data-i18n-tr="Devre Dışı" data-i18n-en="Disabled">Devre Dışı</option>
                        </select>
                        <button type="submit" class="cc-primary-btn" data-i18n-tr="Kaydet" data-i18n-en="Save">Kaydet</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
