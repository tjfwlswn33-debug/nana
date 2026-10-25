<?php
define('NANA_FIXTURE_ONLY', true);
require __DIR__ . '/skin-test.php';
render_skin('nana_character', 'list');
$g5 = ['board_table' => 'g5_board', 'group_table' => 'g5_group', 'write_prefix' => 'g5_write_', 'menu_table' => 'g5_menu'];
$member = ['mb_level' => 1];
$meta = ['bo_read_level' => 1, 'bo_list_level' => 1, 'bo_use_cert' => '', 'gr_use_access' => 0];
$queries = [];
function sql_fetch($query, $error = true) {
    global $meta, $queries;
    $queries[] = $query;
    return $meta;
}
function sql_query($query, $error = true) {
    global $queries;
    $queries[] = $query;
    if (strpos($query, 'g5_menu') !== false) {
        $rows = [
            ['me_name' => '공개메뉴', 'me_link' => '/bbs/board.php?bo_table=world&amp;page=1', 'me_level' => 1],
            ['me_name' => '숨긴메뉴', 'me_link' => '/admin-only', 'me_level' => 10],
            ['me_name' => '안전하지않은메뉴', 'me_link' => 'javascript:alert(1)', 'me_level' => 1],
        ];
    } else {
        $rows = [['wr_id' => 3, 'wr_subject' => '공개 사건', 'wr_1' => '001', 'wr_2' => '시작']];
    }
    return (object) ['rows' => $rows, 'index' => 0];
}
function sql_fetch_array($result) {
    return isset($result->rows[$result->index]) ? $result->rows[$result->index++] : false;
}
foreach (['bo_read_level' => 2, 'bo_list_level' => 2, 'bo_use_cert' => 'cert', 'gr_use_access' => 1] as $key => $value) {
    $saved = $meta[$key];
    $meta[$key] = $value;
    $queries = [];
    expect_skin(nana_public_events('timeline') === [] && count($queries) === 1, 'timeline preview blocks restricted ' . $key . ' before reading posts');
    $meta[$key] = $saved;
}
$queries = [];
expect_skin(nana_public_events("bad'board") === [] && !$queries, 'timeline board ID validated before SQL');
$events = nana_public_events('timeline', 999);
expect_skin(count($events) === 1 && $events[0]['href'] === nana_board_url('timeline', ['wr_id' => 3]), 'public preview maps native post route');
$sql = end($queries);
expect_skin(strpos($sql, "wr_option not like '%secret%'") !== false && strpos($sql, 'wr_is_comment = 0') !== false && strpos($sql, "wr_reply = ''") !== false && strpos($sql, 'limit 16') !== false, 'public preview filters secret/reply/comment rows and bounds query');
$items = nana_navigation(['character_board' => 'oc', 'timeline_board' => 'timeline'], 'oc', '캐릭터');
$labels = array_column($items, 'label');
expect_skin(in_array('공개메뉴', $labels, true) && !in_array('숨긴메뉴', $labels, true) && !in_array('안전하지않은메뉴', $labels, true), 'directory respects menu level and safe URLs');
expect_skin(count(array_unique(array_column($items, 'url'))) === count($items), 'directory deduplicates current board');
