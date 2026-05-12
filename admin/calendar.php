<?php
// Bu dosya: Admin takvim notlarını kaydetme, listeleme ve seçili günü gösterme sayfası.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$pdo->exec("CREATE TABLE IF NOT EXISTS calendar_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_date DATE NOT NULL UNIQUE,
    note_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Not ekleme ve silme işlemleri POST isteğiyle işlenir.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }
    $date = $_POST['note_date'] ?? '';
    $text = trim($_POST['note_text'] ?? '');
    if (preg_match('/^2026-\d{2}-\d{2}$/', $date)) {
        if ($text === '') {
            $stmt = $pdo->prepare('DELETE FROM calendar_notes WHERE note_date = ?');
            $stmt->execute([$date]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO calendar_notes (note_date, note_text) VALUES (?, ?) ON DUPLICATE KEY UPDATE note_text = VALUES(note_text), updated_at = CURRENT_TIMESTAMP');
            $stmt->execute([$date, $text]);
        }
    }
    header('Location: calendar.php?selected=' . urlencode($date) . '&saved=1');
    exit;
}

$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'calendar';
$headerTitle = 'Calendar';
$headerSubtitle = '2026 not takvimi';
$pageTitle = 'Calendar';
$selectedDate = $_GET['selected'] ?? date('Y-m-d');
if (!preg_match('/^2026-\d{2}-\d{2}$/', $selectedDate)) {
    $selectedDate = '2026-01-01';
}

$notes = [];
// Yıla ait tüm takvim notları tarih anahtarıyla hızlı erişim için belleğe alınır.
foreach ($pdo->query("SELECT note_date, note_text FROM calendar_notes WHERE note_date BETWEEN '2026-01-01' AND '2026-12-31'") as $row) {
    $notes[$row['note_date']] = $row['note_text'];
}
$selectedNote = $notes[$selectedDate] ?? '';
$monthNames = [1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'];
$dayNames = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz'];
// Takvim notu boşsa kullanıcıya anlaşılır bir varsayılan metin gösterilir.
$calendar_note_title = function (string $text): string {
    return function_exists('mb_substr') ? mb_substr($text, 0, 80, 'UTF-8') : substr($text, 0, 80);
};
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Takvim sayfası: 2026 içindeki günlere not bağlamak için kullanılır. -->
<main class="cc-content">
    <article class="cc-card calendar-shell">
        <div class="cc-card__head calendar-head">
            <div>
                <h2>2026 TAKVİMİ</h2>
                <p><strong><?= e((string)count($notes)) ?></strong> kayıtlı not · Not eklemek için bir güne tıkla</p>
            </div>
        </div>
        <?php if (!empty($_GET['saved'])): ?>
            <div class="cc-alert-success">Takvim notu kaydedildi.</div>
        <?php endif; ?>
        <div class="calendar-layout">
            <section class="year-grid" aria-label="2026 calendar">
                <?php for ($month=1; $month<=12; $month++):
                    $first = new DateTime(sprintf('2026-%02d-01', $month));
                    $daysInMonth = (int)$first->format('t');
                    $offset = (int)$first->format('N') - 1;
                ?>
                    <div class="month-card">
                        <h3><?= e($monthNames[$month]) ?></h3>
                        <div class="month-days month-days--names">
                            <?php foreach ($dayNames as $dn): ?><span><?= e($dn) ?></span><?php endforeach; ?>
                        </div>
                        <div class="month-days">
                            <?php for ($i=0; $i<$offset; $i++): ?><span class="day-cell is-empty"></span><?php endfor; ?>
                            <?php for ($day=1; $day<=$daysInMonth; $day++):
                                $date = sprintf('2026-%02d-%02d', $month, $day);
                                $hasNote = isset($notes[$date]);
                            ?>
                                <a class="day-cell <?= $date === $selectedDate ? 'is-selected' : '' ?> <?= $hasNote ? 'has-note' : '' ?>" href="calendar.php?selected=<?= e($date) ?>" title="<?= $hasNote ? e($calendar_note_title($notes[$date])) : 'Not ekle' ?>">
                                    <?= e((string)$day) ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </section>
        </div>
        <?php if (isset($_GET['selected'])): ?>
            <div class="calendar-note-overlay" role="dialog" aria-modal="true" aria-labelledby="calendar-note-title">
                <aside class="note-panel note-panel--modal">
                    <div class="note-panel__top">
                        <div>
                            <h3 id="calendar-note-title">Seçili Gün</h3>
                            <strong><?= e(date('d.m.Y', strtotime($selectedDate))) ?></strong>
                        </div>
                        <a class="note-panel__close" href="calendar.php" aria-label="Not panelini kapat">×</a>
                    </div>
                    <form method="post" class="cc-form calendar-note-form">
                        <input type="hidden" name="note_date" value="<?= e($selectedDate) ?>">
                        <?= csrf_field() ?>
                        <label for="note_text">Takvim Notu</label>
                        <textarea id="note_text" name="note_text" rows="10" placeholder="Bu güne not, teslim tarihi, hatırlatma veya fikir yaz..." autofocus><?= e($selectedNote) ?></textarea>
                        <button class="cc-primary-btn" type="submit">Notu Kaydet</button>
                        <?php if ($selectedNote !== ''): ?><small>Notu silmek için alanı boş bırakıp kaydedebilirsin.</small><?php endif; ?>
                    </form>
                </aside>
            </div>
        <?php endif; ?>
    </article>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body></html>
