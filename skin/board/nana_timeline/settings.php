<?php
if (!defined('_GNUBOARD_')) exit;
return [
    // 기존 테마 메뉴가 이미 보인다면 false로 설정해 중복 메뉴를 끌 수 있습니다.
    'show_sidebar' => true,
    'character_board' => '',
    'story_title' => 'Our story',
    'intro' => "서로 다른 길을 걷던 이들이 같은 세계에서 만났습니다. 함께 지나온 계절과, 아직 이름 붙이지 못한 마음을 이곳에 남깁니다.\n\n크고 작은 사건들 사이에는 늘 누군가의 일상이 있습니다. 시간의 흐름을 따라, 이곳에 머무는 사람들의 이야기를 천천히 읽어 주세요.",
    // 캐릭터 목록에 함께 보여줄 연표 게시판의 실제 ID. 비우면 빈 연표 안내를 표시합니다.
    'timeline_board' => '',
    'timeline_count' => 8,
    // 전체 세계관 글 주소 (선택). 예: /bbs/board.php?bo_table=world&wr_id=1
    'story_link' => '',
];
