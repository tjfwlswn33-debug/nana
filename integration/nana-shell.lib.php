<?php
if (!defined('_GNUBOARD_')) exit;

// Accept native board pages only; never load a caller-supplied host or file.
function nana_shell_route($route, $bbs_url) {
    if (!is_string($route) || strlen($route) > 4096 || preg_match('/[\x00-\x20\x7f\\\\]/', $route)) { return ''; }
    $parts = parse_url($route);
    if (!$parts || isset($parts['scheme']) || isset($parts['host']) || strpos($route, '//') === 0) { return ''; }
    $bbs_path = rtrim((string) parse_url($bbs_url, PHP_URL_PATH), '/');
    $allowed = [$bbs_path . '/board.php', $bbs_path . '/write.php'];
    if (!isset($parts['path']) || !in_array($parts['path'], $allowed, true)) { return ''; }
    parse_str(isset($parts['query']) ? $parts['query'] : '', $query);
    if (!isset($query['bo_table']) || !is_string($query['bo_table']) || !preg_match('/^[a-zA-Z0-9_]{1,20}$/D', $query['bo_table'])) { return ''; }
    $params = ['bo_table' => $query['bo_table']];
    if (isset($query['wr_id'])) {
        if (!is_string($query['wr_id']) || !preg_match('/^[1-9][0-9]{0,11}$/D', $query['wr_id'])) { return ''; }
        $params['wr_id'] = $query['wr_id'];
    }
    if ($parts['path'] === $bbs_path . '/write.php' && isset($query['w']) && $query['w'] !== '') {
        if (!is_string($query['w']) || !in_array($query['w'], ['u', 'r'], true) || !isset($params['wr_id'])) { return ''; }
        $params['w'] = $query['w'];
    }
    foreach (['sca', 'sfl', 'stx', 'spt', 'sst', 'sod', 'sop', 'page'] as $key) {
        if (isset($query[$key]) && is_scalar($query[$key]) && strlen((string) $query[$key]) <= 256) {
            $params[$key] = (string) $query[$key];
        }
    }
    foreach (['nana_type' => ['character', 'timeline'], 'nana_section' => ['timeline']] as $key => $values) {
        if (isset($query[$key]) && is_string($query[$key]) && in_array($query[$key], $values, true)) { $params[$key] = $query[$key]; }
    }
    $result = $parts['path'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    if (isset($parts['fragment']) && preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $parts['fragment'])) { $result .= '#' . $parts['fragment']; }
    return $result;
}
