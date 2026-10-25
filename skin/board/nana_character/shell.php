<?php
if (!defined('_GNUBOARD_')) exit;
if (defined('G5_URL')):
?>
<script src="<?= nana_e($board_skin_url) ?>/shell.js?v=4" data-nana-shell-url="<?= nana_e(rtrim(G5_URL, '/') . '/index.php') ?>" defer></script>
<?php endif; ?>
