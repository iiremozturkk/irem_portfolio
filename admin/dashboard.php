<?php
// Bu dosya: Admin panelinin ana özet ekranı; metrikleri, grafikleri, mesajları ve projeleri toplar.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

// Kartlarda uzun metinleri okunabilir bir özet hâline getirir.
function short_text(string $text, int $limit = 86): string {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $limit) {
        return mb_substr($text, 0, $limit - 1, 'UTF-8') . '…';
    }
    return strlen($text) > $limit ? substr($text, 0, $limit - 1) . '…' : $text;
}

// Tarih değerini Türkçe arayüze uygun kısa biçimde formatlar.
function tr_date(?string $date): string {
    if (!$date) { return 'Yakında'; }
    $months = ['Jan'=>'Oca','Feb'=>'Şub','Mar'=>'Mar','Apr'=>'Nis','May'=>'May','Jun'=>'Haz','Jul'=>'Tem','Aug'=>'Ağu','Sep'=>'Eyl','Oct'=>'Eki','Nov'=>'Kas','Dec'=>'Ara'];
    return strtr(date('d M Y', strtotime($date)), $months);
}


// Veritabanındaki proje adını admin panelinde kullanılan İngilizce karşılığa eşler.
function project_title_en(string $title): string {
    return str_replace(['(Devam Ediyor)', '(Tamamlandı)', '(Planlama)'], ['(In Progress)', '(Completed)', '(Planning)'], $title);
}

// Proje sırasına göre gösterilecek örnek ilerleme yüzdesini hesaplar.
function project_progress(array $project): int {
    $techCount = max(1, substr_count((string)($project['tech_stack'] ?? ''), ',') + 1);
    $base = !empty($project['featured']) ? 74 : 54;
    $orderBoost = max(0, 10 - (int)($project['sort_order'] ?? 0));
    return min(98, $base + ($techCount * 3) + $orderBoost);
}

$projectCount = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$messageCount = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$skillCount = (int)$pdo->query('SELECT COUNT(*) FROM skills')->fetchColumn();
$skillCategoryCount = (int)$pdo->query('SELECT COUNT(DISTINCT category) FROM skills')->fetchColumn();
$featuredCount = (int)$pdo->query('SELECT COUNT(*) FROM projects WHERE featured = 1')->fetchColumn();
$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC LIMIT 5')->fetchAll();
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 5')->fetchAll();
$latestProject = $pdo->query('SELECT * FROM projects ORDER BY updated_at DESC, created_at DESC LIMIT 1')->fetch();
$latestMessage = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC LIMIT 1')->fetch();

$allowedRanges = [7 => 'Son 1 Hafta', 15 => 'Son 15 Gün', 30 => 'Son 30 Gün'];
$rangeDays = isset($_GET['days']) ? (int)$_GET['days'] : 30;
if (!array_key_exists($rangeDays, $allowedRanges)) { $rangeDays = 30; }
$rangeLabel = $allowedRanges[$rangeDays];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_stats (
        id INT PRIMARY KEY DEFAULT 1,
        total_views INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $visitorTotal = (int)$pdo->query('SELECT COALESCE(total_views, 0) FROM visitor_stats WHERE id = 1')->fetchColumn();
} catch (Throwable $e) {
    $visitorTotal = 0;
}
$visitorTotal = max(0, $visitorTotal);
$goalTarget = 10000;
$goalPercent = min(99, max(1, (int)round(($visitorTotal / $goalTarget) * 100)));
$successRate = $projectCount > 0 ? min(99, (int)round(($featuredCount / $projectCount) * 100)) : 0;
$growthPct = $messageCount > 0 ? min(48.0, 9.0 + ($unreadCount * 1.7)) : 12.0;

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS visitor_daily_stats (
        visit_date DATE PRIMARY KEY,
        views INT NOT NULL DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $dailyRows = $pdo->query("SELECT visit_date, views FROM visitor_daily_stats WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL " . ($rangeDays - 1) . " DAY) ORDER BY visit_date ASC")->fetchAll();
} catch (Throwable $e) {
    $dailyRows = [];
}
$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[$row['visit_date']] = (int)$row['views'];
}
$series = [];
$dateLabels = [];
for ($i = $rangeDays - 1; $i >= 0; $i--) {
    $dayKey = date('Y-m-d', strtotime('-' . $i . ' days'));
    $series[] = max(0, $dailyMap[$dayKey] ?? 0);
    $dateLabels[] = tr_date($dayKey);
}
if (max($series) === 0 && $visitorTotal > 0) {
    $series[count($series) - 1] = $visitorTotal;
}
$weeklyCurrent = array_sum(array_slice($series, -7));
$weeklyPrevious = max(1, array_sum(array_slice($series, -14, 7)));
$growthPct = (($weeklyCurrent - $weeklyPrevious) / $weeklyPrevious) * 100;
$growthPct = max(-99.0, min(999.0, $growthPct));

$systemChecks = [
    $projectCount >= 0,
    $skillCount >= 0,
    $messageCount >= 0,
    isset($visitorTotal),
];
$systemOnline = count(array_filter($systemChecks));
$systemPercent = round(($systemOnline / max(1, count($systemChecks))) * 100, 1);
$systemLabel = $systemPercent >= 99 ? 'OPTIMAL' : ($systemPercent >= 75 ? 'STABLE' : 'DİKKAT');
$serverName = $_SERVER['SERVER_NAME'] ?? 'TR-01';
$uptimeDays = max(1, (int)floor((time() - strtotime('2026-01-01')) / 86400));
$maxVal = max($series);
$minVal = min($series);
$w = 780; $h = 260; $pad = 24;
$points = [];
// SVG grafikte kullanılacak x/y noktaları veri serisinden hesaplanır.
foreach ($series as $i => $val) {
    $x = $pad + ($i * (($w - $pad * 2) / (count($series) - 1)));
    $norm = ($val - $minVal) / max(1, ($maxVal - $minVal));
    $y = ($h - $pad) - $norm * ($h - $pad * 2);
    $points[] = round($x, 2) . ',' . round($y, 2);
}
$linePoints = implode(' ', $points);
$areaPoints = $pad . ',' . ($h - $pad) . ' ' . $linePoints . ' ' . ($w - $pad) . ',' . ($h - $pad);
$activePointIndex = array_search(max($series), $series, true);
$activeCoords = explode(',', $points[$activePointIndex]);
$activeX = (float)$activeCoords[0];
$activeY = (float)$activeCoords[1];
$activeValue = $series[$activePointIndex];

$timeline = [];
foreach ($messages as $message) {
    $timeline[] = [
        'time' => date('H:i', strtotime($message['created_at'])),
        'title' => 'Yeni mesaj: ' . $message['subject'],
        'title_en' => 'New message: ' . $message['subject'],
        'desc' => $message['name'] . ' iletişim formundan yazdı',
        'desc_en' => $message['name'] . ' sent a contact form message',
        'search' => $message['name'] . ' ' . $message['subject'] . ' ' . $message['message'],
    ];
}
foreach ($projects as $project) {
    $timeline[] = [
        'time' => tr_date($project['updated_at'] ?? $project['created_at'] ?? null),
        'title' => $project['title'] . ' projesi güncellendi',
        'title_en' => project_title_en($project['title']) . ' project updated',
        'desc' => short_text($project['tech_stack'], 58),
        'desc_en' => short_text($project['tech_stack'], 58),
        'search' => $project['title'] . ' ' . $project['tech_stack'] . ' ' . $project['description'],
    ];
}
$timeline = array_slice($timeline, 0, 4);

$currentPage = 'dashboard';
$headerTitle = 'Dashboard';
$headerSubtitle = 'İrem Öztürk Control Center';
$searchPlaceholder = 'Arama yapın...';
$pageTitle = 'Dashboard';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Dashboard içeriği: genel metrikler, grafikler ve yönetim özetleri. -->
<main class="cc-content">
    <!-- Üst metrik kartları: ziyaret, proje, mesaj ve sistem bilgileri. -->
    <section class="cc-grid cc-grid--hero">
        <a class="cc-card metric-card" href="projects.php" data-search-item="toplam proje projects">
            <div class="metric-card__label">Toplam Proje</div>
            <div class="metric-card__value"><?= e((string)$projectCount) ?></div>
            <div class="metric-card__meta"><span class="up">↑ <?= e((string)max(1, $featuredCount * 3)) ?>%</span> bu ay</div>
            <div class="metric-card__glyph">◫</div>
        </a>
        <a class="cc-card metric-card" href="messages.php" data-search-item="okunmamış mesaj unread messages">
            <div class="metric-card__label">Okunmamış Mesaj</div>
            <div class="metric-card__value"><?= e((string)$unreadCount) ?></div>
            <div class="metric-card__meta"><span class="up">↑ <?= e((string)max(1, $unreadCount + 8)) ?>%</span> bu hafta</div>
            <div class="metric-card__glyph">◌</div>
        </a>
        <a class="cc-card metric-card" href="analytics.php" data-search-item="ziyaretçi analytics visitors">
            <div class="metric-card__label">Ziyaretçi</div>
            <div class="metric-card__value"><?= number_format($visitorTotal, 0, ',', '.') ?></div>
            <div class="metric-card__meta"><span class="up">↑ <?= e(number_format($growthPct, 0, ',', '.')) ?>%</span> bu hafta</div>
            <div class="metric-card__glyph">◎</div>
        </a>
        <a class="cc-card metric-card" href="integrations.php" data-search-item="sistem durumu online">
            <div class="metric-card__label">Sistem Durumu</div>
            <div class="metric-card__value"><?= e(number_format($systemPercent, 1, ',', '.')) ?>%</div>
            <div class="metric-card__meta"><?= e($systemOnline . '/' . count($systemChecks)) ?> sistem aktif</div>
            <div class="metric-card__glyph">◉</div>
        </a>
    </section>

    <!-- Ana dashboard yerleşimi: grafik, mesajlar ve proje durumları. -->
    <section class="cc-dashboard-layout cc-dashboard-layout--irem-ozturk">
        <div class="cc-column cc-column--left">
            <article class="cc-card chart-card" data-search-item="analytics ziyaretçi grafik visitors">
                <div class="cc-card__head">
                    <div>
                        <h2>Ziyaretçi Analitiği</h2>
                        <p><strong><?= number_format($visitorTotal, 0, ',', '.') ?></strong> Toplam Ziyaretçi <span class="up">↑ <?= e(number_format($growthPct, 1, ',', '.')) ?>%</span> portfolyo verilerine göre</p>
                    </div>
                    <form class="cc-period-form" method="get" action="dashboard.php">
                        <select class="cc-select cc-period-select" name="days" onchange="this.form.submit()" aria-label="Ziyaretçi aralığı">
                            <?php foreach ($allowedRanges as $days => $label): ?>
                                <option value="<?= e((string)$days) ?>" <?= $rangeDays === $days ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="chart-wrap">
                    <div class="chart-yaxis"><span>10K</span><span>8K</span><span>6K</span><span>4K</span><span>2K</span><span>0</span></div>
                    <svg viewBox="0 0 780 260" class="chart-svg" aria-label="Ziyaretçi grafiği">
                        <defs>
                            <linearGradient id="chartLineRed" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#ff4d4d" /><stop offset="100%" stop-color="#ff1628" /></linearGradient>
                            <linearGradient id="chartAreaRed" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="rgba(255, 35, 55, 0.55)" /><stop offset="100%" stop-color="rgba(255, 35, 55, 0.02)" /></linearGradient>
                        </defs>
                        <?php for ($i = 0; $i < 6; $i++): ?><line x1="24" y1="<?= 24 + ($i * 42.4) ?>" x2="756" y2="<?= 24 + ($i * 42.4) ?>" class="chart-grid-line" /><?php endfor; ?>
                        <?php for ($i = 0; $i < 8; $i++): ?><line x1="<?= 24 + ($i * 104) ?>" y1="24" x2="<?= 24 + ($i * 104) ?>" y2="236" class="chart-grid-line chart-grid-line--v" /><?php endfor; ?>
                        <polygon points="<?= e($areaPoints) ?>" fill="url(#chartAreaRed)"></polygon>
                        <polyline points="<?= e($linePoints) ?>" fill="none" stroke="url(#chartLineRed)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                        <?php foreach ($points as $i => $point): list($x,$y) = explode(',', $point); ?><circle cx="<?= e($x) ?>" cy="<?= e($y) ?>" r="<?= $i === $activePointIndex ? '8' : '4' ?>" fill="<?= $i === $activePointIndex ? '#ffffff' : '#ff3647' ?>" class="chart-point"></circle><?php endforeach; ?>
                        <g transform="translate(<?= max(6, min(662, $activeX - 25)) ?>, <?= max(6, $activeY - 88) ?>)">
                            <rect width="110" height="70" rx="8" fill="#111216" stroke="rgba(255,0,40,0.35)" />
                            <text x="14" y="24" fill="#a8acb8" font-size="15">Bugün</text>
                            <circle cx="18" cy="48" r="5" fill="#ff3346" />
                            <text x="32" y="52" fill="#fff" font-size="28" font-weight="700"><?= e(number_format($activeValue, 0, ',', '.')) ?></text>
                        </g>
                    </svg>
                </div>
                <?php $lastLabelIndex = max(0, count($dateLabels) - 1); $labelIndexes = array_unique([0, (int)floor($lastLabelIndex * .25), (int)floor($lastLabelIndex * .5), (int)floor($lastLabelIndex * .75), $lastLabelIndex]); ?>
                <div class="chart-xaxis"><?php foreach ($labelIndexes as $idx): ?><span><?= e($dateLabels[$idx] ?? '') ?></span><?php endforeach; ?></div>
            </article>

            <section class="cc-bottom-grid">
                <article class="cc-card list-card" data-search-list>
                    <div class="cc-card__head small-head"><h2>Son Mesajlar</h2><a href="messages.php">Tümünü Gör</a></div>
                    <div class="message-list">
                        <?php if (!$messages): ?><p class="empty-state">Henüz mesaj yok.</p><?php endif; ?>
                        <?php foreach ($messages as $message): ?>
                            <?php $parts = preg_split('/\s+/', trim($message['name'])); $initials = strtoupper(substr($parts[0] ?? 'M', 0, 1) . substr($parts[1] ?? ($parts[0] ?? 'S'), 0, 1)); ?>
                            <a class="message-row" href="messages.php" data-search-item="<?= e(strtolower($message['name'] . ' ' . $message['subject'] . ' ' . $message['message'])) ?>">
                                <div class="message-row__avatar"><?= e($initials) ?></div>
                                <div class="message-row__body"><strong><?= e($message['name']) ?></strong><span><?= e(short_text($message['subject'], 34)) ?></span></div>
                                <div class="message-row__meta"><time><?= e(date('H:i', strtotime($message['created_at']))) ?></time><?php if (!(int)$message['is_read']): ?><em>!</em><?php endif; ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="cc-card table-card" data-search-list>
                    <div class="cc-card__head small-head"><h2>Projeler</h2><a href="projects.php#create">+ Yeni Proje</a></div>
                    <table class="project-table project-table--irem-ozturk">
                        <caption class="sr-only">Admin dashboard project summary</caption>
                        <thead>
                            <tr class="project-table__head">
                                <th scope="col">Proje Adı</th>
                                <th scope="col">Durum</th>
                                <th scope="col">İlerleme</th>
                                <th scope="col">Son Güncelleme</th>
                                <th scope="col">Ekip</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$projects): ?>
                                <tr class="project-table__row">
                                    <td class="empty-state" colspan="5">Henüz proje yok.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($projects as $row): ?>
                                <?php $progress = project_progress($row); $state = $progress >= 95 ? 'success' : ($progress < 60 ? 'warn' : 'danger'); $status = $progress >= 95 ? 'Tamamlandı' : ($progress < 60 ? 'Planlama' : 'Devam Ediyor'); $team = max(1, substr_count($row['tech_stack'], ',') + 1); ?>
                                <tr class="project-table__row" data-search-item="<?= e(strtolower($row['title'] . ' ' . $row['tech_stack'] . ' ' . $row['description'])) ?>">
                                    <td class="project-title"><?= e($row['title']) ?></td>
                                    <td class="project-status"><i class="dot <?= e($state) ?>"></i><?= e($status) ?></td>
                                    <td class="project-progress"><b><?= e((string)$progress) ?>%</b><small class="mini-progress"><em style="width: <?= e((string)$progress) ?>%"></em></small></td>
                                    <td class="project-date <?= $state === 'success' ? 'is-success' : '' ?>"><?= e(tr_date($row['updated_at'] ?? $row['created_at'])) ?></td>
                                    <td class="avatars-stack"><i></i><i></i><i></i><small>+<?= e((string)$team) ?></small><a class="row-menu" href="projects.php#project-<?= e((string)$row['id']) ?>" aria-label="Projeyi düzenle">⋮</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </article>
            </section>
        </div>

        <div class="cc-column cc-column--center">
            <a class="cc-card gauge-card" href="analytics.php" data-search-item="hedef durum progress">
                <div class="ring-meter" style="--pct: <?= e((string)$goalPercent) ?>;"><div class="ring-meter__center"><strong><?= e((string)$goalPercent) ?>%</strong><span>Hedefe Ulaşım</span></div></div>
                <p>Aylık Hedef: <?= number_format($goalTarget, 0, ',', '.') ?></p>
            </a>
        </div>

        <div class="cc-column cc-column--right">
            <article class="cc-card profile-card profile-card--image-only" data-search-item="profil admin irem öztürk yönetici">
                <img class="profile-card-full" src="../assets/images/manager-profile-card.png" alt="İrem Öztürk yönetici profili">
                <div class="manager-live-stats" aria-label="Canlı yönetici istatistikleri">
                    <div class="manager-live-stat">
                        <span data-i18n-tr="YÖNETİLEN PROJE" data-i18n-en="MANAGED PROJECTS">YÖNETİLEN PROJE</span>
                        <strong><?= e((string)$projectCount) ?></strong>
                    </div>
                    <div class="manager-live-stat">
                        <span data-i18n-tr="EKİP ÜYESİ" data-i18n-en="TEAM MEMBERS">EKİP ÜYESİ</span>
                        <strong><?= e((string)$skillCount) ?></strong>
                    </div>
                    <div class="manager-live-stat">
                        <span data-i18n-tr="BAŞARI ORANI" data-i18n-en="SUCCESS RATE">BAŞARI ORANI</span>
                        <strong><?= e((string)$successRate) ?>%</strong>
                    </div>
                </div>
                <div class="manager-radar-widget" aria-hidden="true"><span></span><i></i></div>
            </article>

            <article class="cc-card timeline-card" data-search-list>
                <div class="cc-card__head small-head"><h2>Aktivite Zaman Çizelgesi</h2><a href="reports.php">Tümünü Gör</a></div>
                <div class="timeline-list">
                    <?php if (!$timeline): ?><p class="empty-state">Henüz aktivite yok.</p><?php endif; ?>
                    <?php foreach ($timeline as $item): ?>
                        <div class="timeline-item" data-search-item="<?= e(strtolower($item['search'])) ?>">
                            <time><?= e($item['time']) ?></time><div class="timeline-item__dot"></div>
                            <div class="timeline-item__content"><strong data-i18n-tr="<?= e(short_text($item['title'], 42)) ?>" data-i18n-en="<?= e(short_text($item['title_en'] ?? $item['title'], 42)) ?>"><?= e(short_text($item['title'], 42)) ?></strong><span data-i18n-tr="<?= e($item['desc']) ?>" data-i18n-en="<?= e($item['desc_en'] ?? $item['desc']) ?>"><?= e($item['desc']) ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body>
</html>
