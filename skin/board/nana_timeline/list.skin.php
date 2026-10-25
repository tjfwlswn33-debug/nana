<?php
if (!defined('_GNUBOARD_')) exit;
require_once __DIR__ . '/skin.lib.php';
$nana_timeline = true;
?>
<section class="nana-skin" aria-label="<?= nana_e($board['bo_subject']) ?>">
    <header class="nana-heading"><p class="nana-eyebrow"><?= $nana_timeline ? 'WORLD CHRONICLE' : 'CHARACTER ARCHIVE' ?></p><h2><?= nana_e($board['bo_subject']) ?></h2><p><?= $nana_timeline ? '지나온 시간들이 모여 지금의 세계가 됩니다.' : '초상을 누르면, 그 사람의 이야기가 펼쳐집니다.' ?></p></header>
    <div class="nana-toolbar"><span><?= number_format((int) $total_count) ?>개의 기록</span><div><?php if (!empty($admin_href)): ?><a href="<?= nana_e($admin_href) ?>">게시판 설정</a><?php endif; ?><?php if (!empty($write_href)): ?><a class="nana-button" href="<?= nana_e($write_href) ?>">+ 기록 남기기</a><?php endif; ?></div></div>
    <?php if (!empty($is_category)): ?><nav class="nana-categories" aria-label="분류"><ul><?= $category_option ?></ul></nav><?php endif; ?>
    <div class="<?= $nana_timeline ? 'nana-timeline' : 'nana-grid' ?>">
    <?php foreach ((array) $list as $row):
        $locked = strpos(isset($row['wr_option']) ? $row['wr_option'] : '', 'secret') !== false || !empty($row['icon_secret']);
        $title = $locked ? '비공개 기록' : nana_plain(isset($row['wr_subject']) ? $row['wr_subject'] : $row['subject']);
        $image = (!$nana_timeline && !$locked && function_exists('get_file')) ? nana_image(get_file($bo_table, $row['wr_id'])) : '';
    ?>
        <?php if ($nana_timeline): ?>
        <article class="nana-event"><div class="nana-year"><span>YEAR</span><strong><?= $locked ? '—' : nana_e(isset($row['wr_1']) ? $row['wr_1'] : '') ?></strong></div><div class="nana-event-body"><p class="nana-eyebrow"><?= $locked ? 'PRIVATE' : nana_e(isset($row['wr_2']) ? $row['wr_2'] : '') ?></p><h3><a href="<?= nana_e($row['href']) ?>"><?= nana_e($title) ?> ↗</a></h3><p class="nana-excerpt"><?= $locked ? '열람 권한이 필요한 기록입니다.' : nana_e(function_exists('cut_str') ? cut_str(nana_plain(isset($row['wr_content']) ? $row['wr_content'] : ''), 180, '…') : nana_plain(isset($row['wr_content']) ? $row['wr_content'] : '')) ?></p></div></article>
        <?php else: ?>
        <a class="nana-card" href="<?= nana_e($row['href']) ?>"><div class="nana-portrait"><?php if ($image): ?><img src="<?= nana_e($image) ?>" alt="<?= nana_e($title) ?>의 초상" loading="lazy"><?php else: ?><span class="nana-placeholder"><span aria-hidden="true"><?= $locked ? '◇' : '✳' ?></span><?= $locked ? '비공개 기록' : '초상 준비 중' ?></span><?php endif; ?><span class="nana-open" aria-hidden="true">↗</span></div><div class="nana-caption"><h3><?= nana_e($title) ?></h3><p><?= $locked ? '열람 권한이 필요합니다' : nana_e(isset($row['wr_2']) ? $row['wr_2'] : '') ?></p></div></a>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($list)): ?><p class="nana-empty">아직 기록이 없습니다. 첫 이야기를 남겨보세요.</p><?php endif; ?>
    </div>
    <div class="nana-pagination"><?= isset($write_pages) ? $write_pages : '' ?></div>
    <form class="nana-search" method="get" action="<?= nana_e(G5_BBS_URL . '/board.php') ?>"><input type="hidden" name="bo_table" value="<?= nana_e($bo_table) ?>"><input type="hidden" name="sfl" value="wr_subject"><label for="nana-search">제목 검색</label><input id="nana-search" type="search" name="stx" maxlength="100" value="<?= nana_e(isset($stx) ? $stx : '') ?>" placeholder="찾고 싶은 이야기"><button type="submit">검색</button><?php if (!empty($stx)): ?><a href="<?= nana_e(G5_BBS_URL . '/board.php?bo_table=' . rawurlencode($bo_table)) ?>">전체 목록</a><?php endif; ?></form>
</section>
