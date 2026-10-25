<?php
define('_GNUBOARD_', true);
define('G5_BBS_URL', '/bbs');
define('G5_JS_URL', '/js');
define('G5_LINK_COUNT', 2);
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function add_stylesheet($html, $priority) {}
function get_uniqid() { return 'test-uid'; }
function get_write_token($board) { return 'test-csrf-token'; }
function get_file($board, $id) {
    if ($id === 2) { throw new Exception('Secret attachment must not be loaded'); }
    return [0 => ['file' => 'portrait.png', 'path' => '/data/file/oc']];
}
function expect_skin($condition, $label) {
    if (!$condition) { throw new Exception('FAIL: ' . $label); }
    echo 'PASS: ' . $label . "\n";
}
function render_skin($folder, $type, $overrides = []) {
    $board_skin_url = '/skin/board/' . $folder;
    $board = ['bo_subject' => '나의 세계'];
    $bo_table = 'oc';
    $total_count = 2;
    $admin_href = '/adm/board_form.php'; $write_href = '/bbs/write.php?bo_table=oc';
    $is_category = false; $category_option = ''; $write_pages = '<strong>1</strong>'; $stx = '';
    $list = [
        ['wr_id' => 1, 'wr_subject' => '<script>unsafe</script>', 'wr_content' => '공개 이야기', 'href' => '/bbs/board.php?bo_table=oc&wr_id=1', 'wr_option' => '', 'wr_1' => '042', 'wr_2' => '공개 소개'],
        ['wr_id' => 2, 'wr_subject' => '숨길이름', 'wr_content' => '숨길본문', 'href' => '/bbs/password.php', 'wr_option' => 'secret', 'wr_1' => '숨길연도', 'wr_2' => '숨길소개'],
    ];
    $list_href = '/bbs/board.php?bo_table=oc'; $update_href = '/bbs/write.php?w=u'; $delete_href = '/bbs/delete.php?token=engine-token';
    $view = ['wr_subject' => '캐릭터', 'wr_1' => '기록관', 'wr_2' => '소개', 'content' => '<p>엔진이 변환한 본문</p>', 'file' => get_file('oc', 1)];
    $g5 = ['title' => '글쓰기']; $config = ['cf_editor' => ''];
    $action_url = '/bbs/write_update.php'; $width = '100%'; $w = 'u'; $wr_id = 1;
    $sca = $sfl = $spt = $sst = $sod = ''; $page = 1;
    $is_notice = $is_html = $is_mail = $is_dhtml_editor = $is_email = $is_homepage = $is_link = false;
    $is_admin = $is_name = $is_password = $is_file = $is_file_content = $is_use_captcha = true;
    $is_secret = 1; $secret_checked = 'checked'; $notice_checked = $recv_email_checked = $html_checked = '';
    $html_value = 'html1'; $name = '작성자'; $password_required = ''; $subject = '제목';
    $write = ['wr_1' => '042', 'wr_2' => '<script>소개</script>'];
    $file_count = 1; $file = [0 => ['file' => 'portrait.png', 'source' => 'portrait.png', 'size' => '1KB', 'bf_content' => '초상']];
    $upload_max_filesize = '2MB'; $write_min = $write_max = 0;
    $editor_html = '<textarea name="wr_content" id="wr_content" required>본문</textarea>';
    $editor_js = '/* engine-editor */'; $captcha_html = '<div>engine-captcha</div>'; $captcha_js = '/* engine-captcha-check */';
    extract($overrides, EXTR_OVERWRITE);
    ob_start();
    try { include dirname(__DIR__) . '/skin/board/' . $folder . '/' . $type . '.skin.php'; return ob_get_contents(); }
    finally { ob_end_clean(); }
}
foreach (['nana_character', 'nana_timeline'] as $folder) {
    $html = render_skin($folder, 'list');
    expect_skin(strpos($html, 'wr_id=1') !== false && strpos($html, '<script>unsafe') === false, $folder . ' safe title and native profile link');
    expect_skin(strpos($html, '숨길') === false && strpos($html, '비공개 기록') !== false, $folder . ' secret metadata hidden');
    $html = render_skin($folder, 'list', ['list' => [], 'total_count' => 0]);
    expect_skin(strpos($html, '아직 기록이 없습니다') !== false, $folder . ' empty list');
    $html = render_skin($folder, 'view');
    expect_skin(strpos($html, '<p>엔진이 변환한 본문</p>') !== false && strpos($html, 'write.php?w=u') !== false, $folder . ' body and permitted edit link');
    $html = render_skin($folder, 'view', ['update_href' => '', 'delete_href' => '']);
    expect_skin(strpos($html, 'write.php?w=u') === false && strpos($html, 'delete.php') === false, $folder . ' no unauthorized controls');
    $html = render_skin($folder, 'write');
    expect_skin(strpos($html, 'name="wr_1"') !== false && strpos($html, 'name="wr_2"') !== false && strpos($html, '<script>소개') === false, $folder . ' escaped editable extra fields');
    expect_skin(strpos($html, 'name="token"') !== false && strpos($html, 'engine-captcha-check') !== false && strpos($html, 'engine-editor') !== false, $folder . ' engine CSRF, captcha and editor hooks');
    expect_skin(strpos($html, 'bf_file[]') !== false && strpos($html, 'bf_file_del[0]') !== false && strpos($html, '/bbs/write_update.php') !== false, $folder . ' native upload, attachment delete, save route');
}
