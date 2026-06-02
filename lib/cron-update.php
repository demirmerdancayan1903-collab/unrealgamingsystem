<?php
/**
 * GameMonetize - Otomatik Güncelleme (Cron)
 * Her gün çalıştırılır, sadece YENİ oyunları ekler.
 *
 * Hosting cPanel cron örneği (her gün 03:00):
 *   0 3 * * * /usr/bin/php /home/kullanici/public_html/cron-update.php
 *
 * Vercel için: vercel.json'a cron ekle (Pro plan gerektirir)
 */

// Cron dışından erişimi engelle (CLI veya gizli key ile)
$cli = (php_sapi_name() === 'cli');
$key_ok = isset($_GET['key']) && $_GET['key'] === 'cron_gizli_key'; // ← değiştir
if (!$cli && !$key_ok) {
    http_response_code(403);
    die('Yetkisiz.');
}

// ── VERİTABANI AYARLARI ──────────────────────────────────────────────────────
$db_host = 'localhost';
$db_name = 'veritabani_adi';   // ← değiştir
$db_user = 'kullanici';        // ← değiştir
$db_pass = 'sifre';            // ← değiştir
// ─────────────────────────────────────────────────────────────────────────────

set_time_limit(120);

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    log_yaz("HATA: Veritabanı bağlantısı - " . $e->getMessage());
    exit(1);
}

// Son 2 günün yeni oyunlarını çek
$feed_url = 'https://gamemonetize.com/rssfeed.php?format=json&category=All&type=html5&popularity=newest&company=All&amount=100';

$json = @file_get_contents($feed_url);
if (!$json) {
    log_yaz("HATA: Feed çekilemedi.");
    exit(1);
}

$oyunlar = json_decode($json, true);
if (!$oyunlar) {
    log_yaz("HATA: JSON parse edilemedi.");
    exit(1);
}

$stmt = $pdo->prepare("
    INSERT IGNORE INTO games (gm_id, title, description, thumbnail, game_url, category, tags, width, height)
    VALUES (:gm_id, :title, :description, :thumbnail, :game_url, :category, :tags, :width, :height)
");

$eklenen = 0;
foreach ($oyunlar as $oyun) {
    $gm_id = $oyun['id'] ?? $oyun['guid'] ?? '';
    $url   = $oyun['url'] ?? $oyun['link'] ?? '';
    if (empty($gm_id) || empty($url)) continue;

    $tags = is_array($oyun['tags'] ?? null)
                ? implode(',', $oyun['tags'])
                : ($oyun['tags'] ?? '');

    $stmt->execute([
        ':gm_id'       => $gm_id,
        ':title'       => $oyun['title']       ?? '',
        ':description' => $oyun['description'] ?? '',
        ':thumbnail'   => $oyun['thumb']       ?? $oyun['thumbnail'] ?? '',
        ':game_url'    => $url,
        ':category'    => $oyun['category']    ?? 'All',
        ':tags'        => $tags,
        ':width'       => (int)($oyun['width']  ?? 800),
        ':height'      => (int)($oyun['height'] ?? 600),
    ]);

    if ($stmt->rowCount() > 0) $eklenen++;
}

log_yaz("Cron tamamlandı. Yeni oyun: $eklenen");
echo "OK: $eklenen yeni oyun eklendi.\n";

function log_yaz($mesaj) {
    $log = __DIR__ . '/cron.log';
    file_put_contents($log, date('Y-m-d H:i:s') . " - $mesaj\n", FILE_APPEND);
}
