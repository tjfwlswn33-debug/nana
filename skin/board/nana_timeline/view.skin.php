<?php
if (!defined('_GNUBOARD_')) exit;
require_once __DIR__ . '/skin.lib.php';
$nana_timeline = true;
$nana_photo = !$nana_timeline && !empty($view['file']) ? nana_image($view['file']) : '';
?>
<article class="nana-skin">
    <div class="nana-toolbar"><a href="<?= nana_e($list_href) ?>">← 모든 기록</a><span class="nana-eyebrow"><?= $nana_timeline ? 'WORLD CHRONICLE' : 'CHARACTER PROFILE' ?></span></div>
    <div class="nana-profile <?= $nana_timeline ? 'nana-story' : '' ?>">
        <?php if (!$nana_timeline): ?><div class="nana-profile-art"><?php if ($nana_photo): ?><img src="<?= nana_e($nana_photo) ?>" alt="<?= nana_e(nana_plain($view['wr_subject'])) ?>의 초상"><?php else: ?><span class="nana-placeholder"><span aria-hidden="true">✳</span>초상 준비 중</span><?php endif; ?></div><?php endif; ?>
        <div class="nana-profile-copy"><p class="nana-eyebrow"><?= $nana_timeline ? 'YEAR ' : '' ?><?= nana_e(isset($view['wr_1']) ? $view['wr_1'] : '') ?></p><h2><?= nana_e(nana_plain($view['wr_subject'])) ?></h2><p class="nana-subtitle"><?= nana_e(isset($view['wr_2']) ? $view['wr_2'] : '') ?></p><div class="nana-divider"></div><div class="nana-body"><?php echo $view['content']; // 아보카도 엔진이 권한 검사 및 본문 변환을 완료한 결과 ?></div>
        <?php if ($nana_timeline && !empty($view['file'])): foreach ($view['file'] as $nana_file): if (is_array($nana_file) && !empty($nana_file['view'])): ?><div class="nana-body"><?= $nana_file['view'] ?></div><?php endif; endforeach; endif; ?>
        </div>
    </div>
    <nav class="nana-actions" aria-label="기록 관리"><a href="<?= nana_e($list_href) ?>">목록으로</a><?php if (!empty($update_href)): ?><a class="nana-button" href="<?= nana_e($update_href) ?>">수정</a><?php endif; ?><?php if (!empty($delete_href)): ?><a href="<?= nana_e($delete_href) ?>" onclick="if(typeof del === 'function'){del(this.href);return false;} return confirm('이 기록을 삭제할까요?');">삭제</a><?php endif; ?></nav>
</article>
