<?php if ( !defined('API_PILOT') ) exit(); ?>
<?php
/**
 * api-connector.php — HTML5 versiyonu
 * Flash (SWF) kaldırıldı, GameMonetize iframe ile oyun gösterir.
 *
 * Beklenen değişkenler (çağıran sayfa tarafından set edilmeli):
 *   $get_game_data['url']    — oyunun iframe URL'si (game_url alanı)
 *   $get_game_data['title']  — oyun adı
 *   $get_game_data['width']  — genişlik (px veya yüzde)
 *   $get_game_data['height'] — yükseklik (px)
 */

$game_url   = htmlspecialchars($get_game_data['url']    ?? '', ENT_QUOTES);
$game_title = htmlspecialchars($get_game_data['title']  ?? 'Oyun', ENT_QUOTES);
$game_w     = !empty($get_game_data['width'])  ? htmlspecialchars($get_game_data['width'])  : '100%';
$game_h     = !empty($get_game_data['height']) ? (int)$get_game_data['height'] . 'px'       : '600px';
?>

<div id="game-container" style="width:<?php echo $game_w; ?>; height:<?php echo $game_h; ?>; position:relative; background:#000; border-radius:8px; overflow:hidden;">

    <?php if ($game_url): ?>
        <iframe
            id="game-iframe"
            src="<?php echo $game_url; ?>"
            title="<?php echo $game_title; ?>"
            width="100%"
            height="100%"
            frameborder="0"
            scrolling="no"
            allowfullscreen
            allow="autoplay; fullscreen *; geolocation; microphone; camera; midi; monetization; xr-spatial-tracking; gamepad; gyroscope; accelerometer; xr"
            style="display:block; border:none;">
        </iframe>

        <!-- Tam Ekran Butonu -->
        <button onclick="toggleFullscreen()" style="
            position:absolute; bottom:10px; right:10px;
            background:rgba(0,0,0,0.6); color:#fff;
            border:none; border-radius:4px;
            padding:6px 12px; cursor:pointer; font-size:13px; z-index:99;">
            ⛶ Tam Ekran
        </button>

    <?php else: ?>
        <div style="color:#fff; text-align:center; padding:40px;">
            Oyun yüklenemedi.
        </div>
    <?php endif; ?>

</div>

<script>
function toggleFullscreen() {
    var el = document.getElementById('game-iframe');
    if (!el) return;
    if (el.requestFullscreen)       el.requestFullscreen();
    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
    else if (el.mozRequestFullScreen)    el.mozRequestFullScreen();
}
</script>
