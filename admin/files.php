<?php
// Bu dosya: CV ve medya dosyalarını yükleme, listeleme ve meta verilerini saklama sayfası.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'files';
$headerTitle = 'Files';
$headerSubtitle = 'Portfolio file center';
$searchPlaceholder = 'Search files...';
$pageTitle = 'Files';

$uploadDir = __DIR__ . '/../assets/uploads/admin-files';
$uploadUrl = '../assets/uploads/admin-files/';
$metaFile = $uploadDir . '/files.json';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// Yüklenen dosyaların açıklama ve etiket gibi meta bilgileri JSON dosyasından okunur.
$loadMeta = function () use ($metaFile) {
    if (!is_file($metaFile)) return [];
    $json = json_decode((string)file_get_contents($metaFile), true);
    return is_array($json) ? $json : [];
};
// Dosya meta bilgileri okunabilir JSON formatında saklanır.
$saveMeta = function (array $items) use ($metaFile) {
    file_put_contents($metaFile, json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
};

$notice = '';
$error = '';

// Dosya yükleme ve silme aksiyonları aynı POST akışı içinde ayrıştırılır.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }
    $action = $_POST['action'] ?? '';
    $items = $loadMeta();

    if ($action === 'upload') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Portfolio');
        $file = $_FILES['portfolio_file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = 'Dosya yüklenemedi.';
        } else {
            $allowed = ['pdf','png','jpg','jpeg','webp','zip','txt','doc','docx'];
            $original = basename($file['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $error = 'Bu dosya türü desteklenmiyor.';
            } elseif (($file['size'] ?? 0) > 10 * 1024 * 1024) {
                $error = 'Dosya 10MB sınırını aşmamalı.';
            } else {
                $safeBase = preg_replace('/[^a-zA-Z0-9-_]+/', '-', pathinfo($original, PATHINFO_FILENAME));
                $safeBase = trim($safeBase, '-') ?: 'portfolio-file';
                $stored = $safeBase . '-' . date('Ymd-His') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $stored)) {
                    $items[] = [
                        'title' => $title !== '' ? $title : pathinfo($original, PATHINFO_FILENAME),
                        'category' => $category !== '' ? $category : 'Portfolio',
                        'file' => $stored,
                        'original' => $original,
                        'size' => (int)$file['size'],
                        'created_at' => date('Y-m-d H:i'),
                    ];
                    $saveMeta($items);
                    $notice = 'Dosya başarıyla eklendi.';
                } else {
                    $error = 'Dosya klasöre taşınamadı.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $idx = (int)($_POST['index'] ?? -1);
        if (isset($items[$idx])) {
            $path = $uploadDir . '/' . $items[$idx]['file'];
            if (is_file($path)) unlink($path);
            unset($items[$idx]);
            $saveMeta($items);
            $notice = 'Dosya kaldırıldı.';
        }
    }
}

$uploadedFiles = $loadMeta();
$staticFiles = [
    ['title' => 'CV - English', 'title_tr' => 'CV - İngilizce', 'title_en' => 'CV - English', 'category' => 'CV', 'file' => '../assets/cv/irem-ozturk-cv-en.pdf', 'size' => is_file(__DIR__ . '/../assets/cv/irem-ozturk-cv-en.pdf') ? filesize(__DIR__ . '/../assets/cv/irem-ozturk-cv-en.pdf') : 0, 'created_at' => 'Ready'],
    ['title' => 'CV - Türkçe', 'title_tr' => 'CV - Türkçe', 'title_en' => 'CV - Turkish', 'category' => 'CV', 'file' => '../assets/cv/irem-ozturk-cv-tr.pdf', 'size' => is_file(__DIR__ . '/../assets/cv/irem-ozturk-cv-tr.pdf') ? filesize(__DIR__ . '/../assets/cv/irem-ozturk-cv-tr.pdf') : 0, 'created_at' => 'Hazır'],
];
$totalSize = array_sum(array_column($uploadedFiles, 'size')) + array_sum(array_column($staticFiles, 'size'));
// Dosya boyutları KB/MB gibi kullanıcı dostu birimlere çevrilir.
$formatSize = function ($bytes) {
    $bytes = (int)$bytes;
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
};

include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Dosyalar sayfası: CV ve medya yükleme, listeleme ve silme işlemleri. -->
<main class="cc-content files-admin-page">
    <section class="cc-page-stack">
        <article class="cc-card files-hero" data-search-item="Dosya merkezi Files hub portfolio cv upload documents">
            <div class="cc-card__head">
                <div>
                    <h2>Dosya Merkezi</h2>
                    <p>CV, proje belgeleri ve portfolyo dosyalarını buradan yönet.</p>
                </div>
            </div>
            <?php if ($notice): ?><div class="cc-alert cc-alert--ok"><?= e($notice) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="cc-alert cc-alert--error"><?= e($error) ?></div><?php endif; ?>
            <div class="file-metrics">
                <div><span>Toplam Dosya</span><strong><?= count($staticFiles) + count($uploadedFiles) ?></strong></div>
                <div><span>Yüklenen</span><strong><?= count($uploadedFiles) ?></strong></div>
                <div><span>Depolama</span><strong><?= e($formatSize($totalSize)) ?></strong></div>
            </div>
        </article>

        <article class="cc-card file-upload-card" data-search-item="Yeni dosya yükle upload portfolio file">
            <h2>Yeni Dosya Yükle</h2>
            <form method="post" enctype="multipart/form-data" class="file-upload-form">
                <input type="hidden" name="action" value="upload">
                <?= csrf_field() ?>
                <label>
                    <span>Dosya Başlığı</span>
                    <input name="title" type="text" placeholder="Örn. Proje Özeti" data-i18n-tr="Örn. Proje Özeti" data-i18n-en="e.g. Project Brief">
                </label>
                <label>
                    <span>Kategori</span>
                    <select name="category">
                        <option>Portfolio</option>
                        <option>CV</option>
                        <option>Project</option>
                        <option>Report</option>
                        <option>Asset</option>
                    </select>
                </label>
                <label class="file-picker">
                    <span class="file-picker__label" data-i18n-tr="Dosya Seç" data-i18n-en="Choose File">Dosya Seç</span>
                    <span class="file-picker__control">
                        <span class="file-picker__button" data-i18n-tr="Dosya Seç" data-i18n-en="Choose File">Dosya Seç</span>
                        <span class="file-picker__name" data-empty-tr="Dosya seçilmedi" data-empty-en="No file chosen">Dosya seçilmedi</span>
                        <input name="portfolio_file" type="file" required>
                    </span>
                </label>
                <button class="cc-action-btn" type="submit">Yükle</button>
            </form>
        </article>

        <article class="cc-card file-list-card" data-search-item="Dosyalar files cv documents">
            <div class="small-head"><h2>Aktif Dosyalar</h2></div>
            <div class="file-grid">
                <?php foreach ($staticFiles as $file): ?>
                    <div class="file-item" data-search-item="<?= e($file['title'] . ' ' . $file['category']) ?>">
                        <div class="file-item__icon">PDF</div>
                        <div>
                            <strong data-i18n-tr="<?= e($file['title_tr'] ?? $file['title']) ?>" data-i18n-en="<?= e($file['title_en'] ?? $file['title']) ?>"><?= e($file['title_tr'] ?? $file['title']) ?></strong>
                            <span><?= e($file['category']) ?> · <?= e($formatSize($file['size'])) ?></span>
                        </div>
                        <a class="file-link" href="<?= e($file['file']) ?>" target="_blank" rel="noopener">Aç</a>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($uploadedFiles as $index => $file): ?>
                    <div class="file-item" data-search-item="<?= e(($file['title'] ?? '') . ' ' . ($file['category'] ?? '') . ' ' . ($file['original'] ?? '')) ?>">
                        <div class="file-item__icon"><?= e(strtoupper(pathinfo($file['file'], PATHINFO_EXTENSION))) ?></div>
                        <div>
                            <strong><?= e($file['title'] ?? $file['original']) ?></strong>
                            <span><?= e($file['category'] ?? 'Portfolio') ?> · <?= e($formatSize($file['size'] ?? 0)) ?> · <?= e($file['created_at'] ?? '') ?></span>
                        </div>
                        <a class="file-link" href="<?= e($uploadUrl . $file['file']) ?>" target="_blank" rel="noopener">Aç</a>
                        <form method="post" onsubmit="return confirm('Bu dosya kaldırılsın mı?');">
                            <input type="hidden" name="action" value="delete">
                            <?= csrf_field() ?>
                            <input type="hidden" name="index" value="<?= (int)$index ?>">
                            <button class="file-delete" type="submit">Kaldır</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
