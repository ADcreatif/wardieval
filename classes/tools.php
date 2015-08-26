<?php

/** Inclue automatiquement tous les fichiers du dossier fourni en paramètre */
function auto_include($folder) {
    $files = scandir($folder);
    foreach ($files as $fileName) {
        if (! in_array($fileName, array(".", "..")) && is_file($folder . $fileName)) {
            require_once($folder . $fileName);
        }
    }
}

/** converts seconds to time
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
 *
 * @param String $start on peut utiliser 'now'
 * @param String $end (optionel) si non défini il utilise 'now'
 *
 * @return int la durée delta en secondes
 */
function get_time_diff($start, $end = null) {
    $last = new DateTime($start);
    $now = $end == null ? new DateTime('now') : new DateTime($end);
    $delta = $now->getTimeStamp() - $last->getTimestamp();

    return $delta;
}

/** réarrange un tableau en mettant l'identifiant en clé */
function set_id_as_key($array, $id_name = 'id') {
    $new_array = [];
    foreach ($array as $val) {
        $new_array[$val[$id_name]] = $val;
    }
    return $new_array;
}


