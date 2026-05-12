<?php
// Bu dosya: Gelen iletişim mesajlarını listeleyen ve okundu durumunu güncelleyen sayfa.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();
// Mesajlar okundu/okunmadı durumuna göre POST isteğiyle güncellenir.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }

    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'mark_read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = :id')->execute([':id' => $id]);
    }
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: messages.php'); exit;
}
// Mesajlar en yeni kayıt üstte olacak şekilde listelenir.
$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'messages';
$headerTitle = 'Mesajlar';
$headerSubtitle = 'İletişim kutusu';
$pageTitle = 'Messages';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Mesajlar sayfası: iletişim formundan gelen kayıtlar buradan takip edilir. -->
<main class="cc-content">
    <section class="cc-page-stack">
        <?php if (!$messages): ?><article class="cc-card empty-state">Mesaj bulunamadı.</article><?php endif; ?>
        <?php foreach ($messages as $message): ?>
            <article class="cc-card message-item <?= $message['is_read'] ? 'read' : 'unread' ?>">
                <div class="message-item__head">
                    <div>
                        <strong><?= e($message['subject']) ?></strong>
                        <span><?= e($message['name']) ?> · <?= e($message['email']) ?></span>
                    </div>
                    <time><?= e($message['created_at']) ?></time>
                </div>
                <p><?= nl2br(e($message['message'])) ?></p>
                <form method="POST" class="inline-actions">
                    <input type="hidden" name="id" value="<?= e((string)$message['id']) ?>">
                    <?= csrf_field() ?>
                    <?php if (!(int)$message['is_read']): ?><button class="cc-primary-btn" name="action" value="mark_read">Okundu İşaretle</button><?php endif; ?>
                    <button class="cc-danger-btn" name="action" value="delete" onclick="return confirm('Mesajı silmek istiyor musunuz?')">Sil</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body>
</html>
