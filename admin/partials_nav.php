<?php
// Bu dosya: Admin panelinin sol menüsü, üst barı ve bildirim alanını oluşturan ortak parça.
// Ortak navigasyon parçası kullanılmadan önce sayfa varsayılanları hazırlanır.
$currentPage = $currentPage ?? 'dashboard';
$unreadCount = $unreadCount ?? 0;
$notificationItems = [];
$notificationCount = 0;
$systemPercent = $systemPercent ?? 100;
$systemLabel = $systemLabel ?? 'OPTIMAL';
$serverName = $serverName ?? ($_SERVER['SERVER_NAME'] ?? 'TR-01');
$uptimeDays = $uptimeDays ?? max(1, (int)floor((time() - strtotime('2026-01-01')) / 86400));
$headerTitle = $headerTitle ?? ucfirst($currentPage);
$headerSubtitle = $headerSubtitle ?? 'İrem Öztürk Control Center';
$searchPlaceholder = $searchPlaceholder ?? 'Arama yapın...';
$adminName = $_SESSION['admin_username'] ?? 'admin';
$initials = strtoupper(substr($adminName, 0, 1) . substr($adminName, 1, 1));
// Sol menüdeki ana admin sayfaları tek dizi üzerinden üretilir.
$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => '▦'],
    'projects' => ['label' => 'Projeler', 'href' => 'projects.php', 'icon' => '⧉'],
    'messages' => ['label' => 'Mesajlar', 'href' => 'messages.php', 'icon' => '✉', 'badge' => $unreadCount],
    'analytics' => ['label' => 'Analitik', 'href' => 'analytics.php', 'icon' => '▥'],
    'settings' => ['label' => 'Ayarlar', 'href' => 'settings.php', 'icon' => '⚙'],
];
// Daha seyrek kullanılan bölümler hızlı erişim listesinde tutulur.
$quickItems = [
    ['label' => 'Takvim', 'href' => 'calendar.php', 'icon' => '🗓'],
    ['label' => 'Dosyalar', 'href' => 'files.php', 'icon' => '🗂'],
    ['label' => 'Ekip', 'href' => 'team.php', 'icon_html' => '<svg class="cc-users-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8.2 11.4a3.6 3.6 0 1 0 0-7.2 3.6 3.6 0 0 0 0 7.2Zm7.8.2a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM8.2 13.1c-3.7 0-6.2 1.85-6.2 4.6v.55c0 .86.69 1.55 1.55 1.55h9.3a1.55 1.55 0 0 0 1.55-1.55v-.55c0-2.75-2.5-4.6-6.2-4.6Zm7.8.45c-.54 0-1.05.05-1.53.15 1.19.91 1.93 2.2 1.93 4v.55c0 .54-.13 1.05-.36 1.5h4.41A1.55 1.55 0 0 0 22 18.2v-.4c0-2.55-2.42-4.25-6-4.25Z"/></svg>'],
    ['label' => 'Raporlar', 'href' => 'reports.php', 'icon' => '🧾'],
    ['label' => 'Entegrasyonlar', 'href' => 'integrations.php', 'icon' => '⟳'],
];
?>
<!-- Admin arayüzünün ana kabuğu: sol menü ve içerik alanı burada birleşir. -->
<div class="cc-shell">
    <!-- Sol menü: marka, ana navigasyon, hızlı erişim ve sistem durumu. -->
    <aside class="cc-sidebar">
        <div class="cc-brand">
            <div class="cc-brand__icon"><img src="../assets/images/io-logo-red.png" alt="İrem Öztürk logosu"></div>
            <div>
                <strong>İREM ÖZTÜRK</strong>
                <small>CONTROL CENTER</small>
            </div>
        </div>

        <nav class="cc-menu">
            <?php foreach ($navItems as $key => $item): ?>
                <a class="cc-menu__item <?= $currentPage === $key ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>">
                    <span class="cc-menu__icon"><?= $item['icon_html'] ?? e($item['icon']) ?></span>
                    <span><?= e($item['label']) ?></span>
                    <?php if (!empty($item['badge'])): ?><em class="cc-badge"><?= e((string)$item['badge']) ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="cc-sidebar__section-title">Hızlı Erişim</div>
        <nav class="cc-menu cc-menu--small">
            <?php foreach ($quickItems as $item): ?>
                <a class="cc-menu__item <?= basename($_SERVER['SCRIPT_NAME']) === $item['href'] ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>">
                    <span class="cc-menu__icon"><?= $item['icon_html'] ?? e($item['icon']) ?></span>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="cc-statusbox">
            <span class="cc-statusbox__label">Sistem Durumu</span>
            <strong><?= e($systemLabel) ?></strong>
            <div class="cc-mini-radar">
                <span></span><span></span><span></span>
            </div>
            <p>Sunucu: <?= e($serverName) ?></p>
            <p>Çalışma Süresi: <?= e((string)$uptimeDays) ?>g</p>
        </div>
    </aside>

    <div class="cc-main-shell">
        <!-- Üst bar: arama, bildirim, dil değiştirme ve hızlı proje ekleme kontrolleri. -->
        <header class="cc-topbar">
            <form class="cc-search" onsubmit="return false;">
                <span class="cc-search__icon">⌕</span>
                <input id="globalSearch" type="search" placeholder="<?= e($searchPlaceholder) ?>" autocomplete="off">
            </form>

            <div class="cc-topbar__actions">
                <a href="../index.php" target="_blank" rel="noopener" class="cc-icon-btn" aria-label="Portfolyo önizlemesi" title="Portfolyo sitesini önizle"><span>↗</span></a>
                <div class="cc-notification-wrap">
                    <button type="button" class="cc-icon-btn cc-notification-btn" id="notificationToggle" aria-label="Bildirimler" aria-expanded="false"><span class="cc-bell-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M12 22a2.75 2.75 0 0 0 2.62-1.95H9.38A2.75 2.75 0 0 0 12 22Zm7.2-5.35-1.55-2.08V10a5.68 5.68 0 0 0-4.45-5.55V3.8a1.2 1.2 0 1 0-2.4 0v.65A5.68 5.68 0 0 0 6.35 10v4.57L4.8 16.65a1.18 1.18 0 0 0 .95 1.9h12.5a1.18 1.18 0 0 0 .95-1.9Z"/></svg></span><em><?= e((string)$notificationCount) ?></em></button>
                    <div class="cc-notification-panel" id="notificationPanel" hidden>
                        <div class="cc-notification-panel__head">
                            <strong>Bildirimler</strong>
                            <small>Canlı panel</small>
                        </div>
                        <?php if (empty($notificationItems)): ?>
                            <p class="cc-notification-empty">Yeni bildirim yok.</p>
                        <?php else: ?>
                            <?php foreach ($notificationItems as $notice): ?>
                                <a class="cc-notification-item" href="<?= e($notice['href']) ?>">
                                    <span><?= e($notice['type']) ?></span>
                                    <strong><?= e($notice['text']) ?></strong>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="messages.php" class="cc-icon-btn" aria-label="Mesajlar"><span>✉</span><em><?= e((string)$unreadCount) ?></em></a>
                <button type="button" class="cc-icon-btn cc-lang-btn" id="languageToggle" aria-label="Dil değiştir" title="Türkçe / English"><span>TR</span></button>
                <div class="cc-userbox">
                    <div class="cc-avatar"><img src="../assets/images/io-logo-red.png" alt="İrem Öztürk logosu"></div>
                    <div>
                        <strong>İrem Öztürk</strong>
                        <small>Yönetici</small>
                    </div>
                </div>
                <a href="projects.php#create" class="cc-primary-btn">＋ Yeni Proje</a>
            </div>
        </header>
