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
        if (isset($_GET['nana_section']) && $_GET['nana_section'] === 'timeline') { $params['nana_section'] = 'timeline'; }
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
    function nana_entry_type($row) {
        return nana_field($row, 'wr_9') === 'nana_timeline' ? 'timeline' : 'character';
    }
    function nana_board_manager($meta) {
        global $is_admin, $member;
        if (isset($is_admin) && $is_admin === 'super') { return true; }
        $id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
        return $id !== '' && ($id === nana_field($meta, 'bo_admin') || $id === nana_field($meta, 'gr_admin'));
    }
    function nana_entry_edit_url($board, $row, $meta) {
        global $member;
        $id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
        if (!nana_board_manager($meta) && ($id === '' || $id !== nana_field($row, 'mb_id'))) { return ''; }
        return rtrim(G5_BBS_URL, '/') . '/write.php?' . http_build_query(['w' => 'u', 'bo_table' => $board, 'wr_id' => (int) $row['wr_id']], '', '&', PHP_QUERY_RFC3986);
    }
    function nana_navigation($settings, $board, $title) {
        global $g5, $member;
        $home = function_exists('get_main_link') ? nana_url(get_main_link()) : (defined('G5_URL') ? G5_URL . '/main.php' : nana_board_url($board));
        $items = [['label' => '홈으로', 'url' => $home]];
        if (function_exists('sql_query') && isset($g5['menu_table'])) {
            $result = sql_query("select me_name, me_link, me_level from {$g5['menu_table']} where me_use = '1' and length(me_code) = '2' order by me_order*1, me_id", false);
            while ($result && ($row = sql_fetch_array($result))) {
                $level = isset($member['mb_level']) ? (int) $member['mb_level'] : 1;
                if ($level >= (int) $row['me_level'] && nana_url($row['me_link'])) { $items[] = ['label' => nana_plain($row['me_name']), 'url' => nana_url($row['me_link'])]; }
            }
        }
        $items[] = ['label' => nana_plain($title), 'url' => nana_board_url($board)];
        $items[] = ['label' => '전체 타임라인', 'url' => nana_board_url($board, ['nana_section' => 'timeline'])];
        $seen = [];
        return array_values(array_filter($items, function ($item) use (&$seen) {
            if (!$item['url'] || isset($seen[$item['url']])) { return false; }
            $seen[$item['url']] = true;
            return true;
        }));
    }
    function nana_archive_data($id, $board, $skin_url, $fallback, $context, $timeline_only, $limit) {
        global $g5;
        $per_page = max(1, min(50, (int) (isset($board['bo_page_rows']) ? $board['bo_page_rows'] : 12)));
        $page = max(1, (int) (isset($context['page']) && is_scalar($context['page']) ? $context['page'] : 1));
        $data = ['characters' => [], 'events' => [], 'character_count' => 0, 'event_count' => 0, 'page' => $page, 'per_page' => $per_page];
        // The native board controller has already checked read/group/cert access.
        // Query only this board, never a separately configured board.
        if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/D', $id) || !isset($g5['write_prefix']) || !function_exists('sql_escape_string') || !function_exists('get_list')) {
            foreach ((array) $fallback as $row) {
                if (nana_entry_type($row) === 'timeline') { $data['events'][] = $row; } else { $data['characters'][] = $row; }
            }
            $data['character_count'] = count($data['characters']); $data['event_count'] = count($data['events']);
            return $data;
        }
        $where = "wr_is_comment = 0 and wr_reply = ''";
        if (function_exists('get_sql_search')) {
            $native = get_sql_search(isset($context['sca']) && is_scalar($context['sca']) ? $context['sca'] : '', 'wr_subject', isset($context['stx']) && is_scalar($context['stx']) ? $context['stx'] : '', 'and');
            if (is_string($native) && $native !== '') { $where .= ' and (' . $native . ')'; }
        } else {
        foreach (['stx' => 'wr_subject', 'sca' => 'ca_name'] as $key => $column) {
            if (isset($context[$key]) && is_scalar($context[$key]) && (string) $context[$key] !== '') {
                $value = sql_escape_string(html_entity_decode((string) $context[$key], ENT_QUOTES, 'UTF-8'));
                $where .= $key === 'stx' ? " and {$column} like '%{$value}%'" : " and {$column} = '{$value}'";
            }
        }
        }
        $table = $g5['write_prefix'] . $id;
        foreach (['characters' => "(wr_9 <> 'nana_timeline' or wr_9 is null)", 'events' => "wr_9 = 'nana_timeline'"] as $kind => $type_where) {
            $count = sql_fetch("select count(*) as cnt from {$table} where {$where} and {$type_where}", false);
            $total = $count ? (int) $count['cnt'] : 0;
            $data[$kind === 'characters' ? 'character_count' : 'event_count'] = $total;
            $active = $timeline_only ? $kind === 'events' : $kind === 'characters';
            $current = $active ? min($page, max(1, (int) ceil($total / $per_page))) : 1;
            if ($active) { $data['page'] = $current; }
            $take = $active ? $per_page : max(1, min(32, (int) $limit));
            $offset = $active ? ($current - 1) * $per_page : 0;
            $order = $kind === 'events' ? 'cast(wr_10 as signed) asc, wr_num asc, wr_id asc' : 'wr_num asc, wr_id asc';
            $result = sql_query("select * from {$table} where {$where} and {$type_where} order by {$order} limit {$offset}, {$take}", false);
            while ($result && ($row = sql_fetch_array($result))) {
                $entry = get_list($row, $board, $skin_url, 100);
                $params = ['wr_id' => (int) $row['wr_id']];
                if ($timeline_only) { $params['nana_section'] = 'timeline'; }
                foreach (['page', 'stx', 'sca'] as $key) { if (isset($context[$key]) && is_scalar($context[$key])) { $params[$key] = $context[$key]; } }
                if (!empty($params['stx'])) { $params['sfl'] = 'wr_subject'; }
                $entry['href'] = nana_board_url($id, $params);
                $data[$kind][] = $entry;
            }
        }
        return $data;
    }
    function nana_archive_pagination($id, $data, $timeline_only, $context) {
        $count = $timeline_only ? $data['event_count'] : $data['character_count'];
        $pages = (int) ceil($count / $data['per_page']);
        if ($pages <= 1) { return ''; }
        $current = $data['page']; $html = '';
        $params = $timeline_only ? ['nana_section' => 'timeline'] : [];
        foreach (['stx', 'sca'] as $key) { if (isset($context[$key]) && is_scalar($context[$key])) { $params[$key] = $context[$key]; } }
        if (!empty($params['stx'])) { $params['sfl'] = 'wr_subject'; }
        $numbers = array_unique(array_merge([1], range(max(1, $current - 2), min($pages, $current + 2)), [$pages]));
        foreach ($numbers as $number) {
            $params['page'] = $number;
            $html .= $number === $current ? '<strong aria-current="page">' . $number . '</strong>' : '<a target="_self" href="' . nana_e(nana_board_url($id, $params)) . '">' . $number . '</a>';
        }
        return $html;
    }

}
add_stylesheet('<link rel="stylesheet" href="'.nana_e($board_skin_url).'/style.css?v=4">', 20);
