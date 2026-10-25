<?php
// Static browser fixtures. No database, authentication or save actions are emulated.
define('NANA_FIXTURE_ONLY', true);
define('G5_URL', '/site');
putenv('NANA_TEST_BBS_URL=/site/bbs');
require dirname(__DIR__) . '/skin-test.php';
$g5 = ['board_table' => 'g5_board', 'group_table' => 'g5_group', 'write_prefix' => 'g5_write_', 'menu_table' => 'g5_menu'];
$member = ['mb_level' => 10, 'mb_id' => 'owner'];
$is_admin = 'super';
function sql_fetch($sql, $error = true) { return ['bo_read_level' => 1, 'bo_list_level' => 1, 'bo_use_cert' => '', 'gr_use_access' => 0, 'bo_write_level' => 2, 'bo_admin' => 'owner', 'gr_admin' => 'owner']; }
function sql_query($sql, $error = true) {
    $rows = [];
    if (strpos($sql, 'g5_menu') !== false) {
        $rows = [['me_name' => '세계관', 'me_link' => '/site/bbs/board.php?bo_table=oc&nana_section=timeline', 'me_level' => 1]];
    } else {
        foreach (['첫 번째 불빛', '도시가 깨어난 날', '이름 없는 편지', '다시 만난 계절', '건너편의 사람들', '긴 여행의 끝', '다음 이야기'] as $index => $name) {
            $rows[] = ['wr_id' => 11 + $index, 'wr_subject' => $name, 'wr_1' => sprintf('%03d', 10 + $index * 8), 'wr_2' => '우리의 기록', 'mb_id' => 'owner'];
        }
    }
    return (object) ['rows' => $rows, 'index' => 0];
}
function sql_fetch_array($result) { return isset($result->rows[$result->index]) ? $result->rows[$result->index++] : false; }
$output = '/output';
foreach (['nana_character'] as $folder) {
    $target = $output . '/skin/board/' . $folder;
    if (!is_dir($target)) { mkdir($target, 0777, true); }
    foreach (glob(dirname(__DIR__, 2) . '/skin/board/' . $folder . '/*') as $file) {
        if (basename($file) !== 'settings.local.php') { copy($file, $target . '/' . basename($file)); }
    }
    file_put_contents($target . '/settings.local.php', '<?php return ["show_sidebar" => true];');
}
$list = [];
foreach (['서이안', '유하린', '백서우'] as $index => $name) {
    $list[] = ['wr_id' => 1 + $index * 2, 'wr_subject' => $name, 'wr_content' => '서로 다른 길에서 만난 사람들. 오래 남은 계절과 함께한 순간들을 기록합니다.', 'href' => '/site/bbs/board.php?bo_table=oc&wr_id=' . (1 + $index * 2), 'wr_option' => '', 'wr_1' => '기록관', 'wr_2' => ['이야기를 수집하는 사람', '경계 너머의 여행자', '새벽을 지키는 정원사'][$index]];
}
$view = ['wr_subject' => '서이안', 'wr_1' => 'ARCHIVIST · 기록관', 'wr_2' => '이야기를 수집하는 사람', 'wr_3' => '3월 20일', 'wr_4' => '172cm', 'wr_5' => '인간', 'wr_6' => '흰 새, 오래된 나무', 'wr_7' => '고요함 · 다정함', 'wr_8' => 'Seo Ian', 'wr_option' => '', 'wr_content' => "사라진 역의 기록실에서 오래된 이름들을 수집한다. 이안에게 기록은 지나간 시간을 붙잡는 일이 아니라, 누군가가 이곳에 있었다는 작은 약속이다.\n\n비가 내리는 날에는 창문을 조금 열어 둔다. 종이 냄새와 젖은 흙의 냄새가 섞이면, 잊었던 이름 하나가 문득 떠오르곤 한다.\n\n## 외형\n짙은 머리카락과 차분한 눈빛. 계절에 관계없이 조금 큰 외투를 걸치고, 가슴 주머니에는 짧은 연필을 넣어 둔다.\n\n옷자락에는 종종 잉크가 묻어 있다. 오래된 종이를 만지는 손길은 느리지만 망설임은 없다.\n\n## 성격\n말을 아끼고, 상대의 이야기가 끝날 때까지 기다린다. 이름보다 그 사람이 남긴 작은 습관들을 먼저 기억하는 편이다.\n\n## 지나온 이야기\n어느 날부터 역에는 돌아오지 않는 사람들이 늘었다. 이안은 그들의 이름을 적기 시작했고, 그 작은 기록은 한 권의 책이 되었다.", 'file' => get_file('oc', 1)];
function save_fixture($output, $filename, $folder, $kind, $data) {
    if ($kind === 'write') { $data += ['is_name' => false, 'is_password' => false, 'is_use_captcha' => false, 'secret_checked' => '']; }
    $data['skin_path'] = $output . '/skin/board/' . $folder;
    $html = render_skin($folder, $kind, $data);
    $head = '<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nana skin fixture</title><link rel="stylesheet" href="/skin/board/' . $folder . '/style.css"><style>body{margin:0;background:#e9edf0;color:#164669;font:13px/2 Arial,"Malgun Gothic",sans-serif}#header{max-width:1100px;margin:20px auto 0;padding:0 24px 15px;border-bottom:1px solid #cdd8df;display:flex;justify-content:space-between;align-items:center}#header a{color:inherit;text-decoration:none}#logo{font:italic 30px Georgia}header{background:transparent}nav{display:flex;gap:24px}.fix-layout{max-width:1140px;padding:0 14px;margin:auto}footer{text-align:center;margin:40px 0;font-size:10px;color:#7691a2}iframe{max-width:100%}</style></head><body><header id="header"><a id="logo" href="/site/main.php">nana.</a><nav id="host-menu"><a href="/site/main.php">Home</a><a href="/site/bbs/board.php?bo_table=oc">Archive</a></nav></header><div id="host-bgm" style="position:fixed;left:8px;bottom:8px;z-index:100;background:#fff;border:1px solid #bacbd4;padding:6px 12px;font-size:11px">♪ BGM <button type="button" aria-label="BGM controls">Ⅱ</button></div><section id="body"><div class="fix-layout">';
    file_put_contents($output . '/' . $filename, $head . $html . '</div></section><footer>A little collection of our stories.</footer></body></html>');
}
save_fixture($output, 'list.html', 'nana_character', 'list', ['list' => $list, 'total_count' => 3, 'page' => 1]);
save_fixture($output, 'profile.html', 'nana_character', 'view', ['view' => $view, 'list_href' => null]);
save_fixture($output, 'write.html', 'nana_character', 'write', ['write' => $view]);
$events = [];
foreach (['첫 번째 불빛', '도시가 깨어난 날', '이름 없는 편지', '다시 만난 계절', '건너편의 사람들', '긴 여행의 끝', '다음 이야기'] as $index => $name) {
    $events[] = ['wr_id' => 11 + $index, 'wr_subject' => $name, 'wr_content' => '서로 다른 사람들이 같은 도시에서 만났습니다. 작은 인연들이 모여 이 세계의 이야기가 되었습니다.', 'href' => '/site/bbs/board.php?bo_table=oc&wr_id=' . (11 + $index), 'wr_option' => '', 'wr_1' => sprintf('%03d', $index * 10 + 1), 'wr_2' => '세계의 기록', 'wr_9' => 'nana_timeline', 'wr_10' => (string) $index, 'mb_id' => 'owner'];
}
$combined = array_merge($events, $list);
save_fixture($output, 'list.html', 'nana_character', 'list', ['list' => $combined, 'total_count' => 10, 'page' => 1]);
$timeline_entries = $events; foreach ($timeline_entries as &$entry) { $entry['href'] .= '&nana_section=timeline'; } unset($entry);
save_fixture($output, 'timeline.html', 'nana_character', 'list', ['list' => array_merge($timeline_entries, $list), 'request' => ['nana_section' => 'timeline']]);
$event = $events[0]; $event['file'] = get_file('oc', 11); $event['content'] = '<p>첫 만남을 기록합니다.</p>';
save_fixture($output, 'event.html', 'nana_character', 'view', ['view' => $event]);
save_fixture($output, 'event-timeline.html', 'nana_character', 'view', ['view' => $event, 'request' => ['nana_section' => 'timeline']]);
save_fixture($output, 'write-event.html', 'nana_character', 'write', ['w' => '', 'write' => [], 'request' => ['nana_type' => 'timeline']]);
save_fixture($output, 'edit-event.html', 'nana_character', 'write', ['write' => $event]);
save_fixture($output, 'write.html', 'nana_character', 'write', ['w' => '', 'write' => [], 'request' => ['nana_type' => 'character']]);
echo "Rendered unified-board browser fixtures\n";
