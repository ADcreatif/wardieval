<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 31/08/15
 * Time: 15:35
 */
class CardsModel {

    function __construct($user_id = null) {
        $this->user_id = $user_id;
    }

    /**
     * returns all modifiers (income,building,price,damage,life,queue_size)
     * @param $user_id
     * @return array
     */
    public static function getModifiers($user_id) {
        $db = new Db();

        return $db->queryOne('SELECT * FROM modifiers WHERE user_id = ?', [$user_id]);
    }

}
