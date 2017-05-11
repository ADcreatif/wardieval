<?php

/** converts seconds to time
 * @param $t int seconds
 * @return string formated times
 * @deprecated javascript met déjà en forme grace à countdown
 */
function sec_to_hms($t) {
    $s = $t % 60;
    $t = ($t - $s) / 60;
    $m = $t % 60;
    $t = ($t - $m) / 60;
    $h = $t % 60;
    $d = ($t - $h) / 24;

    $d = $d > 0 ? $d . ' jrs ' : '';
    $h = $h > 0 ? $h . 'h ' : '';
    $m = $h > 0 || $m > 0 ? $m . 'm ' : '';

    return $d . $h . $m . $s . 's';
}

/** calcul du delta entre deux dates
 * @param String $start on peut utiliser 'now'
 * @param String $end (optionel) si non défini il utilise 'now'
 * @return int la durée delta en secondes
 */
function get_time_diff($start, $end = null) {
    $last = new DateTime($start);
    $now = $end == null ? new DateTime('now') : new DateTime($end);
    $delta = $now->getTimeStamp() - $last->getTimestamp();

    return $delta;
}

/**
 * organise an associative array using any choosen id as key
 * @param $array array an associative array
 * @param $id_name String wich value should be the key
 * @return array
 */
function set_id_as_key($array, $id_name = 'id') {
    $new_array = [];
    foreach ($array as $val) {
        $new_array[$val[$id_name]] = $val;
    }
    return $new_array;
}

function clean_html($html) {
    //On nettoie le HTML
    $html = strip_tags($html, '<table><tr><td><th><tbody><thead><tfooter><p><ul><li><strong><b><em><u><ol><lh><strike><i><dl><dt><dd><br>');

    //On nettoie les class, id et autres attributs
    $suppr = array('/[\s]{1}class=[\"\'].*?[\"\']/', '/[\s]{1}id=[\"\'].*?[\"\']/', '/[\s]{1}color=[\"\'].*?[\"\']/', '/[\s]{1}face=[\"\'].*?[\"\']/', '/[\s]{1}size=[\"\'].*?[\"\']/', '/[\s]{1}align=[\"\'].*?[\"\']/');
    $html = preg_replace($suppr, '', $html);

    return $html;
}

function redirect($url = "") {
    header("Location: " . _HOME_ . $url);
    exit;
}



