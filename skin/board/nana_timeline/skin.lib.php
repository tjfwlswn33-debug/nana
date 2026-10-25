<?php
if (!defined('_GNUBOARD_')) exit;
if (!function_exists('nana_e')) {
    function nana_e($v) { return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    function nana_plain($v) { return trim(html_entity_decode(strip_tags((string) $v), ENT_QUOTES, 'UTF-8')); }
    function nana_url($v) {
        $v = (string) $v;
        return preg_match('~^https?://~i', $v) || preg_match('~^(?!//)[/a-zA-Z0-9_.?][a-zA-Z0-9_./%?=&;+#-]*$~D', $v) ? $v : '';
    }
    function nana_image($files) {
        foreach ((array) $files as $f) {
            if (is_array($f) && !empty($f['file']) && preg_match('/\.(png|jpe?g|gif|webp)$/i', $f['file'])) {
                return nana_url($f['path'] . '/' . rawurlencode($f['file']));
            }
        }
        return '';
    }
}
add_stylesheet('<link rel="stylesheet" href="'.nana_e($board_skin_url).'/style.css">', 20);
