<?php if (!defined('_GNUBOARD_')) exit; ?>
<div class="nana-sidebar" role="navigation" aria-label="개인홈 메뉴">
    <span class="nana-sidebar-title">Directory</span>
    <span class="nana-sidebar-note">이야기의 갈피</span>
    <ul><?php foreach (nana_navigation($nana_settings, $bo_table, $board['bo_subject']) as $nana_nav): ?><li><a target="_self" href="<?= nana_e($nana_nav['url']) ?>"><?= nana_e($nana_nav['label']) ?></a></li><?php endforeach; ?></ul>
</div>
