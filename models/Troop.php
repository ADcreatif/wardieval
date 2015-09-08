<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 30/08/15
 * Time: 21:18
 */
class Troop extends ObjectModel {

    public $name;
    public $description;
    public $price;
    public $building_time;
    public $damage;
    public $life;
    public $image_name;
    public $quantity;
    public $unit_id;
    public $user_id;
    public $combat_id;

    protected $table = 'troops';
    protected static $definition = [
        ['name' => 'unit_id', 'type' => self::TYPE_INT],
        ['name' => 'user_id', 'type' => self::TYPE_INT],
        ['name' => 'quantity', 'type' => self::TYPE_INT],
        ['name' => 'combat_id', 'type' => self::TYPE_INT],
    ];

    // on surcharge add à cause de la contrainte d'unicité sur user_id x unit_id
    // et il faut incrémenter la quantité plutôt qu'insérer une nouvelle valeur
    public function add() {
        $fields = $this->format_request();

        $sql = "INSERT troops SET {$fields['sql_params']}
                ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";

        $req = Db::prepare($sql);

        foreach ($fields['bind_params'] as $field)
            $req->bindParam($field['name'], $field['value'], $field['type']);

        if ($req->execute())
            return Db::getLastInsertId();
        return false;

    }

}
