<?php
// Bu dosya: Son mesajlar, projeler ve genel durum için rapor ekranını hazırlar.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

// Rapor sayfasındaki tarihleri okunabilir Türkçe biçimde gösterir.
function report_date(?string $date): string {
    if (!$date) { return 'Yakında'; }
    $months = ['Jan'=>'Oca','Feb'=>'Şub','Mar'=>'Mar','Apr'=>'Nis','May'=>'May','Jun'=>'Haz','Jul'=>'Tem','Aug'=>'Ağu','Sep'=>'Eyl','Oct'=>'Eki','Nov'=>'Kas','Dec'=>'Ara'];
    return strtr(date('d M Y', strtotime($date)), $months);
}

// Rapor kartlarında uzun açıklamaları kısaltır.
function report_short_text(string $text, int $limit = 58): string {
    $plain = trim(strip_tags($text));
    if (function_exists('mb_strlen') && mb_strlen($plain, 'UTF-8') > $limit) {
        return mb_substr($plain, 0, $limit - 3, 'UTF-8') . '...';
    }
    if (!function_exists('mb_strlen') && strlen($plain) > $limit) {
        return substr($plain, 0, $limit - 3) . '...';
    }
    return $plain;
}


// Rapor ekranında proje adlarını tutarlı İngilizce başlıklara dönüştürür.
function report_project_title_en(string $title): string {
    return str_replace(['(Devam Ediyor)', '(Tamamlandı)', '(Planlama)'], ['(In Progress)', '(Completed)', '(Planning)'], $title);
}

// HTML etiketlerini temizleyip rapor özetine uygun düz metin üretir.
function report_clean_text(?string $text): string {
    $plain = trim(strip_tags((string)$text));
    $plain = preg_replace('/\s+/u', ' ', $plain);
    return $plain ?: 'Detay bilgisi henüz girilmedi.';
}

$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$projectCount = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$messageCount = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$featuredCount = (int)$pdo->query('SELECT COUNT(*) FROM projects WHERE featured = 1')->fetchColumn();
$skillCount = (int)$pdo->query('SELECT COUNT(*) FROM skills')->fetchColumn();
// Rapor ekranı için son eklenen projeler ayrıca listelenir.
$recentProjects = $pdo->query('SELECT title, tech_stack, description, updated_at, created_at, featured FROM projects ORDER BY updated_at DESC, created_at DESC LIMIT 6')->fetchAll();
// Rapor ekranı için en son gelen mesajlardan kısa bir özet alınır.
$recentMessages = $pdo->query('SELECT name, subject, message, created_at FROM messages ORDER BY created_at DESC LIMIT 4')->fetchAll();

$activityTimeline = [];
foreach ($recentMessages as $message) {
    $messageBody = report_clean_text($message['message'] ?? '');
    $subject = report_clean_text($message['subject'] ?? 'Konu yok');
    $sender = report_clean_text($message['name'] ?? 'Ziyaretçi');
    $activityTimeline[] = [
        'time' => date('H:i', strtotime($message['created_at'])),
        'title_tr' => 'Yeni iletişim mesajı alındı: ' . $subject,
        'title_en' => 'New contact message received: ' . $subject,
        'desc_tr' => $sender . ' iletişim formu üzerinden şu mesajı gönderdi: “' . $messageBody . '”. Bu kayıt rapor akışına eklendi ve admin panelinden takip edilebilir.',
        'desc_en' => $sender . ' sent this message through the contact form: “' . $messageBody . '”. This record was added to the report timeline and can be tracked from the admin panel.',
        'search' => $sender . ' ' . $subject . ' ' . $messageBody,
    ];
}
foreach ($recentProjects as $project) {
    $projectTitle = report_clean_text($project['title'] ?? 'Proje');
    $projectTitleEn = report_project_title_en($projectTitle);
    $techStack = report_clean_text($project['tech_stack'] ?? 'Teknoloji bilgisi girilmedi.');
    $description = report_clean_text($project['description'] ?? 'Açıklama bilgisi henüz girilmedi.');
    $updatedDate = report_date($project['updated_at'] ?? $project['created_at'] ?? null);
    $activityTimeline[] = [
        'time' => $updatedDate,
        'title_tr' => $projectTitle . ' projesi rapor zaman çizelgesine eklendi',
        'title_en' => $projectTitleEn . ' project was added to the report timeline',
        'desc_tr' => 'Bu proje için kullanılan teknolojiler: ' . $techStack . '. Proje açıklaması: ' . $description . ' Son güncelleme tarihi: ' . $updatedDate . '.',
        'desc_en' => 'Technologies used for this project: ' . $techStack . '. Project description: ' . $description . ' Last update date: ' . $updatedDate . '.',
        'search' => $projectTitle . ' ' . $techStack . ' ' . $description,
    ];
}
$activityTimeline = array_slice($activityTimeline, 0, 8);

$currentPage = 'reports';
$headerTitle = 'Raporlar';
$headerSubtitle = 'İrem Öztürk rapor merkezi';
$pageTitle = 'Raporlar';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Raporlar sayfası: son aktiviteler ve özet metrikler tek ekranda toplanır. -->
<main class="cc-content reports-page-only">
    <section class="cc-page-stack reports-page-stack">
        <article class="cc-card table-card" data-search-list>
            <div class="cc-card__head small-head"><h2>Son Proje Güncellemeleri</h2><a href="projects.php">Projeler</a></div>
            <div class="project-table project-table--irem-ozturk report-project-table">
                <div class="project-table__head"><span>Proje Adı</span><span>Durum</span><span>Teknolojiler</span><span>Son Güncelleme</span><span>Rapor</span></div>
                <?php if (!$recentProjects): ?><p class="empty-state">Kayıt yok.</p><?php endif; ?>
                <?php foreach ($recentProjects as $project): ?>
                    <?php
                        $projectTitle = report_clean_text($project['title'] ?? 'Proje');
                        $projectTitleEn = report_project_title_en($projectTitle);
                        $techStack = report_clean_text($project['tech_stack'] ?? 'Teknoloji bilgisi girilmedi.');
                        $description = report_clean_text($project['description'] ?? 'Açıklama bilgisi henüz girilmedi.');
                        $updatedDate = report_date($project['updated_at'] ?? $project['created_at'] ?? null);
                        $statusTr = $project['featured'] ? 'Öne Çıkan' : 'Devam Ediyor';
                        $statusEn = $project['featured'] ? 'Featured' : 'In Progress';
                        $reportTr = $projectTitle . ' projesi için rapor özeti: Bu çalışma ' . $techStack . ' teknolojileri kullanılarak geliştirildi. Proje açıklaması: ' . $description . ' Son güncelleme tarihi ' . $updatedDate . ' olarak kaydedildi ve rapor sayfasında izlenebilir durumdadır.';
                        $reportEn = 'Report summary for ' . $projectTitleEn . ': This project was developed using ' . $techStack . ' technologies. Project description: ' . $description . ' The last update date is recorded as ' . $updatedDate . ', and the project is visible on the reports page.';
                    ?>
                    <div class="project-table__row" data-search-item="<?= e(strtolower($projectTitle . ' ' . $techStack . ' ' . $description)) ?>">
                        <span class="project-title"><?= e($projectTitle) ?></span>
                        <span class="project-status"><i class="dot <?= $project['featured'] ? 'success' : 'danger' ?>"></i><span data-i18n-tr="<?= e($statusTr) ?>" data-i18n-en="<?= e($statusEn) ?>"><?= e($statusTr) ?></span></span>
                        <span><?= e($techStack) ?></span>
                        <span class="project-date"><?= e($updatedDate) ?></span>
                        <span class="project-report-text" data-i18n-tr="<?= e($reportTr) ?>" data-i18n-en="<?= e($reportEn) ?>"><?= e($reportTr) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="cc-card timeline-card report-timeline-card" data-search-list>
            <div class="cc-card__head small-head"><h2>Aktivite Zaman Çizelgesi</h2><a href="dashboard.php">Dashboard</a></div>
            <div class="timeline-list">
                <?php if (!$activityTimeline): ?><p class="empty-state">Henüz aktivite yok.</p><?php endif; ?>
                <?php foreach ($activityTimeline as $item): ?>
                    <div class="timeline-item" data-search-item="<?= e(strtolower($item['search'])) ?>">
                        <time><?= e($item['time']) ?></time><div class="timeline-item__dot"></div>
                        <div class="timeline-item__content">
                            <strong data-i18n-tr="<?= e($item['title_tr']) ?>" data-i18n-en="<?= e($item['title_en']) ?>"><?= e($item['title_tr']) ?></strong>
                            <span data-i18n-tr="<?= e($item['desc_tr']) ?>" data-i18n-en="<?= e($item['desc_en']) ?>"><?= e($item['desc_tr']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <section class="cc-grid cc-grid--hero reports-metric-grid">
            <article class="cc-card metric-card" data-search-item="rapor toplam proje projects">
                <div class="metric-card__label">Toplam Proje</div>
                <div class="metric-card__value"><?= e((string)$projectCount) ?></div>
                <div class="metric-card__meta">Rapor Özeti</div>
                <div class="metric-card__glyph">◫</div>
            </article>
            <article class="cc-card metric-card" data-search-item="rapor toplam mesaj messages">
                <div class="metric-card__label">Toplam Mesaj</div>
                <div class="metric-card__value"><?= e((string)$messageCount) ?></div>
                <div class="metric-card__meta"><?= e((string)$unreadCount) ?> okunmamış mesaj var</div>
                <div class="metric-card__glyph">✉</div>
            </article>
            <article class="cc-card metric-card" data-search-item="rapor featured öne çıkan proje">
                <div class="metric-card__label">Öne Çıkan Proje</div>
                <div class="metric-card__value"><?= e((string)$featuredCount) ?></div>
                <div class="metric-card__meta">Son mesajlar ve proje verileri rapor için hazır.</div>
                <div class="metric-card__glyph">◎</div>
            </article>
            <article class="cc-card metric-card" data-search-item="rapor yetenek skills">
                <div class="metric-card__label">Yetenek</div>
                <div class="metric-card__value"><?= e((string)$skillCount) ?></div>
                <div class="metric-card__meta">Rapor Özeti</div>
                <div class="metric-card__glyph">▥</div>
            </article>
        </section>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
