<?php
define('_GNUBOARD_', true);
require dirname(__DIR__) . '/integration/nana-shell.lib.php';
function shell_check($ok, $label) { if (!$ok) { throw new Exception('FAIL: ' . $label); } echo 'PASS: ' . $label . "\n"; }
$base = 'https://example.test/site/bbs';
$valid = nana_shell_route('/site/bbs/write.php?bo_table=oc&w=u&wr_id=7&nana_type=timeline&token=strip-me', $base);
shell_check(strpos($valid, 'bo_table=oc') !== false && strpos($valid, 'nana_type=timeline') !== false && strpos($valid, 'token=') === false, 'native edit route retained; unrelated credentials stripped');
foreach (['https://evil.test/site/bbs/board.php?bo_table=oc', '//evil.test/path', '/site/_common.php', '/site/bbs/board.php?bo_table[]=oc', '/site/bbs/write.php?bo_table=oc&w=delete', '/site/bbs/board.php?bo_table=oc&wr_id=-1', []] as $bad) {
    shell_check(nana_shell_route($bad, $base) === '', 'untrusted shell route rejected');
}
$fixture = sys_get_temp_dir() . '/nana-integration-' . bin2hex(random_bytes(5));
mkdir($fixture); mkdir($fixture . '/custommenu');
foreach (glob(dirname(__DIR__) . '/integration/*.php') as $file) { copy($file, $fixture . '/' . basename($file)); }
file_put_contents($fixture . '/_common.php', '<?php define("_GNUBOARD_",true); define("G5_PATH", __DIR__); define("G5_URL","https://example.test/site"); define("G5_BBS_URL",G5_URL."/bbs"); define("G5_CSS_URL",G5_URL."/css"); define("G5_IS_MOBILE",true); $config=["cf_bgm"=>1]; function get_main_link(){return G5_URL."/main.php";} function add_stylesheet($s,$p){} function goto_url($u){throw new Exception("Unexpected mobile redirect");} function get_site_content($key){return "<p id=original-content>site_main</p>";}');
file_put_contents($fixture . '/head.sub.php', '<html><body>'); file_put_contents($fixture . '/tail.sub.php', '</body></html>');
file_put_contents($fixture . '/head.php', '<div id="original-head">Original menu</div>'); file_put_contents($fixture . '/tail.php', '<div id="original-tail">Footer</div>');
file_put_contents($fixture . '/custommenu/index.php', '<div id="original-menu">Custom menu</div>');
function run_entry($fixture, $file, $get) {
    $code = '$_GET=' . var_export($get, true) . ';require ' . var_export($fixture . '/' . $file, true) . ';';
    exec(escapeshellarg(PHP_BINARY) . ' -d short_open_tag=0 -d display_errors=1 -r ' . escapeshellarg($code) . ' 2>&1', $lines, $status);
    $html = implode("\n", $lines);
    shell_check($status === 0 && strpos($html, 'Warning:') === false && strpos($html, 'Fatal error:') === false, $file . ' works with short_open_tag disabled');
    return $html;
}
try {
    $html = run_entry($fixture, 'index.php', ['nana_route' => '/site/bbs/board.php?bo_table=oc&wr_id=7']);
    shell_check(substr_count($html, 'id="bgm_frame"') === 1 && substr_count($html, 'id="original-menu"') === 1, 'original outer music frame and custom menu rendered once');
    shell_check(strpos($html, '/site/bbs/board.php?bo_table=oc&amp;wr_id=7') !== false, 'selected board opens inside original frm_main, including mobile');
    $html = run_entry($fixture, 'index.php', ['nana_shell_probe' => '1']);
    shell_check(json_decode($html, true) === ['nana_shell' => 1], 'probe returns capability only, without duplicate player');
    $html = run_entry($fixture, 'main.php', []);
    shell_check(strpos($html, 'original-head') !== false && strpos($html, 'original-tail') !== false && strpos($html, 'original-content') !== false, 'main.php preserves supplied head/tail/site_main content');
} finally {
    foreach (glob($fixture . '/custommenu/*') as $f) { unlink($f); } rmdir($fixture . '/custommenu');
    foreach (glob($fixture . '/*') as $f) { unlink($f); } rmdir($fixture);
}
