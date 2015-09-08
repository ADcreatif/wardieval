<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 03/09/15
 * Time: 17:48
 */
class Army {

    public $total_units;
    public $total_life;
    public $total_damage;
    public $combat_id;
    protected $user_id;

    // contient toutes les units et infos de celles-ci
    public $troops;

    function __construct($user_id, $combat_id = -1) {
        $this->user_id = $user_id;

        $this->combat_id = $combat_id;

        $this->troops = $this->get_troops_with_infos();
    }

    public static function get_unit_infos() {
        $req = Db::query('SELECT * FROM units_infos');
        return set_id_as_key($req->fetchAll(PDO::FETCH_ASSOC));
    }

    public function get_troops() {

        $sql = "SELECT i.id, o.id as troop_id, o.quantity, o.combat_id FROM units_infos i
                LEFT JOIN(SELECT unit_id, id, quantity, combat_id FROM troops WHERE user_id = $this->user_id AND combat_id = $this->combat_id) as o
                ON i.id = o.unit_id
        ";
        $req = Db::query($sql);
        return set_id_as_key($req->fetchAll(PDO::FETCH_ASSOC));
    }

    public function get_troops_with_infos() {
        // récupération des informations dans la base
        $units = $this->get_troops();
        $modifiers = Cards::get_static_modifiers($this->user_id);
        $infos = self::get_unit_infos();

        foreach ($infos as $unit_id => $info) {
            if ($this->combat_id == -1 || !is_null($units[$unit_id]['troop_id'])) {
                $troop = new Troop($units[$unit_id]['troop_id']);

                $this->troops[$unit_id] = $troop;

                $troop->hydrate([
                    'name'          => $info['name'],
                    'description'   => $info['description'],
                    'price'         => $info['price'] * $modifiers['price'],
                    'building_time' => $info['building_time'] * $modifiers['building'],
                    'damage'        => $info['damage'] * $modifiers['damage'],
                    'life'          => $info['life'] * $modifiers['life'],
                    'image_name'    => $info['image_name'],
                ]);

                $this->total_units += $troop->quantity;
                $this->total_damage += ($troop->damage * $troop->quantity);
                $this->total_life += ($troop->life * $troop->quantity);
            }
        }
        return $this->troops;
    }

    // après avoir annulé une attaque pour réafficher les quantités dans la page empire
    public function get_light_fleets() {
        $sql = "SELECT unit_id, quantity
                FROM Troops_owned
                WHERE user_id = $this->user_id AND quantity > 0";
        $req = Db::query($sql);
        return json_encode($req->fetchAll(PDO::FETCH_ASSOC));
    }

    public function add_troop($unit_id, $quantity, $combat_id = -1, $update_score = false) {
        $troop = new Troop();

        $troop->hydrate([
            'user_id'   => $this->user_id,
            'unit_id'   => $unit_id,
            'quantity'  => $quantity,
            'combat_id' => $combat_id
        ]);
        $troop->save();

        if ($update_score) {
            // on met à jour le score du joueur
            $unit = self::get_unit_infos()[$unit_id];
            $score = round(($unit['price'] + $unit['life'] + $unit['damage']) / 20);
            $user = new User($this->user_id);
            $user->increase_score($score);
        }
    }

    private function sort_troops_by_quantities($troops) {
        $quantities = [];
        $sorted_troop = [];

        foreach ($troops as $troop_id => $troop) {
            $quantities[$troop_id] = $troop->quantity;
            $sorted_troop[$troop_id] = $troop;
        }

        // on range les flottes de la plus nombreuse à la moins nombreuse
        array_multisort($quantities, SORT_DESC, $sorted_troop);
        return $sorted_troop;
    }

    /** permet de répartir aléatoirement les dégats sur les différentes unités
     * @param $damage int quantité totale de dégats infligés ce tour
     * @return string le résultat de cette escarmouche
     */
    public function split_damage($damage) {
        //on range les unités de la plus nombreuse à la moins nombreuse
        $troops = $this->sort_troops_by_quantities($this->troops);

        // on limite à 99% pour éviter les division par 0 si jammais rand = 100
        $percent_left = 99;

        $damage_left = $damage;
        $nb_fleet = count($troops);
        $result = '';
        $i = 0;

        foreach ($troops as $unit_id => $troop) {
            $i++;

            // si on a déjà plus d'unités, on reporte les dommages aux unités suivantes
            if (intval($troop->quantity) <= 0)
                continue;

            $unit_life = $troop->life * $troop->quantity;

            switch ($i) {
                case $nb_fleet : // la dernière encaise tous les dommages restants
                    $rand = 100;
                    break;
                case 0: //la première encaisse au moins 20%
                    $rand = rand(20, $percent_left);
                    break;
                default : // les autres encaissent entre 1 et le % restant
                    $rand = rand(1, $percent_left);
            }

            // mise à jour des probabilités pour le tour suivant
            $percent_left -= $rand;

            // dégats infligés ce tour çi
            $cur_damage = $damage_left * $rand / 100;

            // calcul du nombre d'unités qui vont mourir
            $losses = round($troop->quantity * $cur_damage / $unit_life);

            // dégats restants
            $damage_left -= $cur_damage;

            // cas particulier si toutes les unités sont détruites (noter que les dégats sont alors gaspillés)

            if ($troop->quantity - $losses <= 0) {
                $losses = $troop->quantity;
                $this->troops[$unit_id]->quantity = 0;
            } else {
                $this->troops[$unit_id]->quantity -= $losses;
            }

            // log
            $result .= $troop->name . ' : ' . $troop->quantity;
            if ($losses > 0) $result .= '(-' . $losses . ')'; // affiche les pertes
            if ($i != $nb_fleet) $result .= ', '; // ajoute une virgule entre les unités

            // suppression des unités dans la base
            $fleet = new Troop($troop->id);
            //todo : j'en étais là, des fois la quantité semble "nulle"
            $fleet->update_value('quantity', $this->troops[$unit_id]->quantity);

            // mise à jour des propriétés
            $this->total_units -= $losses;
            $this->total_damage -= $troop->damage * $losses;
            $this->total_life -= $troop->life * $losses;

        }

        return $result . '<br>Dégats reçu :' . $damage;
    }
} 
