<?php
// Bu dosya: Ekip üyelerini ekleme, silme ve kart görünümünde listeleme sayfası.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'team';
$headerTitle = 'Ekip';
$headerSubtitle = 'Portfolyo üretim ekibini yönet';
$searchPlaceholder = 'Ekip içinde ara...';
$pageTitle = 'Ekip';
$notice = null;
$noticeEn = null;
$errorNotice = null;

// Ekip tablosu boşsa arayüzde gösterilecek örnek üyeler.
$defaultMembers = [
    ['name' => 'İrem Öztürk', 'role_tr' => 'Proje Lideri', 'role_en' => 'Project Lead', 'focus_tr' => 'Full-stack geliştirme, tasarım sistemi ve veri tabanı yönetimi', 'focus_en' => 'Full-stack development, design system and database management', 'status' => 'active', 'workload' => 96],
    ['name' => 'Frontend Radar', 'role_tr' => 'Arayüz Modülü', 'role_en' => 'Interface Module', 'focus_tr' => 'Responsive UI, DOM etkileşimleri ve animasyonlar', 'focus_en' => 'Responsive UI, DOM interactions and animations', 'status' => 'active', 'workload' => 88],
    ['name' => 'Backend Core', 'role_tr' => 'Sunucu Modülü', 'role_en' => 'Server Module', 'focus_tr' => 'PHP oturumları, MySQL kayıtları ve güvenli panel akışı', 'focus_en' => 'PHP sessions, MySQL records and secure panel flow', 'status' => 'active', 'workload' => 91],
    ['name' => 'QA Sentinel', 'role_tr' => 'Test ve Kontrol', 'role_en' => 'Testing & Review', 'focus_tr' => 'Form doğrulama, bağlantı kontrolü ve son teslim hazırlığı', 'focus_en' => 'Form validation, link checks and final delivery readiness', 'status' => 'standby', 'workload' => 72],
];

$statusLabels = [
    'active' => ['tr' => 'Aktif', 'en' => 'Active'],
    'standby' => ['tr' => 'Hazır', 'en' => 'Standby'],
    'paused' => ['tr' => 'Duraklatıldı', 'en' => 'Paused'],
];

// Ekip üyeleri için tablo yoksa admin sayfası açılırken oluşturulur.
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_name VARCHAR(120) NOT NULL,
        role_tr VARCHAR(120) NOT NULL,
        role_en VARCHAR(120) NOT NULL,
        focus_tr TEXT NULL,
        focus_en TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        workload INT NOT NULL DEFAULT 80,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $count = (int)$pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
    if ($count === 0) {
        $insertDefault = $pdo->prepare('INSERT INTO team_members (member_name, role_tr, role_en, focus_tr, focus_en, status, workload) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($defaultMembers as $member) {
            $insertDefault->execute([$member['name'], $member['role_tr'], $member['role_en'], $member['focus_tr'], $member['focus_en'], $member['status'], $member['workload']]);
        }
    }

    // Ekip üyesi ekleme ve silme işlemleri POST isteğiyle işlenir.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $name = trim($_POST['member_name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $focus = trim($_POST['focus'] ?? '');
            $status = $_POST['status'] ?? 'active';
            $workload = max(0, min(100, (int)($_POST['workload'] ?? 80)));
            if ($name !== '' && $role !== '' && isset($statusLabels[$status])) {
                $insert = $pdo->prepare('INSERT INTO team_members (member_name, role_tr, role_en, focus_tr, focus_en, status, workload) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $insert->execute([$name, $role, $role, $focus, $focus, $status, $workload]);
                $notice = 'Ekip üyesi eklendi.';
                $noticeEn = 'Team member added.';
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $workload = max(0, min(100, (int)($_POST['workload'] ?? 80)));
            if ($id > 0 && isset($statusLabels[$status])) {
                $update = $pdo->prepare('UPDATE team_members SET status = ?, workload = ? WHERE id = ?');
                $update->execute([$status, $workload, $id]);
                $notice = 'Ekip durumu güncellendi.';
                $noticeEn = 'Team status updated.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $delete = $pdo->prepare('DELETE FROM team_members WHERE id = ?');
                $delete->execute([$id]);
                $notice = 'Ekip üyesi kaldırıldı.';
                $noticeEn = 'Team member removed.';
            }
        }
    }

    $members = $pdo->query('SELECT * FROM team_members ORDER BY status ASC, workload DESC, member_name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Ekip kartları için eksik alanlara güvenli varsayılan değerler atanır.
    $members = array_map(function ($member, $index) {
        return [
            'id' => $index + 1,
            'member_name' => $member['name'],
            'role_tr' => $member['role_tr'],
            'role_en' => $member['role_en'],
            'focus_tr' => $member['focus_tr'],
            'focus_en' => $member['focus_en'],
            'status' => $member['status'],
            'workload' => $member['workload'],
        ];
    }, $defaultMembers, array_keys($defaultMembers));
    $errorNotice = 'Veritabanı bağlantısı kurulamadı; demo ekip listesi gösteriliyor.';
}

$activeCount = count(array_filter($members, fn($m) => ($m['status'] ?? '') === 'active'));
$avgWorkload = count($members) ? (int)round(array_sum(array_map(fn($m) => (int)($m['workload'] ?? 0), $members)) / count($members)) : 0;

include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Ekip sayfası: ekip üyeleri ve rollerinin yönetildiği bölüm. -->
<main class="cc-content team-page">
    <?php if ($notice): ?><div class="cc-alert cc-alert--success" data-i18n-tr="<?= e($notice) ?>" data-i18n-en="<?= e($noticeEn ?? 'Team status updated.') ?>"><?= e($notice) ?></div><?php endif; ?>
    <?php if ($errorNotice): ?><div class="cc-alert" data-i18n-tr="<?= e($errorNotice) ?>" data-i18n-en="Database connection could not be established; the demo team list is shown."><?= e($errorNotice) ?></div><?php endif; ?>

    <section class="cc-grid simple-grid-3">
        <article class="cc-card"><h2 data-i18n-tr="Toplam Ekip" data-i18n-en="Total Team">Toplam Ekip</h2><p class="stat-big"><?= e((string)count($members)) ?></p><p data-i18n-tr="Panelde kayıtlı üye" data-i18n-en="Members registered in the panel">Panelde kayıtlı üye</p></article>
        <article class="cc-card"><h2 data-i18n-tr="Aktif Modül" data-i18n-en="Active Modules">Aktif Modül</h2><p class="stat-big"><?= e((string)$activeCount) ?></p><p data-i18n-tr="Şu anda görevde" data-i18n-en="Currently on duty">Şu anda görevde</p></article>
        <article class="cc-card"><h2 data-i18n-tr="Ortalama Yük" data-i18n-en="Average Load">Ortalama Yük</h2><p class="stat-big"><?= e((string)$avgWorkload) ?>%</p><p data-i18n-tr="Sprint yoğunluğu" data-i18n-en="Sprint intensity">Sprint yoğunluğu</p></article>
    </section>

    <section class="cc-card team-board">
        <div class="cc-card__head">
            <div>
                <h2 data-i18n-tr="Ekip Kontrol Merkezi" data-i18n-en="Team Control Center">Ekip Kontrol Merkezi</h2>
                <p data-i18n-tr="Portfolyo projesindeki görevleri ve ekip durumunu buradan yönet." data-i18n-en="Manage portfolio project roles and team status here.">Portfolyo projesindeki görevleri ve ekip durumunu buradan yönet.</p>
            </div>
        </div>

        <div class="team-grid">
            <?php foreach ($members as $member): ?>
                <?php
                    $status = $member['status'] ?? 'active';
                    $statusTr = $statusLabels[$status]['tr'] ?? ucfirst($status);
                    $statusEn = $statusLabels[$status]['en'] ?? ucfirst($status);
                    $roleTr = $member['role_tr'] ?? '';
                    $roleEn = $member['role_en'] ?? $roleTr;
                    $focusTr = $member['focus_tr'] ?? '';
                    $focusEn = $member['focus_en'] ?? $focusTr;
                ?>
                <article class="team-member-card" data-search-item="<?= e(($member['member_name'] ?? '') . ' ' . $roleTr . ' ' . $roleEn . ' ' . $focusTr . ' ' . $focusEn) ?>">
                    <div class="team-member-card__top">
                        <span class="team-avatar"><?= e(strtoupper(substr($member['member_name'] ?? 'T', 0, 1))) ?></span>
                        <div>
                            <strong><?= e($member['member_name'] ?? '') ?></strong>
                            <em data-i18n-tr="<?= e($roleTr) ?>" data-i18n-en="<?= e($roleEn) ?>"><?= e($roleTr) ?></em>
                        </div>
                        <span class="team-status team-status--<?= e($status) ?>" data-i18n-tr="<?= e($statusTr) ?>" data-i18n-en="<?= e($statusEn) ?>"><?= e($statusTr) ?></span>
                    </div>
                    <p data-i18n-tr="<?= e($focusTr) ?>" data-i18n-en="<?= e($focusEn) ?>"><?= e($focusTr) ?></p>
                    <div class="team-load"><span style="width: <?= e((string)(int)($member['workload'] ?? 0)) ?>%"></span></div>
                    <form method="post" class="team-actions">
                        <input type="hidden" name="action" value="update">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e((string)($member['id'] ?? 0)) ?>">
                        <select class="cc-select" name="status" aria-label="Ekip durumu" data-i18n-tr="Ekip durumu" data-i18n-en="Team status">
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?> data-i18n-tr="Aktif" data-i18n-en="Active">Aktif</option>
                            <option value="standby" <?= $status === 'standby' ? 'selected' : '' ?> data-i18n-tr="Hazır" data-i18n-en="Standby">Hazır</option>
                            <option value="paused" <?= $status === 'paused' ? 'selected' : '' ?> data-i18n-tr="Duraklatıldı" data-i18n-en="Paused">Duraklatıldı</option>
                        </select>
                        <input class="cc-input team-load-input" type="number" name="workload" min="0" max="100" value="<?= e((string)(int)($member['workload'] ?? 0)) ?>" aria-label="İş yükü" data-i18n-tr="İş yükü" data-i18n-en="Workload">
                        <button type="submit" class="cc-primary-btn" data-i18n-tr="Kaydet" data-i18n-en="Save">Kaydet</button>
                    </form>
                    <form method="post" class="team-delete-form">
                        <input type="hidden" name="action" value="delete">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e((string)($member['id'] ?? 0)) ?>">
                        <button type="submit" class="team-delete-btn" data-i18n-tr="Kaldır" data-i18n-en="Remove">Kaldır</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="cc-card team-add-card">
        <h2 data-i18n-tr="Yeni Ekip Üyesi" data-i18n-en="New Team Member">Yeni Ekip Üyesi</h2>
        <form method="post" class="team-add-form">
            <input type="hidden" name="action" value="add">
            <?= csrf_field() ?>
            <input class="cc-input" name="member_name" placeholder="Ad Soyad" data-i18n-tr="Ad Soyad" data-i18n-en="Full Name" required>
            <input class="cc-input" name="role" placeholder="Rol" data-i18n-tr="Rol" data-i18n-en="Role" required>
            <input class="cc-input" name="focus" placeholder="Odak alanı" data-i18n-tr="Odak alanı" data-i18n-en="Focus area">
            <select class="cc-select" name="status" aria-label="Ekip durumu" data-i18n-tr="Ekip durumu" data-i18n-en="Team status">
                <option value="active" data-i18n-tr="Aktif" data-i18n-en="Active">Aktif</option>
                <option value="standby" data-i18n-tr="Hazır" data-i18n-en="Standby">Hazır</option>
                <option value="paused" data-i18n-tr="Duraklatıldı" data-i18n-en="Paused">Duraklatıldı</option>
            </select>
            <input class="cc-input" type="number" name="workload" min="0" max="100" value="80" aria-label="İş yükü" data-i18n-tr="İş yükü" data-i18n-en="Workload">
            <button type="submit" class="cc-primary-btn" data-i18n-tr="Ekle" data-i18n-en="Add">Ekle</button>
        </form>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
