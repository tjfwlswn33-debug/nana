<?php
define('NANA_FIXTURE_ONLY', true);
require __DIR__ . '/skin-test.php';
render_skin('nana_character', 'list');
$g5 = ['write_prefix' => 'g5_write_'];
$member = ['mb_level' => 2, 'mb_id' => 'writer'];
$rows = [];
foreach (range(1, 15) as $i) { $rows[] = ['wr_id' => $i, 'wr_9' => $i <= 7 ? 'nana_timeline' : '', 'wr_10' => (string) (8 - $i), 'wr_subject' => '이야기' . $i, 'mb_id' => 'writer', 'wr_option' => '']; }
$queries = [];
function sql_escape_string($s) { return addslashes($s); }
function get_list($row, $board, $url, $length) { $row['href'] = '/bbs/board.php?bo_table=oc&wr_id=' . $row['wr_id']; return $row; }
function selected_rows($sql) {
    global $rows, $queries;
    $queries[] = $sql;
    if (strpos($sql, 'g5_write_oc') === false) { throw new Exception('Wrong board queried'); }
    $event = strpos($sql, "wr_9 = 'nana_timeline'") !== false;
    $filtered = array_values(array_filter($rows, function ($row) use ($event) { return (nana_entry_type($row) === 'timeline') === $event; }));
    if ($event) { usort($filtered, function ($a, $b) { return (int) $a['wr_10'] <=> (int) $b['wr_10']; }); }
    return $filtered;
}
function sql_fetch($sql, $error = true) { return ['cnt' => count(selected_rows($sql))]; }
function sql_query($sql, $error = true) {
    $selected = selected_rows($sql);
    if (preg_match('/limit (\d+), (\d+)/', $sql, $m)) { $selected = array_slice($selected, (int) $m[1], (int) $m[2]); }
    return (object) ['rows' => $selected, 'index' => 0];
}
function sql_fetch_array($r) { return isset($r->rows[$r->index]) ? $r->rows[$r->index++] : false; }
$board = ['bo_page_rows' => 3, 'bo_admin' => 'board-admin', 'gr_admin' => 'group-admin'];
$data = nana_archive_data('oc', $board, '/skin', [], ['page' => 2], false, 7);
expect_skin($data['event_count'] === 7 && $data['character_count'] === 8 && count($data['events']) === 7 && count($data['characters']) === 3, 'one table provides independently counted timeline and paginated characters');
expect_skin($data['events'][0]['wr_id'] === 7 && $data['characters'][0]['wr_id'] === 11, 'numeric event ordering and character page offset');
$data = nana_archive_data('oc', $board, '/skin', [], ['page' => 2, 'stx' => "x' or 1=1"], true, 7);
expect_skin(count($data['events']) === 3 && strpos($data['events'][0]['href'], 'nana_section=timeline') !== false, 'full timeline stays in same board and paginates');
expect_skin(strpos(implode("\n", $queries), "x\\' or 1=1") !== false, 'title search escaped before SQL');
expect_skin(strpos(nana_archive_pagination('oc', $data, true, []), 'nana_section=timeline') !== false, 'timeline pagination retains same-board section');
$row = ['wr_id' => 7, 'mb_id' => 'writer'];
expect_skin(nana_entry_edit_url('oc', $row, $board) !== '', 'author edit action');
$member['mb_id'] = 'unrelated'; $is_admin = 'board';
expect_skin(nana_entry_edit_url('oc', $row, $board) === '', 'other-board admin gets no edit action');
$member['mb_id'] = 'board-admin';
expect_skin(nana_entry_edit_url('oc', $row, $board) !== '', 'current board admin edit action');
$member = ['mb_id' => '', 'mb_level' => 1]; $is_admin = '';
expect_skin(nana_entry_edit_url('oc', $row, $board) === '', 'guest gets no owner edit action');

expect_skin(strpos($data['events'][0]['href'], 'sfl=wr_subject') !== false && strpos(nana_archive_pagination('oc', $data, true, ['stx' => '이야기']), 'sfl=wr_subject') !== false, 'search links keep native search field on detail and pagination');
