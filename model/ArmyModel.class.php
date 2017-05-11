<?php

/**
 * Gère l'armée complète de l'utilisateur, une armée est composée de troops
 */
class ArmyModel {

    //private $total_units;
    //public $total_damage;
    //public  $total_life;

    private $user_id;
    private $combat_id;
    private $troops;

    function __construct($user_id, $combat_id = -1) {
        $this->combat_id = $combat_id;
        $this->user_id = $user_id;

        $this->get_troops();
    }

    public static function get_unit_infos() {
        $db = new Db();
        return set_id_as_key($db->query('SELECT * FROM units_infos'));
    }

    /**
     * récupère toutes les unités en combat ou en stock
     * @param bool $computeModifiers will calculate things including player modifiers
     * @return array [id,name,description, price, building_time, damage, life, image_name, troop_id, quantity, combat_id]
     *
     */
    public function get_troops($computeModifiers = true) {
        if (!$this->troops) {
            $sql = "SELECT i.*, troop.id AS troop_id, IFNULL(troop.quantity,0) AS quantity, IFNULL(troop.combat_id,-1) AS combat_id FROM units_infos i
                    LEFT JOIN(SELECT unit_id, id, quantity , combat_id FROM troops WHERE user_id = ? AND combat_id = ?) AS troop
                    ON i.id = troop.unit_id";
            $db = new Db();
            $troops = set_id_as_key($db->query($sql, [$this->user_id, $this->combat_id]));

            if ($computeModifiers) {
                $modifiers = CardsModel::getModifiers($this->user_id);
                foreach ($troops as $key => $troop) {
                    $troops[$key]['price'] = $troop['price'] * $modifiers['price'];
                    $troops[$key]['building_time'] = $troop['building_time'] * $modifiers['building'];
                    $troops[$key]['damage'] = $troop['damage'] * $modifiers['damage'];
                    $troops[$key]['life'] = $troop['life'] * $modifiers['life'];
                }
            }
            $this->troops = $troops;
        }
        return $this->troops;
    }

    /**
     * est ce qu'il reste des unités dans la troop ?
     * @param $troops
     * @return bool
     */
    public function troop_anihilated(array $troops) {
        return max(array_column($troops, 'quantity')) > 0;
    }

    public function get_total_units() {
        $quantity = 0;
        foreach ($this->troops as $troop) {
            $quantity += $troop['quantity'];
        }
        return $quantity;
        /*
        if(!$this->total_units) {
            $db = new Db;
            $sql = 'SELECT SUM(quantity) FROM troops WHERE combat_id = ? AND user_id = ?';
            $qty = $db->queryOne($sql, [$this->combat_id, $this->user_id]);
            $this->total_units = $qty;
        }
            return $this->total_units;
        */

    }

    public function get_total_damage() {
        $total_damage = 0;
        foreach ($this->troops as $unit_id => $troop) {
            $total_damage += $troop['damage'] * $troop['quantity'];
        }
        return $total_damage;
    }

    /**
     * Only used for computing loot amount
     */
    public function get_total_life() {
        $total_life = 0;

        foreach ($this->troops as $unit_id => $troop) {
            $total_life += $troop['life'] * $troop['quantity'];
        }

        return $total_life;
    }


    /**
     * Removes moving troops from base to combat
     * @param $quantity
     * @param $from
     */
    public function transfert_units($quantity, $from) {
        $db = new Db();
        $db->exec("UPDATE troops SET quantity = quantity - ? WHERE id = ? ", [$quantity, $from]);
        $db->exec("INSERT INTO troops (quantity, unit_id , combat_id) VALUES (?,?,?)", [$quantity, $this->user_id, $this->combat_id]);
    }

    /** Ajoute la flotte au joueur en mettant à jour le score si besoin
     * @param int $unit_id ArmyModel id
     * @param int $quantity quantity to add
     * @param bool|false $update_score will add extrapoints if units is build for instance
     */
    public function add($unit_id, $quantity, $update_score = false) {
        // on ne recrée pas une ligne si elle existe déjà, on met juste les quantités à jour (unicité unit_id, user_id, combat_id)
        $sql = "INSERT troops SET unit_id = ?, user_id = ?, quantity = ?, combat_id = ?
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";
        $db = new Db();
        $db->exec($sql, [$unit_id, $this->user_id, $quantity, $this->combat_id, $quantity]);

        if ($update_score) {
            // on met à jour le score du joueur (argent investi / 100)
            $unit = self::get_unit_infos()[$unit_id];
            $score = round($unit['price'] * $quantity / 100);
            $user = new UserModel($this->user_id);
            $user->increase_score($score);
        }
    }

    private function remove_empty() {
        $db = new Db();
        $db->exec('DELETE FROM troops WHERE quantity <= 0 && combat_id != -1');
    }

    public function update_db() {
        $this->remove_empty();
        foreach ($this->troops as $troop) {


        }
    }

    private function update_troop($unit_id, $quantity) {
        if ($quantity <= 0) {
            $sql = "DELETE FROM troops WHERE combat_id = $this->combat_id";
        } else {
            $sql = "UPDATE troops 
                    SET quantity = $quantity 
                    WHERE user_id = $this->user_id 
                    AND combat_id = $this->combat_id
                    AND unit_id = $unit_id";
        }

        $db = new Db();
        $db->exec($sql);
    }

    private function sort_troops_by_quantities($troops) {
        $quantities = [];
        $sorted_troop = [];
        foreach ($troops as $troop_id => $troop) {
            if ($troop['quantity'] > 0) {
                $quantities[$troop_id] = $troop['quantity'];
                $sorted_troop[$troop_id] = $troop;
            }
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
        $troops = $this->sort_troops_by_quantities($this->troops);        //on range les unités de la plus nombreuse à la moins nombreuse

        $damage_left = $damage ? $damage : 1;  // ce tour çi, la flotte va encaisser $damage en tout
        $skirmish_qty = count($troops);          // nombre d'escamouches
        $skirmish_id = 0;

        $turn_details = '';

        foreach ($troops as $unit_id => $troop) {
            $skirmish_id++;

            // si on a déjà plus dans cette escarmouche, on reporte les dommages aux unités suivantes
            if (intval($troop['quantity']) <= 0)
                continue;

            // quantité de dégats encaissés par cette escamouche
            switch ($skirmish_id) {
                case $skirmish_qty : // la dernière encaise tous les dommages restants;
                    $cur_damage = $damage_left;
                    break;
                case 0 : //la première encaisse au moins 20%
                    $cur_damage = ceil($damage_left * (rand(20, 100) / 100));
                    break;
                default : // les autres encaissent entre 1 et le % restant
                    $cur_damage = ceil($damage_left * (rand(1, 100) / 100));
                    break;
            }

            $damage_left -= $cur_damage;

            // on passe si on fait 0 dégats (ou qu'il n'y en à plus à reporter)
            if ($cur_damage <= 0)
                continue;

            // mise à jour des probabilités pour le tour suivant
            //echo $skirmish_id .' '.$troop['name']." $cur_damage / $damage_left<br>";

            //$unit_life = $troop['life'] * $troop['quantity'];
            $losses = round((($troop['life'] * $troop['quantity']) - $cur_damage) / $troop['life']);
            //$losses = round($troop['quantity'] * ($cur_damage / $unit_life));

            echo "$skirmish_id - $cur_damage - $losses<br>";

            // cas particulier si toutes les unités sont détruites (noter que les dégats sont alors gaspillés)

            if ($troop['quantity'] - $losses <= 0) {
                $losses = $troop['quantity'];
                $this->troops[$unit_id]['quantity'] = 0;
            } else {
                $this->troops[$unit_id]['quantity'] -= $losses;
            }

            // log
            $turn_details .= $troop['name'] . ' : ' . $troop['quantity'];
            if ($losses > 0) $turn_details .= '(-' . $losses . ')'; // affiche les pertes
            if ($skirmish_id != $skirmish_qty) $turn_details .= ', '; // ajoute une virgule entre les unités

            // suppression des unités dans la base
            // todo : j'en étais là, des fois la quantité semble "nulle"
            //$this->update_troop($unit_id, $this->troops[$unit_id]['quantity']);

            // mise à jour des propriétés
            //$this->total_units -= $losses;
            //$this->total_damage -= $troop->damage * $losses;
            //$this->total_life -= $troop->life * $losses;

        }
        return $turn_details . '<br>Dégats reçus :' . $damage;
    }
}

