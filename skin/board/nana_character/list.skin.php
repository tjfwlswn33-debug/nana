<?php
if (!defined('_GNUBOARD_')) exit;
require_once __DIR__ . '/skin.lib.php';
$nana_settings = nana_load_settings(__DIR__);
$nana_timeline_only = isset($_GET['nana_section']) && $_GET['nana_section'] === 'timeline';
$nana_context = get_defined_vars();
$nana_data = nana_archive_data($bo_table, $board, $board_skin_url, $list, $nana_context, $nana_timeline_only, $nana_settings['timeline_count']);
$nana_meta = $board;
if (isset($group['gr_admin'])) { $nana_meta['gr_admin'] = $group['gr_admin']; }
$nana_front = !$nana_timeline_only && empty($stx) && empty($sca) && $nana_data['page'] === 1;
$nana_add = !empty($write_href) ? rtrim(G5_BBS_URL, '/') . '/write.php?bo_table=' . rawurlencode($bo_table) : '';
?>
<div class="nana-shell <?= !empty($nana_settings['show_sidebar']) ? 'nana-has-sidebar' : '' ?>">
<?php if (!empty($nana_settings['show_sidebar'])) { include __DIR__ . '/sidebar.php'; } ?>
<div class="nana-skin nana-character-page" aria-label="<?= nana_e($board['bo_subject']) ?>">
    <div class="nana-heading"><p class="nana-eyebrow">OUR LITTLE UNIVERSE</p><h2><?= nana_e($board['bo_subject']) ?></h2><p class="nana-heading-note"><?= $nana_timeline_only ? '이 세계가 지나온 모든 순간들.' : '세계의 시간과, 그 안에 살아가는 사람들.' ?></p></div>
    <?php if ($nana_front): ?><section class="nana-introduction" aria-labelledby="nana-story-title"><h3 id="nana-story-title" class="nana-script"><?= nana_e($nana_settings['story_title']) ?></h3><div class="nana-prose"><?= nana_paragraphs($nana_settings['intro']) ?></div><?php if (nana_url($nana_settings['story_link'])): ?><a class="nana-text-link" target="_self" href="<?= nana_e(nana_url($nana_settings['story_link'])) ?>">이야기 더 읽기 ↗</a><?php endif; ?></section><?php endif; ?>
    <section class="nana-era-section" aria-labelledby="nana-era-title"><div class="nana-timeline-heading"><div class="nana-section-label"><h3 id="nana-era-title">Timeline</h3><span><?= number_format($nana_data['event_count']) ?>개의 순간</span></div><div class="nana-timeline-tools"><a class="nana-text-link" target="_self" href="<?= nana_e(nana_board_url($bo_table, $nana_timeline_only ? [] : ['nana_section' => 'timeline'])) ?>"><?= $nana_timeline_only ? '전체 기록' : '전체 타임라인' ?> ↗</a><?php if ($nana_add): ?><a class="nana-button nana-add-event" target="_self" href="<?= nana_e($nana_add . '&nana_type=timeline') ?>">+ 타임라인 등록</a><?php endif; ?></div></div>
    <?php if ($nana_timeline_only): ?><div class="nana-timeline"><?php foreach ($nana_data['events'] as $row):
        $locked = strpos(nana_field($row, 'wr_option'), 'secret') !== false || !empty($row['icon_secret']);
        $title = $locked ? '비공개 기록' : nana_plain(nana_field($row, 'wr_subject') ?: nana_field($row, 'subject'));
        $edit = !$locked ? nana_entry_edit_url($bo_table, $row, $nana_meta) : '';
    ?><article class="nana-event"><div class="nana-year"><span>YEAR</span><strong><?= $locked ? '—' : nana_e(nana_field($row, 'wr_1')) ?></strong></div><div class="nana-event-body"><p class="nana-eyebrow"><?= $locked ? 'PRIVATE' : nana_e(nana_field($row, 'wr_2')) ?></p><h3><a target="_self" href="<?= nana_e(nana_url($row['href'])) ?>"><?= nana_e($title) ?> ↗</a></h3><p class="nana-excerpt"><?= $locked ? '열람 권한이 필요한 기록입니다.' : nana_e(function_exists('cut_str') ? cut_str(nana_plain(nana_field($row, 'wr_content')), 180, '…') : nana_plain(nana_field($row, 'wr_content'))) ?></p><?php if ($edit): ?><a class="nana-event-edit" target="_self" href="<?= nana_e($edit) ?>">이 사건 수정</a><?php endif; ?></div></article><?php endforeach; ?></div>
    <?php else: ?><div class="nana-eras"><?php foreach ($nana_data['events'] as $row):
        $locked = strpos(nana_field($row, 'wr_option'), 'secret') !== false || !empty($row['icon_secret']);
        $title = $locked ? '비공개 기록' : nana_plain(nana_field($row, 'wr_subject') ?: nana_field($row, 'subject'));
        $image = !$locked && function_exists('get_file') ? nana_image(get_file($bo_table, $row['wr_id'])) : '';
        $edit = !$locked ? nana_entry_edit_url($bo_table, $row, $nana_meta) : '';
    ?><div class="nana-era-item"><a class="nana-era" target="_self" href="<?= nana_e(nana_url($row['href'])) ?>"><div class="nana-era-image"><?php if ($image): ?><img src="<?= nana_e($image) ?>" alt="" loading="lazy"><?php else: ?><span><?= $locked ? '◇' : nana_e(nana_field($row, 'wr_1') ?: '—') ?></span><?php endif; ?></div><span class="nana-era-title"><?= nana_e($title) ?></span><span class="nana-era-year"><?= $locked ? '' : nana_e(nana_field($row, 'wr_1')) ?></span></a><?php if ($edit): ?><a class="nana-era-edit" target="_self" href="<?= nana_e($edit) ?>" aria-label="<?= nana_e($title) ?> 수정">수정</a><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?>
    <?php if (!$nana_data['events']): ?><p class="nana-empty nana-empty-inline">아직 기록된 타임라인이 없습니다.</p><?php endif; ?></section>
    <?php if (!$nana_timeline_only): ?><section class="nana-characters-section" aria-labelledby="nana-character-title"><div class="nana-timeline-heading"><div class="nana-section-label"><h3 id="nana-character-title">Characters</h3><span><?= number_format($nana_data['character_count']) ?>명의 이야기</span></div><?php if ($nana_add): ?><a class="nana-text-link nana-add-character" target="_self" href="<?= nana_e($nana_add . '&nana_type=character') ?>">+ 캐릭터 등록</a><?php endif; ?></div>
    <div class="nana-grid"><?php foreach ($nana_data['characters'] as $row):
        $locked = strpos(nana_field($row, 'wr_option'), 'secret') !== false || !empty($row['icon_secret']);
        $title = $locked ? '비공개 기록' : nana_plain(nana_field($row, 'wr_subject') ?: nana_field($row, 'subject'));
        $image = !$locked && function_exists('get_file') ? nana_image(get_file($bo_table, $row['wr_id'])) : '';
    ?><a class="nana-card" target="_self" href="<?= nana_e(nana_url($row['href'])) ?>"><div class="nana-portrait"><?php if ($image): ?><img src="<?= nana_e($image) ?>" alt="<?= nana_e($title) ?>의 초상" loading="lazy"><?php else: ?><span class="nana-placeholder"><span aria-hidden="true"><?= $locked ? '◇' : '✳' ?></span><?= $locked ? '비공개 기록' : '초상 준비 중' ?></span><?php endif; ?></div><div class="nana-caption"><h3><?= nana_e($title) ?><span aria-hidden="true">↗</span></h3><p><?= $locked ? '열람 권한이 필요합니다' : nana_e(nana_field($row, 'wr_2')) ?></p></div></a><?php endforeach; ?><?php if (!$nana_data['characters']): ?><p class="nana-empty">아직 등록된 캐릭터가 없습니다.</p><?php endif; ?></div></section><?php endif; ?>
    <div class="nana-pagination"><?= nana_archive_pagination($bo_table, $nana_data, $nana_timeline_only, $nana_context) ?></div>
    <div class="nana-toolbar"><?php if (!empty($admin_href)): ?><a target="_self" href="<?= nana_e(nana_url($admin_href)) ?>">게시판 설정</a><?php endif; ?></div>
    <form class="nana-search" target="_self" method="get" action="<?= nana_e(G5_BBS_URL . '/board.php') ?>"><input type="hidden" name="bo_table" value="<?= nana_e($bo_table) ?>"><input type="hidden" name="sfl" value="wr_subject"><?php if ($nana_timeline_only): ?><input type="hidden" name="nana_section" value="timeline"><?php endif; ?><label for="nana-search">제목 검색</label><input id="nana-search" type="search" name="stx" maxlength="100" value="<?= nana_e(isset($stx) ? $stx : '') ?>" placeholder="찾고 싶은 이야기"><button type="submit">검색</button><?php if (!empty($stx)): ?><a target="_self" href="<?= nana_e(nana_board_url($bo_table)) ?>">전체 기록</a><?php endif; ?></form>
</div></div>
<?php include __DIR__ . '/shell.php'; ?>
