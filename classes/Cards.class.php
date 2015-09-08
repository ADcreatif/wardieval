<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 31/08/15
 * Time: 15:35
 */
class Cards {

    function __construct($user_id = null) {
        $this->user_id = $user_id;
    }

    public function get_modifiers() {
        return $this->get_static_modifiers($this->user_id);
    }

    public static function get_static_modifiers($user_id) {
        $sql = "SELECT * FROM modifiers WHERE user_id = :user_id";
        $req = Db::prepare($sql);
        $req->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $req->execute();
        return $res = $req->fetch(PDO::FETCH_ASSOC);
    }

}
