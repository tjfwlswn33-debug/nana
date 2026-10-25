<?php
if (!defined('_GNUBOARD_')) exit;
require_once __DIR__ . '/skin.lib.php';
$nana_settings = nana_load_settings(__DIR__);
$nana_timeline = false;
$nana_return = nana_list_url($bo_table, get_defined_vars());
$nana_photo = !$nana_timeline && !empty($view['file']) ? nana_image($view['file']) : '';
$nana_content = nana_sections($view);
?>
<div class="nana-shell <?= !empty($nana_settings['show_sidebar']) ? 'nana-has-sidebar' : '' ?>">
<?php if (!empty($nana_settings['show_sidebar'])) { include __DIR__ . '/sidebar.php'; } ?>
<div class="nana-skin <?= $nana_timeline ? 'nana-story-page' : 'nana-profile-page' ?>">
    <div class="nana-toolbar nana-back-row"><a class="nana-text-link" target="_self" href="<?= nana_e($nana_return) ?>">← 목록으로</a><span class="nana-eyebrow"><?= $nana_timeline ? 'WORLD CHRONICLE' : 'CHARACTER NOTES' ?></span></div>
    <div class="nana-profile-heading"><p class="nana-eyebrow"><?= $nana_timeline ? 'YEAR ' : '' ?><?= nana_e(nana_field($view, 'wr_1')) ?></p><div class="nana-name-row"><h2><?= nana_e(nana_plain($view['wr_subject'])) ?></h2><?php if (!$nana_timeline && nana_field($view, 'wr_8')): ?><p class="nana-script nana-roman-name"><?= nana_e($view['wr_8']) ?></p><?php endif; ?></div><p class="nana-subtitle"><?= nana_e(nana_field($view, 'wr_2')) ?></p></div>
    <div class="nana-profile <?= $nana_timeline ? 'nana-story' : '' ?>">
        <?php if (!$nana_timeline): ?><div class="nana-profile-art"><?php if ($nana_photo): ?><img src="<?= nana_e($nana_photo) ?>" alt="<?= nana_e(nana_plain($view['wr_subject'])) ?>의 초상"><?php else: ?><span class="nana-placeholder"><span aria-hidden="true">✳</span>초상 준비 중</span><?php endif; ?></div><?php endif; ?>
        <div class="nana-profile-copy"><div class="nana-body nana-prose"><?= $nana_content['intro'] ?></div>
        <?php if (!$nana_timeline): ?><dl class="nana-facts"><?php foreach (['wr_3' => '생일', 'wr_4' => '키', 'wr_5' => '종족', 'wr_6' => '상징 · 수호신', 'wr_7' => '키워드'] as $nana_key => $nana_label): if (nana_field($view, $nana_key) !== ''): ?><div><dt><?= nana_e($nana_label) ?></dt><dd><?= nana_e($view[$nana_key]) ?></dd></div><?php endif; endforeach; ?></dl><?php endif; ?>
        </div>
    </div>
    <?php if ($nana_content['sections']): ?><div class="nana-detail-sections"><?php foreach ($nana_content['sections'] as $nana_part): ?><section class="nana-detail"><h3><?= nana_e($nana_part['title']) ?></h3><div class="nana-body nana-prose"><?= $nana_part['html'] ?></div></section><?php endforeach; ?></div><?php endif; ?>
    <?php if ($nana_timeline && !empty($view['file'])): foreach ($view['file'] as $nana_file): if (is_array($nana_file) && !empty($nana_file['view'])): ?><div class="nana-body nana-event-attachment"><?= $nana_file['view'] ?></div><?php endif; endforeach; endif; ?>
    <div class="nana-actions" role="navigation" aria-label="기록 관리"><a class="nana-text-link" target="_self" href="<?= nana_e($nana_return) ?>">← 목록으로</a><div><?php if (!empty($update_href)): ?><a class="nana-button" target="_self" href="<?= nana_e(nana_url($update_href)) ?>">수정</a><?php endif; ?><?php if (!empty($delete_href)): ?><a target="_self" href="<?= nana_e(nana_url($delete_href)) ?>" onclick="if(typeof del === 'function'){del(this.href);return false;} return confirm('이 기록을 삭제할까요?');">삭제</a><?php endif; ?></div></div>
</div>

</div>
