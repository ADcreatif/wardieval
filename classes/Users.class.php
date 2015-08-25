<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 17/08/15
 * Time: 14:48
 */

class Users {

    public static function get_users_list($limit = 0){
        $limit = 0 ? '' : " LIMIT $limit ";
        $sql = "SELECT id,pseudo,score FROM users $limit";
        $res = Db::query($sql);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }


} 