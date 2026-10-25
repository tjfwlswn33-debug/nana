<?php
if (!defined('_GNUBOARD_')) exit;
if (!function_exists('nana_e')) {
    function nana_e($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    function nana_plain($value) {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
    }
    function nana_load_settings($path) {
        $settings = require $path . '/settings.php';
        if (is_file($path . '/settings.local.php')) {
            $local = require $path . '/settings.local.php';
            if (is_array($local)) { $settings = array_replace($settings, $local); }
        }
        return $settings;
    }
    function nana_field($row, $key) {
        return isset($row[$key]) && is_scalar($row[$key]) ? (string) $row[$key] : '';
    }
    function nana_url($value) {
        $value = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
        if (preg_match('/[\x00-\x20\x7f]/', $value)) { return ''; }
        if (preg_match('~^https?://~i', $value)) {
            return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
        }
        return preg_match('~^(?!//)[/a-zA-Z0-9_.?][a-zA-Z0-9_./%?=&;+#-]*$~D', $value) ? $value : '';
    }
    function nana_image($files) {
        foreach ((array) $files as $file) {
            if (is_array($file) && !empty($file['file']) && preg_match('/\.(png|jpe?g|gif|webp)$/i', $file['file'])) {
                return nana_url($file['path'] . '/' . rawurlencode($file['file']));
            }
        }
        return '';
    }
    function nana_board_url($board, $params = []) {
        // Do not depend on list_href: some Avocado forks omit it or pass a view URL.
        return rtrim(G5_BBS_URL, '/') . '/board.php?' . http_build_query(['bo_table' => $board] + $params, '', '&', PHP_QUERY_RFC3986);
    }
    function nana_list_url($board, $context) {
        $params = [];
        foreach (['sca', 'sfl', 'stx', 'spt', 'sst', 'sod', 'page'] as $key) {
            if (isset($context[$key]) && is_scalar($context[$key]) && (string) $context[$key] !== '') {
                $params[$key] = (string) $context[$key];
            }
        }
        $params['page'] = isset($params['page']) ? max(1, (int) $params['page']) : 1;
        return nana_board_url($board, $params);
    }
    function nana_paragraphs($text) {
        $html = '';
        foreach (preg_split('/\n\s*\n/', str_replace("\r", '', $text)) as $paragraph) {
            if (trim($paragraph) !== '') { $html .= '<p>' . nl2br(nana_e(trim($paragraph))) . '</p>'; }
        }
        return $html;
    }
    function nana_rich_sections($html) {
        if (!class_exists('DOMDocument')) {
            // Without DOM support, retain complex editor wrappers intact.
            if (preg_match('~<(?:div|table|section|article|blockquote)\b~i', $html)) {
                return ['intro' => $html, 'sections' => []];
            }
            $parts = preg_split('~<h[23]\b[^>]*>(.*?)</h[23]\s*>~is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
            $intro = array_shift($parts);
            $sections = [];
            for ($i = 0; $i + 1 < count($parts); $i += 2) {
                $sections[] = ['title' => nana_plain($parts[$i]), 'html' => $parts[$i + 1]];
            }
            return ['intro' => $intro, 'sections' => $sections];
        }
        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $doc->loadHTML('<!doctype html><html><head><meta charset="utf-8"></head><body><div id="nana-content-root">' . $html . '</div></body></html>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $root = $loaded ? $doc->getElementById('nana-content-root') : null;
        if (!$root) { return ['intro' => $html, 'sections' => []]; }
        $result = ['intro' => '', 'sections' => []];
        $walk = function ($node) use (&$walk, &$result, $doc) {
            $tag = $node->nodeType === XML_ELEMENT_NODE ? strtolower($node->nodeName) : '';
            if ($tag === 'h2' || $tag === 'h3') {
                $result['sections'][] = ['title' => trim($node->textContent), 'html' => ''];
            } elseif (in_array($tag, ['div', 'section', 'article'], true) && ($node->getElementsByTagName('h2')->length || $node->getElementsByTagName('h3')->length)) {
                // Flatten only section containers, so no opening wrapper is separated from its closing tag.
                foreach ($node->childNodes as $child) { $walk($child); }
            } else {
                $fragment = $doc->saveHTML($node);
                if ($result['sections']) {
                    $index = count($result['sections']) - 1;
                    $result['sections'][$index]['html'] .= $fragment;
                } else {
                    $result['intro'] .= $fragment;
                }
            }
        };
        foreach ($root->childNodes as $node) { $walk($node); }
        return $result;
    }
    function nana_sections($view) {
        $raw = nana_field($view, 'wr_content');
        $is_html = strpos(nana_field($view, 'wr_option'), 'html') !== false;
        if (array_key_exists('wr_content', $view) && !$is_html) {
            $parts = preg_split('/^\h*##\h+(.+?)\h*$/m', str_replace("\r", '', $raw), -1, PREG_SPLIT_DELIM_CAPTURE);
            $intro = nana_paragraphs(array_shift($parts));
            $sections = [];
            for ($i = 0; $i + 1 < count($parts); $i += 2) {
                $sections[] = ['title' => trim($parts[$i]), 'html' => nana_paragraphs($parts[$i + 1])];
            }
        } else {
            // Content is already converted/sanitized by the board engine.
            return nana_rich_sections(nana_field($view, 'content'));
        }
        return ['intro' => $intro, 'sections' => $sections];
    }
    function nana_navigation($settings, $board, $title) {
        global $g5, $member;
        $home = function_exists('get_main_link') ? nana_url(get_main_link()) : (defined('G5_URL') ? G5_URL . '/main.php' : nana_board_url($board));
        $items = [['label' => '홈으로', 'url' => $home]];
        if (function_exists('sql_query') && isset($g5['menu_table'])) {
            $result = sql_query("select me_name, me_link, me_level from {$g5['menu_table']} where me_use = '1' and length(me_code) = '2' order by me_order*1, me_id", false);
            while ($result && ($row = sql_fetch_array($result))) {
                $level = isset($member['mb_level']) ? (int) $member['mb_level'] : 1;
                if ($level >= (int) $row['me_level'] && nana_url($row['me_link'])) {
                    $items[] = ['label' => nana_plain($row['me_name']), 'url' => nana_url($row['me_link'])];
                }
            }
        }
        // These links also work when the host's custom menu is absent on board pages.
        foreach (['character_board' => '캐릭터', 'timeline_board' => '세계의 시간'] as $key => $label) {
            if (!empty($settings[$key]) && preg_match('/^[a-zA-Z0-9_]{1,20}$/D', $settings[$key])) {
                $items[] = ['label' => $label, 'url' => nana_board_url($settings[$key])];
            }
        }
        $items[] = ['label' => nana_plain($title), 'url' => nana_board_url($board)];
        $seen = [];
        return array_values(array_filter($items, function ($item) use (&$seen) {
            if (!$item['url'] || isset($seen[$item['url']])) { return false; }
            $seen[$item['url']] = true;
            return true;
        }));
    }
    function nana_public_board_meta($board) {
        global $g5;
        if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/D', $board) || !function_exists('sql_fetch') || !isset($g5['board_table'], $g5['group_table'], $g5['write_prefix'])) { return null; }
        $meta = sql_fetch("select b.bo_read_level, b.bo_list_level, b.bo_use_cert, b.bo_write_level, b.bo_admin, g.gr_use_access, g.gr_admin from {$g5['board_table']} b left join {$g5['group_table']} g on b.gr_id = g.gr_id where b.bo_table = '{$board}'", false);
        if (!$meta || (int) $meta['bo_read_level'] > 1 || (int) $meta['bo_list_level'] > 1 || !empty($meta['bo_use_cert']) || !empty($meta['gr_use_access'])) { return null; }
        return $meta;
    }
    function nana_board_manager($meta) {
        global $is_admin, $member;
        if (!$meta) { return false; }
        if (isset($is_admin) && $is_admin === 'super') { return true; }
        $id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
        return $id !== '' && ($id === nana_field($meta, 'bo_admin') || $id === nana_field($meta, 'gr_admin'));
    }
    function nana_timeline_tools($board) {
        $tools = ['list_href' => '', 'write_href' => ''];
        if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/D', $board)) { return $tools; }
        $tools['list_href'] = nana_board_url($board);
        $meta = nana_public_board_meta($board);
        global $member;
        $level = isset($member['mb_level']) ? (int) $member['mb_level'] : 1;
        if ($meta && (nana_board_manager($meta) || (isset($meta['bo_write_level']) && $level >= (int) $meta['bo_write_level']))) {
            $tools['write_href'] = rtrim(G5_BBS_URL, '/') . '/write.php?' . http_build_query(['bo_table' => $board], '', '&', PHP_QUERY_RFC3986);
        }
        return $tools;
    }
    function nana_event_edit_url($board, $row, $meta) {
        global $member;
        if (!$meta) { return ''; }
        $id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
        $author = $id !== '' && $id === nana_field($row, 'mb_id');
        if (!nana_board_manager($meta) && !$author) { return ''; }
        return rtrim(G5_BBS_URL, '/') . '/write.php?' . http_build_query(['w' => 'u', 'bo_table' => $board, 'wr_id' => (int) $row['wr_id']], '', '&', PHP_QUERY_RFC3986);
    }
    function nana_public_events($board, $limit = 8) {
        global $g5;
        $meta = nana_public_board_meta($board);
        if (!$meta) { return []; }
        $limit = max(1, min(16, (int) $limit));
        $result = sql_query("select wr_id, wr_subject, wr_1, wr_2, mb_id from {$g5['write_prefix']}{$board} where wr_is_comment = 0 and wr_reply = '' and wr_option not like '%secret%' order by wr_num, wr_id limit {$limit}", false);
        $events = [];
        while ($result && ($row = sql_fetch_array($result))) {
            $row['href'] = nana_board_url($board, ['wr_id' => (int) $row['wr_id']]);
            $row['edit_href'] = nana_event_edit_url($board, $row, $meta);
            $row['image'] = function_exists('get_file') ? nana_image(get_file($board, $row['wr_id'])) : '';
            $events[] = $row;
        }
        return $events;
    }
}
add_stylesheet('<link rel="stylesheet" href="'.nana_e($board_skin_url).'/style.css?v=3">', 20);
