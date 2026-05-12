<?php
// Bu dosya: Öne çıkan projeleri JSON formatında döndüren API uç noktası.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

// Ana sayfada gösterilecek öne çıkan projeler sıralı şekilde çekilir.
try {
    $stmt = $pdo->query('SELECT id, title, code_name, short_description, description, tech_stack, image, github_url, live_url FROM projects WHERE featured = 1 ORDER BY sort_order ASC, created_at DESC');
    echo json_encode([
        'success' => true,
        'projects' => $stmt->fetchAll(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Projects could not be loaded.',
    ], JSON_UNESCAPED_UNICODE);
}
?>
