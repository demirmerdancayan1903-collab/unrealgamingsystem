<?php
/**
 * GameMonetize - Tüm Oyunları Import Et
 * Bu dosyayı TARAYICIdan bir kere çalıştır, sonra sil!
 * Örnek: https://sitenadres.com/import-games.php?key=GIZLI_ANAHTAR
 */

// Güvenlik anahtarı - değiştir!
define('IMPORT_KEY', 'gizli123');

if (!isset($_GET['key']) || $_GET['key'] !== IMPORT_KEY) {
    die('Yetkisiz erişim.');
}

// ── VERİTABANI AYARLARI ──────────────────────────────────────────────────────
$db_host = 'localhost';
$db_name = 'veritabani_adi';   // ← değiştir
$db_user = 'kullanici';        // ← değiştir
$db_pass = 'sifre';            // ← değiştir
// ─────────────────────────────────────────────────────────────────────────────

set_time_limit(300);
$baslangic = microtime(true);

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// games tablosuna gerekli sütunlar yoksa ekle
$pdo->exec("
    ALTER TABLE games
        ADD COLUMN IF NOT EXISTS gm_id VARCHAR(100) UNIQUE,
        ADD COLUMN IF NOT EXISTS title VARCHAR(255),
        ADD COLUMN IF NOT EXISTS description TEXT,
        ADD COLUMN IF NOT EXISTS thumbnail VARCHAR(500),
        ADD COLUMN IF NOT EXISTS game_url VARCHAR(500),
        ADD COLUMN IF NOT EXISTS category VARCHAR(100),
        ADD COLUMN IF NOT EXISTS tags VARCHAR(500),
        ADD COLUMN IF NOT EXISTS width INT DEFAULT 800,
        ADD COLUMN IF NOT EXISTS height INT DEFAULT 600,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
");

// GameMonetize JSON feed - tüm oyunları çek
$feed_url = 'https://gamemonetize.com/rssfeed.php?format=json&category=All&type=html5&popularity=newest&company=All&amount=All';

echo "<h2>GameMonetize Oyun Import</h2>";
echo "Feed çekiliyor...<br>";
flush();

$json = @file_get_contents($feed_url);
if (!$json) {
    die("HATA: Feed çekilemedi. PHP'de allow_url_fopen açık olmalı.");
}

$oyunlar = json_decode($json, true);
if (!$oyunlar || !is_array($oyunlar)) {
    die("HATA: JSON parse edilemedi.");
}

echo "Toplam oyun sayısı: <strong>" . count($oyunlar) . "</strong><br><br>";
flush();

$eklenen  = 0;
$guncellenen = 0;
$hata = 0;

$stmt = $pdo->prepare("
    INSERT INTO games (gm_id, title, description, thumbnail, game_url, category, tags, width, height)
    VALUES (:gm_id, :title, :description, :thumbnail, :game_url, :category, :tags, :width, :height)
    ON DUPLICATE KEY UPDATE
        title       = VALUES(title),
        description = VALUES(description),
        thumbnail   = VALUES(thumbnail),
        game_url    = VALUES(game_url),
        category    = VALUES(category),
        tags        = VALUES(tags),
        width       = VALUES(width),
        height      = VALUES(height)
");

foreach ($oyunlar as $oyun) {
    try {
        $gm_id      = $oyun['id']          ?? $oyun['guid']        ?? '';
        $title      = $oyun['title']       ?? '';
        $desc       = $oyun['description'] ?? '';
        $thumb      = $oyun['thumb']       ?? $oyun['thumbnail']   ?? '';
        $url        = $oyun['url']         ?? $oyun['link']        ?? '';
        $category   = $oyun['category']    ?? 'All';
        $tags       = is_array($oyun['tags'] ?? null)
                        ? implode(',', $oyun['tags'])
                        : ($oyun['tags'] ?? '');
        $width      = (int)($oyun['width']  ?? 800);
        $height     = (int)($oyun['height'] ?? 600);

        if (empty($gm_id) || empty($url)) continue;

        $stmt->execute([
            ':gm_id'       => $gm_id,
            ':title'       => $title,
            ':description' => $desc,
            ':thumbnail'   => $thumb,
            ':game_url'    => $url,
            ':category'    => $category,
            ':tags'        => $tags,
            ':width'       => $width,
            ':height'      => $height,
        ]);

        $eklenen++;

    } catch (Exception $e) {
        $hata++;
    }
}

$sure = round(microtime(true) - $baslangic, 2);

echo "<strong style='color:green'>✓ Tamamlandı!</strong><br>";
echo "Eklenen/Güncellenen: <strong>$eklenen</strong><br>";
echo "Hata: <strong>$hata</strong><br>";
echo "Süre: <strong>{$sure} saniye</strong><br><br>";
echo "<strong style='color:red'>⚠ Güvenlik için bu dosyayı şimdi sil!</strong>";
