<?php

function auto_include($folder){
    $files = scandir($folder);
    foreach($files as $fileName){
        if(!in_array($fileName,array(".","..")) && is_file($folder.$fileName)){
            //$className = strstr($fileName, '.', true);
            //echo($className);
            require_once($folder.$fileName);
        }
    }
}

/** converts seconds to time */
function sec_to_hms($t){
    $s=$t%60;
    $t=($t-$s)/60;
    $m=$t%60;
    $t=($t-$m)/60;
    $h=$t%60;
    $d=($t-$h)/24;

    $d = $d>0 ? $d.' jrs ' : '';
    $h = $h>0 ? $h.'h ' : '';
    $m = $h>0 || $m>0 ? $m.'m ' : '';

    return $d.$h.$m.$s.'s';
}

/** delta calculation between 2 dates
 * @param String $start you can use 'now'
 * @param String $end (optional) if not set it uses 'now'
 *
 * @return int time delta in seconds
 */
function get_time_diff($start, $end = null){
    $last = new DateTime($start);
    $now = $end == null ? new DateTime('now') : new DateTime($end);
    $delta = $now->getTimeStamp() - $last->getTimestamp();

    return $delta;
}

function set_id_as_key($array,$id_name = 'id' ){
    $new_array = [];
    foreach ($array as $val ){
        $new_array[$val[$id_name]] = $val;
    }
    return $new_array;
}

/**
 * @param $to int recipient user id
 * @param $message string message (can be html)
 * @param $topic string message topic trucated after 50 caracters
 * @param $from int (optional) sender user id if not defines, it'll be from the admin
 */
function send_mail($to, $message, $topic = '', $from = 0){
    if (strlen($topic) >= 50)
        $topic = trim(substr($topic, 0, 50). '...');

    $sql = "INSERT INTO messages (author, recipient, date, message, topic) VALUES (:to, :from, now(), :message, :topic)";
    $req = Db::prepare($sql);
    $req->bindParam(':to', $to, PDO::PARAM_INT);
    $req->bindParam(':from', $from, PDO::PARAM_INT);
    $req->bindParam(':message', $message, PDO::PARAM_STR);
    $req->bindParam(':topic', $topic, PDO::PARAM_STR);
    $req->execute();
    $req->closeCursor();
}
