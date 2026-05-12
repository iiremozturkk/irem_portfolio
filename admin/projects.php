<?php
// Bu dosya: Portfolyo projelerini ekleme, güncelleme, silme ve listeleme işlemlerini yönetir.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();


// Proje formundaki alanlar için POST verisini veya varsayılan değeri güvenli döndürür.
function project_form_value(string $key): string
{
    $value = $_POST[$key] ?? '';
    if (is_array($value)) {
        $value = reset($value);
    }
    return trim((string)$value);
}

// Boş bırakılan proje bağlantılarını kırık link üretmemek için # değerine çevirir.
function project_url_value(string $key): string
{
    $value = project_form_value($key);
    if ($value === '' || $value === '#') {
        return $value;
    }
    return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
}

// Proje işlemlerinden sonra kullanıcıyı durum parametresiyle liste sayfasına döndürür.
function redirect_projects(string $status = ''): void
{
    $suffix = $status !== '' ? '?' . $status : '';
    header('Location: projects.php' . $suffix);
    exit;
}

// Proje ekleme, güncelleme ve silme işlemleri action değerine göre ayrılır.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        reject_invalid_csrf();
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'create' || $action === 'update') {
        $requiredValues = [
            project_form_value('title'),
            project_form_value('code_name'),
            project_form_value('short_description'),
            project_form_value('description'),
            project_form_value('tech_stack'),
        ];
        if (in_array('', $requiredValues, true)) {
            redirect_projects('error=missing');
        }
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare('INSERT INTO projects (title, code_name, short_description, description, tech_stack, image, github_url, live_url, featured, sort_order) VALUES (:title, :code_name, :short_description, :description, :tech_stack, :image, :github_url, :live_url, :featured, :sort_order)');
        $stmt->execute([
            ':title' => project_form_value('title'),
            ':code_name' => project_form_value('code_name'),
            ':short_description' => project_form_value('short_description'),
            ':description' => project_form_value('description'),
            ':tech_stack' => project_form_value('tech_stack'),
            ':image' => project_form_value('image') ?: 'assets/images/project-portfolio.svg',
            ':github_url' => project_url_value('github_url'),
            ':live_url' => project_url_value('live_url'),
            ':featured' => isset($_POST['featured']) ? 1 : 0,
            ':sort_order' => max(0, (int)($_POST['sort_order'] ?? 0)),
        ]);
        redirect_projects('saved=1');
    }
    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
        $stmt->execute([':id' => (int)($_POST['id'] ?? 0)]);
        redirect_projects('deleted=1');
    }
    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE projects SET title=:title, code_name=:code_name, short_description=:short_description, description=:description, tech_stack=:tech_stack, image=:image, github_url=:github_url, live_url=:live_url, featured=:featured, sort_order=:sort_order WHERE id=:id');
        $stmt->execute([
            ':id' => (int)($_POST['id'] ?? 0),
            ':title' => project_form_value('title'),
            ':code_name' => project_form_value('code_name'),
            ':short_description' => project_form_value('short_description'),
            ':description' => project_form_value('description'),
            ':tech_stack' => project_form_value('tech_stack'),
            ':image' => project_form_value('image') ?: 'assets/images/project-portfolio.svg',
            ':github_url' => project_url_value('github_url'),
            ':live_url' => project_url_value('live_url'),
            ':featured' => isset($_POST['featured']) ? 1 : 0,
            ':sort_order' => max(0, (int)($_POST['sort_order'] ?? 0)),
        ]);
        redirect_projects('saved=1');
    }
    redirect_projects();
}

// Projeler admin listesinde sıralama değerine ve tarihe göre gösterilir.
$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC')->fetchAll();
$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$currentPage = 'projects';
$headerTitle = 'Projeler';
$headerSubtitle = 'Proje yönetim merkezi';
$pageTitle = 'Projects';
include __DIR__ . '/page_head.php';
include __DIR__ . '/partials_nav.php';
?>
<!-- Projeler sayfası: portfolyo kartlarını yöneten CRUD arayüzü. -->
<main class="cc-content">
    <section class="cc-page-stack">
        <?php if (isset($_GET['saved'])): ?><div class="cc-alert cc-alert--success project-notice">Proje kaydedildi.</div><?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?><div class="cc-alert cc-alert--success project-notice">Proje silindi.</div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="cc-alert cc-alert--error project-notice">Lütfen zorunlu proje alanlarını doldurun.</div><?php endif; ?>
        <article class="cc-card form-card" id="create">
            <div class="cc-card__head small-head"><h2>Yeni Proje Ekle</h2></div>
            <form method="POST" class="cc-form cc-form--grid">
                <input type="hidden" name="action" value="create">
                <?= csrf_field() ?>
                <label>Başlık<input name="title" required></label>
                <label>Kod Adı<input name="code_name" required></label>
                <label>Kısa Açıklama<input name="short_description" required></label>
                <label>Teknolojiler<input name="tech_stack" required></label>
                <label>Görsel<input name="image" value="assets/images/project-portfolio.svg"></label>
                <label>GitHub URL<input name="github_url"></label>
                <label>Canlı URL<input name="live_url"></label>
                <label>Sıra<input type="number" name="sort_order" value="0" min="0" step="1"></label>
                <label class="wide">Açıklama<textarea name="description" required></textarea></label>
                <label class="checkbox-inline"><input type="checkbox" name="featured" checked> Öne Çıkar</label>
                <button class="cc-primary-btn" type="submit">Projeyi Ekle</button>
            </form>
        </article>

        <?php foreach ($projects as $project): ?>
            <article class="cc-card form-card" id="project-<?= e((string)$project['id']) ?>">
                <div class="cc-card__head small-head"><h2><?= e($project['title']) ?></h2></div>
                <form method="POST" class="cc-form cc-form--grid">
                    <input type="hidden" name="action" value="update">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e((string)$project['id']) ?>">
                    <label>Başlık<input name="title" value="<?= e($project['title']) ?>" required></label>
                    <label>Kod Adı<input name="code_name" value="<?= e($project['code_name']) ?>" required></label>
                    <label>Kısa Açıklama<input name="short_description" value="<?= e($project['short_description']) ?>" required></label>
                    <label>Teknolojiler<input name="tech_stack" value="<?= e($project['tech_stack']) ?>" required></label>
                    <label>Görsel<input name="image" value="<?= e($project['image']) ?>"></label>
                    <label>GitHub URL<input name="github_url" value="<?= e($project['github_url']) ?>"></label>
                    <label>Canlı URL<input name="live_url" value="<?= e($project['live_url']) ?>"></label>
                    <label>Sıra<input type="number" name="sort_order" value="<?= e((string)$project['sort_order']) ?>" min="0" step="1"></label>
                    <label class="wide">Açıklama<textarea name="description" required><?= e($project['description']) ?></textarea></label>
                    <label class="checkbox-inline"><input type="checkbox" name="featured" <?= $project['featured'] ? 'checked' : '' ?>> Öne Çıkar</label>
                    <div class="cc-form__actions">
                        <button class="cc-primary-btn" type="submit">Kaydet</button>
                    </div>
                </form>
                <form method="POST" onsubmit="return confirm('Bu projeyi silmek istiyor musunuz?')" class="delete-inline">
                    <input type="hidden" name="action" value="delete">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e((string)$project['id']) ?>">
                    <button class="cc-danger-btn" type="submit">Sil</button>
                </form>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</div></div>
<script src="../assets/js/admin-panel.js"></script>
</body>
</html>
