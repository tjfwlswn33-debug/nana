<?php
define('_GNUBOARD_', true);
define('G5_BBS_URL', getenv('NANA_TEST_BBS_URL') ?: '/bbs');
define('G5_JS_URL', '/js');
define('G5_LINK_COUNT', 2);
set_error_handler(function ($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
function add_stylesheet($html, $priority) {}
function get_uniqid() { return 'test-uid'; }
function get_write_token($board) { return 'test-csrf-token'; }
function get_file($board, $id) {
    if ($id === 2) { throw new Exception('Secret attachment must not be loaded'); }
    return [0 => ['file' => defined('NANA_FIXTURE_ONLY') ? 'portrait-' . $id . '.png' : 'portrait.png', 'path' => '/data/file/oc']];
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
    $request = [];
    $skin_path = dirname(__DIR__) . '/skin/board/' . $folder;
    extract($overrides, EXTR_OVERWRITE);
    $saved_request = $_GET; $_GET = $request;
    ob_start();
    try { include $skin_path . '/' . $type . '.skin.php'; return ob_get_contents(); }
    finally { ob_end_clean(); $_GET = $saved_request; }
}
if (defined('NANA_FIXTURE_ONLY')) { return; }
$folder = 'nana_character';
$html = render_skin($folder, 'list');
expect_skin(strpos($html, 'wr_id=1') !== false && strpos($html, '<script>unsafe') === false, 'safe character links');
expect_skin(strpos($html, '숨길') === false && strpos($html, '비공개 기록') !== false, 'secret title and metadata hidden');
expect_skin(strpos($html, 'bo_table=oc&amp;nana_type=timeline') !== false && strpos($html, 'bo_table=oc&amp;nana_type=character') !== false, 'both creation buttons target the SAME board');
$html = render_skin($folder, 'list', ['list' => []]);
expect_skin(strpos($html, '아직 등록된 캐릭터') !== false && strpos($html, '아직 기록된 타임라인') !== false, 'both empty states and creation buttons');
$html = render_skin($folder, 'view', ['list_href' => null, 'page' => 3, 'stx' => '별 & 달']);
$return = nana_list_url('oc', ['page' => 3, 'stx' => '별 & 달', 'sfl' => '', 'sca' => '', 'spt' => '', 'sst' => '', 'sod' => '']);
expect_skin(substr_count($html, 'href="' . nana_e($return) . '"') === 2, 'both return links work without list_href');
expect_skin(strpos($html, 'CHARACTER NOTES') !== false && strpos($html, '<p>엔진이 변환한 본문</p>') !== false, 'character detail uses native content');
$event = ['wr_subject' => '사건', 'wr_1' => '001', 'wr_2' => '시작', 'wr_9' => 'nana_timeline', 'content' => '<p>사건 내용</p>'];
$html = render_skin($folder, 'view', ['view' => $event, 'request' => ['nana_section' => 'timeline']]);
expect_skin(strpos($html, 'WORLD CHRONICLE') !== false && strpos($html, 'nana-profile-art') === false && strpos($html, 'nana_section=timeline') !== false, 'SAME skin renders timeline detail and returns to timeline');
$html = render_skin($folder, 'view', ['update_href' => '', 'delete_href' => '']);
expect_skin(strpos($html, 'write.php?w=u') === false && strpos($html, 'delete.php') === false, 'no unauthorized native controls');
$html = render_skin($folder, 'write', ['w' => '', 'request' => ['nana_type' => 'timeline']]);
expect_skin(strpos($html, 'value="nana_timeline" selected') !== false, 'new timeline selected from its registration button');
expect_skin(strpos($html, 'name="wr_9"') !== false && strpos($html, 'name="wr_10"') !== false, 'native form saves entry kind and display order');
expect_skin(strpos($html, 'name="token"') !== false && strpos($html, 'engine-captcha-check') !== false && strpos($html, 'engine-editor') !== false, 'native CSRF captcha editor hooks');
expect_skin(strpos($html, 'bf_file[]') !== false && strpos($html, '/bbs/write_update.php') !== false, 'native attachment and save route');
$html = render_skin($folder, 'write', ['write' => ['wr_9' => 'legacy', 'wr_10' => '보존10', 'wr_3' => '생일']]);
expect_skin(strpos($html, 'value="legacy" selected') !== false && strpos($html, 'value="보존10"') !== false, 'unmarked legacy characters and metadata preserved');
$parts = nana_sections(['wr_content' => "소개 <script>bad</script>\n\n## 외형\n푸른 머리\n\n## 성격\n조용함", 'wr_option' => '']);
expect_skin(count($parts['sections']) === 2 && strpos($parts['intro'], '<script>') === false, 'safe plain-text profile sections');
$parts = nana_sections(['wr_option' => 'html1', 'content' => '<div><p>소개</p><h2>외형</h2><p><em>본문</em></p></div>']);
expect_skin(strpos($parts['intro'] . implode('', array_column($parts['sections'], 'html')), '<em>본문</em>') !== false, 'rich content preserved');
expect_skin(nana_url('javascript:alert(1)') === '' && nana_url('//evil.test/a') === '', 'unsafe URLs rejected');
