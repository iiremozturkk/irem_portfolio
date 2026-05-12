<?php
// Bu dosya: Admin panelindeki ziyaretçi istatistikleri ve grafik verilerini hazırlayan sayfa.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();
$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$allowedRanges = [
    7 => ['tr' => 'Son 7 Gün', 'en' => 'Last 7 Days'],
    15 => ['tr' => 'Son 15 Gün', 'en' => 'Last 15 Days'],
    30 => ['tr' => 'Son 30 Gün', 'en' => 'Last 30 Days'],
];
$rangeDays = isset($_GET['days']) ? (int)$_GET['days'] : 30;
if (!array_key_exists($rangeDays, $allowedRanges)) { $rangeDays = 30; }
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_stats (id INT PRIMARY KEY DEFAULT 1, total_views INT NOT NULL DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_daily_stats (visit_date DATE PRIMARY KEY, views INT NOT NULL DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $visitorTotal = (int)$pdo->query('SELECT COALESCE(total_views, 0) FROM visitor_stats WHERE id = 1')->fetchColumn();
    $dailyRows = $pdo->query("SELECT visit_date, views FROM visitor_daily_stats WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL " . ($rangeDays - 1) . " DAY) ORDER BY visit_date ASC")->fetchAll();
} catch (Throwable $e) {
    $visitorTotal = 0;
    $dailyRows = [];
}
$dailyMap = [];
foreach ($dailyRows as $row) { $dailyMap[$row['visit_date']] = (int)$row['views']; }
$series = [];
for ($i = $rangeDays - 1; $i >= 0; $i--) { $series[] = max(0, $dailyMap[date('Y-m-d', strtotime('-' . $i . ' days'))] ?? 0); }
$todayViews = end($series) ?: 0;
$weekViews = array_sum(array_slice($series, -7));
$currentPage = 'analytics';
$headerTitle = 'Analitik';
$headerSubtitle = 'Canlı ziyaretçi verileri';
$searchPlaceholder = 'Analitik içinde ara...';
$pageTitle = 'Analitik';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Analitik sayfası: seçilen aralığa göre ziyaret verileri ve canlı durum kartları. -->
<main class="cc-content">
    <section class="cc-grid simple-grid-3">
        <article class="cc-card">
            <h2 data-i18n-tr="Toplam Ziyaretçi" data-i18n-en="Total Visitors">Toplam Ziyaretçi</h2>
            <p class="stat-big"><?= number_format($visitorTotal, 0, ',', '.') ?></p>
            <p data-i18n-tr="Portfolyo ana sayfasından canlı çekiliyor" data-i18n-en="Live data from the portfolio homepage">Portfolyo ana sayfasından canlı çekiliyor</p>
        </article>
        <article class="cc-card">
            <h2 data-i18n-tr="Bugün" data-i18n-en="Today">Bugün</h2>
            <p class="stat-big"><?= number_format($todayViews, 0, ',', '.') ?></p>
            <p data-i18n-tr="Bugünkü gerçek giriş sayısı" data-i18n-en="Real visits recorded today">Bugünkü gerçek giriş sayısı</p>
        </article>
        <article class="cc-card">
            <h2 data-i18n-tr="Son 7 Gün" data-i18n-en="Last 7 Days">Son 7 Gün</h2>
            <p class="stat-big"><?= number_format($weekViews, 0, ',', '.') ?></p>
            <p data-i18n-tr="Haftalık ziyaret toplamı" data-i18n-en="Total visits for the last week">Haftalık ziyaret toplamı</p>
        </article>
    </section>
    <section class="cc-card analytics-live-card">
        <div class="cc-card__head"><h2 data-i18n-tr="Canlı Ziyaretçi Verisi" data-i18n-en="Live Visitor Data">Canlı Ziyaretçi Verisi</h2>
            <form class="cc-period-form" method="get" action="analytics.php">
                <select class="cc-select cc-period-select" name="days" onchange="this.form.submit()" aria-label="Ziyaretçi aralığı">
                    <?php foreach ($allowedRanges as $days => $label): ?>
                        <option value="<?= e((string)$days) ?>" <?= $rangeDays === $days ? 'selected' : '' ?> data-i18n-tr="<?= e($label['tr']) ?>" data-i18n-en="<?= e($label['en']) ?>"><?= e($label['tr']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="visitor-bars" style="--range-days: <?= e((string)$rangeDays) ?>;">
            <?php $max = max(1, max($series)); foreach ($series as $value): ?>
                <span style="height: <?= e((string)max(6, round(($value / $max) * 150))) ?>px" title="<?= e((string)$value) ?> ziyaret"></span>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
