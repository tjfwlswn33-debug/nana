<?php
include_once __DIR__ . '/_common.php';
if (!defined('_GNUBOARD_')) exit;
define('_INDEX_', true);

// Capability check used by the skin. It does not start a second music player.
if (isset($_GET['nana_shell_probe']) && $_GET['nana_shell_probe'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['nana_shell' => 1]);
    exit;
}
require_once __DIR__ . '/nana-shell.lib.php';
$main_link = get_main_link();
$nana_route = nana_shell_route(isset($_GET['nana_route']) ? $_GET['nana_route'] : '', G5_BBS_URL);
if ($nana_route !== '') { $main_link = $nana_route; }
// Keep the outer frame on mobile too: the supplied mobile redirect bypassed BGM.
include_once __DIR__ . '/head.sub.php';
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/index.css">', 0);
?>
<?php if (!empty($config['cf_bgm'])): ?>
<div id="site_bgm_box">
    <iframe src="<?php echo htmlspecialchars(G5_URL . '/bgm.php?action=play', ENT_QUOTES, 'UTF-8'); ?>" name="bgm_frame" id="bgm_frame" title="배경음악 재생" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" allow="autoplay; encrypted-media"></iframe>
</div>
<?php endif; ?>
<div id="wrapper">
    <iframe src="<?php echo htmlspecialchars($main_link, ENT_QUOTES, 'UTF-8'); ?>" name="frm_main" id="main" title="개인홈 본문" frameborder="0" marginwidth="0" marginheight="0" scrolling="auto" allowtransparency="true"></iframe>
</div>
<?php
if (is_file(G5_PATH . '/custommenu/index.php')) {
    include G5_PATH . '/custommenu/index.php';
}
?>
<script>
document.addEventListener('click', function (event) {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    var link = event.target.closest ? event.target.closest('a[href]') : null;
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
    try {
        var url = new URL(link.href, window.location.href);
        var bbs = new URL(<?php echo json_encode(G5_BBS_URL . '/', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, window.location.href);
        var main = new URL(<?php echo json_encode(G5_URL . '/main.php', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, window.location.href);
        var board = url.pathname === bbs.pathname + 'board.php' || url.pathname === bbs.pathname + 'write.php';
        if (url.origin !== window.location.origin || (!board && url.pathname !== main.pathname)) return;
        if (board && !/^[a-zA-Z0-9_]{1,20}$/.test(url.searchParams.get('bo_table') || '')) return;
        document.getElementById('main').src = url.href;
        event.preventDefault();
    } catch (error) { /* Preserve normal navigation for other links. */ }
});
document.addEventListener('keydown', function (event) {
    if (event.key === 'F5' || event.keyCode === 116) {
        try {
            document.getElementById('main').contentWindow.location.reload();
            event.preventDefault();
        } catch (error) { /* Let the browser reload if a frame is unavailable. */ }
    }
});
</script>
<?php include_once G5_PATH . '/tail.sub.php'; ?>
