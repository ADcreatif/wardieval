<?php

/**
 * Class Users
 * Contient des fonctions globales à tous les utilisateurs
 */
class Users {

    public static function get_users_list($limit = 0) {
        $limit = 0 ? '' : " LIMIT $limit ";
        $sql = "SELECT id,pseudo,score FROM users $limit";
        $res = Db::query($sql);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }
}