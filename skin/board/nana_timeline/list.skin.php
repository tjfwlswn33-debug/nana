<?php
if (!defined('_GNUBOARD_')) exit;
require_once __DIR__ . '/skin.lib.php';
$nana_settings = nana_load_settings(__DIR__);
$nana_timeline = true;
$nana_current_meta = $nana_timeline ? nana_public_board_meta($bo_table) : null;
$nana_front = empty($stx) && empty($sca) && (!isset($page) || (int) $page <= 1);
?>
<div class="nana-shell <?= !empty($nana_settings['show_sidebar']) ? 'nana-has-sidebar' : '' ?>">
<?php if (!empty($nana_settings['show_sidebar'])) { include __DIR__ . '/sidebar.php'; } ?>
<div class="nana-skin <?= $nana_timeline ? 'nana-chronicle-page' : 'nana-character-page' ?>" aria-label="<?= nana_e($board['bo_subject']) ?>">
    <div class="nana-heading"><p class="nana-eyebrow"><?= $nana_timeline ? 'THE CHRONICLE' : 'OUR LITTLE UNIVERSE' ?></p><h2><?= nana_e($board['bo_subject']) ?></h2><p class="nana-heading-note"><?= $nana_timeline ? '흩어진 순간들을 시간의 순서로 모아 둡니다.' : '같은 세계를 살아가는, 서로 다른 이야기들.' ?></p></div>
    <?php if (!$nana_timeline && $nana_front): ?>
    <section class="nana-introduction" aria-labelledby="nana-story-title"><h3 id="nana-story-title" class="nana-script"><?= nana_e($nana_settings['story_title']) ?></h3><div class="nana-prose"><?= nana_paragraphs($nana_settings['intro']) ?></div><?php if (nana_url($nana_settings['story_link'])): ?><a class="nana-text-link" target="_self" href="<?= nana_e(nana_url($nana_settings['story_link'])) ?>">이야기 더 읽기 <span aria-hidden="true">↗</span></a><?php endif; ?></section>
    <?php $nana_tools = nana_timeline_tools($nana_settings['timeline_board']); ?>
    <section class="nana-era-section" aria-labelledby="nana-era-title">
        <div class="nana-timeline-heading"><div class="nana-section-label"><h3 id="nana-era-title">Timeline</h3><span>우리가 지나온 시간</span></div><div class="nana-timeline-tools">
        <?php if ($nana_tools['list_href']): ?><a class="nana-text-link" target="_self" href="<?= nana_e($nana_tools['list_href']) ?>">전체 타임라인 <span aria-hidden="true">↗</span></a><?php endif; ?>
        <?php if ($nana_tools['write_href']): ?><a class="nana-button nana-add-event" target="_self" href="<?= nana_e($nana_tools['write_href']) ?>">+ 타임라인 추가</a><?php endif; ?>
        </div></div>
        <?php $nana_events = nana_public_events($nana_settings['timeline_board'], $nana_settings['timeline_count']); ?>
        <?php if ($nana_events): ?><div class="nana-eras"><?php foreach ($nana_events as $nana_event): ?><div class="nana-era-item"><a class="nana-era" target="_self" href="<?= nana_e($nana_event['href']) ?>"><div class="nana-era-image"><?php if ($nana_event['image']): ?><img src="<?= nana_e($nana_event['image']) ?>" alt="" loading="lazy"><?php else: ?><span><?= nana_e(nana_field($nana_event, 'wr_1') ?: '—') ?></span><?php endif; ?></div><span class="nana-era-title"><?= nana_e(nana_plain($nana_event['wr_subject'])) ?></span><span class="nana-era-year"><?= nana_e(nana_field($nana_event, 'wr_1')) ?></span></a><?php if ($nana_event['edit_href']): ?><a class="nana-era-edit" target="_self" href="<?= nana_e($nana_event['edit_href']) ?>" aria-label="<?= nana_e(nana_plain($nana_event['wr_subject'])) ?> 수정">수정</a><?php endif; ?></div><?php endforeach; ?></div><?php else: ?><p class="nana-empty nana-empty-inline">아직 공개된 연표가 없습니다.</p><?php endif; ?>
    </section>
    <?php endif; ?>
    <div class="nana-section-label"><h3><?= $nana_timeline ? 'Moments in time' : 'Characters' ?></h3><span><?= number_format((int) $total_count) ?>개의 기록</span></div>
    <div class="nana-toolbar"><div><?php if (!empty($admin_href)): ?><a target="_self" href="<?= nana_e(nana_url($admin_href)) ?>">게시판 설정</a><?php endif; ?><?php if (!empty($write_href)): ?><a class="nana-text-link" target="_self" href="<?= nana_e(nana_url($write_href)) ?>">+ <?= $nana_timeline ? '사건 기록' : '캐릭터 등록' ?></a><?php endif; ?></div></div>
    <?php if (!empty($is_category)): ?><div class="nana-categories" role="navigation" aria-label="분류"><ul><?= $category_option ?></ul></div><?php endif; ?>
    <div class="<?= $nana_timeline ? 'nana-timeline' : 'nana-grid' ?>">
    <?php foreach ((array) $list as $row):
        $locked = strpos(nana_field($row, 'wr_option'), 'secret') !== false || !empty($row['icon_secret']);
        $title = $locked ? '비공개 기록' : nana_plain(isset($row['wr_subject']) ? $row['wr_subject'] : $row['subject']);
        $nana_edit = $nana_timeline && !$locked ? nana_event_edit_url($bo_table, $row, $nana_current_meta) : '';
        $image = !$locked && function_exists('get_file') ? nana_image(get_file($bo_table, $row['wr_id'])) : '';
    ?>
        <?php if ($nana_timeline): ?>
        <article class="nana-event"><div class="nana-year"><span>YEAR</span><strong><?= $locked ? '—' : nana_e(nana_field($row, 'wr_1')) ?></strong></div><div class="nana-event-body"><p class="nana-eyebrow"><?= $locked ? 'PRIVATE' : nana_e(nana_field($row, 'wr_2')) ?></p><h3><a target="_self" href="<?= nana_e(nana_url($row['href'])) ?>"><?= nana_e($title) ?> <span aria-hidden="true">↗</span></a></h3><p class="nana-excerpt"><?= $locked ? '열람 권한이 필요한 기록입니다.' : nana_e(function_exists('cut_str') ? cut_str(nana_plain(nana_field($row, 'wr_content')), 180, '…') : nana_plain(nana_field($row, 'wr_content'))) ?></p><?php if ($nana_edit): ?><a class="nana-event-edit" target="_self" href="<?= nana_e($nana_edit) ?>">이 사건 수정</a><?php endif; ?></div><?php if ($image): ?><a class="nana-event-image" target="_self" href="<?= nana_e(nana_url($row['href'])) ?>" aria-label="<?= nana_e($title) ?> 읽기"><img src="<?= nana_e($image) ?>" alt="" loading="lazy"></a><?php endif; ?></article>
        <?php else: ?>
        <a class="nana-card" target="_self" href="<?= nana_e(nana_url($row['href'])) ?>"><div class="nana-portrait"><?php if ($image): ?><img src="<?= nana_e($image) ?>" alt="<?= nana_e($title) ?>의 초상" loading="lazy"><?php else: ?><span class="nana-placeholder"><span aria-hidden="true"><?= $locked ? '◇' : '✳' ?></span><?= $locked ? '비공개 기록' : '초상 준비 중' ?></span><?php endif; ?></div><div class="nana-caption"><h3><?= nana_e($title) ?><span aria-hidden="true">↗</span></h3><p><?= $locked ? '열람 권한이 필요합니다' : nana_e(nana_field($row, 'wr_2')) ?></p></div></a>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($list)): ?><p class="nana-empty">아직 기록이 없습니다. 첫 이야기를 남겨보세요.</p><?php endif; ?>
    </div>
    <div class="nana-pagination"><?= isset($write_pages) ? $write_pages : '' ?></div>
    <form class="nana-search" target="_self" method="get" action="<?= nana_e(G5_BBS_URL . '/board.php') ?>"><input type="hidden" name="bo_table" value="<?= nana_e($bo_table) ?>"><input type="hidden" name="sfl" value="wr_subject"><label for="nana-search">제목 검색</label><input id="nana-search" type="search" name="stx" maxlength="100" value="<?= nana_e(isset($stx) ? $stx : '') ?>" placeholder="찾고 싶은 이야기"><button type="submit">검색</button><?php if (!empty($stx)): ?><a target="_self" href="<?= nana_e(nana_board_url($bo_table)) ?>">전체 목록</a><?php endif; ?></form>
</div>

</div>
