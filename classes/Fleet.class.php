<?php

/**
 * Class Fleet
 * Gère les flottes qui sont envoyées vers un autre joueur
 */
class Fleet {
    public $units = [];
    public $total_units = 0;
    public $total_damage = 0;
    public $total_life; // uniquement utilisé pour savoir combien de ressources peuvent être pillées
    private $empire;
    private $fleet_id;

    function __construct($fleet_id, $is_defender = false) {
        $this->fleet_id = intval($fleet_id);

        $sql = "SELECT user_id, target_id, fleet FROM fleets WHERE id = {$this->fleet_id} LIMIT 1";
        $query = Db::query($sql);
        if ($query->rowCount() > 0) {
            $res = $query->fetch(PDO::FETCH_ASSOC);

            $user_id = $is_defender ? $res['target_id'] : $res['user_id'];
            $this->empire = new Empire(new User($user_id));

            if ($is_defender)
                $this->set_defence_fleet($user_id);
            else {
                $this->set_attack_fleet(json_decode($res['fleet']));
            }
        }
    }

    /**
     *  Récupère et formate les flottes possédées par le défenseur
     *
     * @param $user_id int
     */
    private function set_defence_fleet($user_id) {
        $sql = "SELECT unit_id, quantity FROM units_owned WHERE user_id = $user_id AND quantity > 0";
        if ($query = Db::query($sql)) {
            $fleet = [];
            foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $unit) {
                $fleet[$unit['unit_id']] = $unit['quantity'];
            }
            $this->set_attack_fleet($fleet);
        }
    }

    /**
     *  Récupère et formate les flottes possédées par l'attaquant
     *
     * @param $units Array
     */
    private function set_attack_fleet($units) {

        $quantities = [];
        $fleet = [];

        foreach ($units as $unit_id => $quantity) {
            if ($quantity == 0)
                continue;
            $unit_infos = Empire::$units_list[$unit_id];
            $fleet[$unit_id] = [
                'id'       => $unit_id,
                'name'     => $unit_infos['name'],
                'damage'   => $unit_infos['damage'] * $this->empire->modifiers['unit_damage'],
                'life'     => $unit_infos['life'] * $this->empire->modifiers['unit_life'],
                'quantity' => $quantity,
            ];
            $quantities[$unit_id] = $quantity;
            $this->total_units += $quantity;
            $this->total_damage += $fleet[$unit_id]['damage'] * $quantity;
        }

        // on range les flottes de la plus nombreuse à la moins nombreuse
        array_multisort($quantities, SORT_DESC, $fleet);
        $this->units = $fleet;

    }

    /**
     * uniquement utilisé pour savoir combien de ressources peuvent être pillées
     */
    public function get_total_life() {
        foreach ($this->units as $unit) {
            $this->total_life += $unit['life'] * $unit['quantity'];
        }
        return $this->total_life;
    }

    /**
     * Efface la flotte en cours de déplacement et remet les unités dans le stock du joueur
     */
    public function reset_fleet() {

        foreach ($this->units as $unit_id => $unit) {
            $this->empire->add_unit($unit_id, $unit['quantity']);
        }
        $sql = "DELETE FROM fleets WHERE id = :fleet_id";
        $del_fleet = Db::prepare($sql);
        $del_fleet->bindParam(':fleet_id', $this->fleet_id, PDO::PARAM_INT);
        $del_fleet->execute();
    }

    /**
     * permet de répartir aléatoirement les dégats sur les différentes unités
     *
     * @param $damage int quantité totale de dégats infligés ce tour
     *
     * @return string le résultat de cette escarmouche
     */
    public function split_damage($damage) {
        $damage_left = $damage;
        $percent_left = 99; // on limite à 99% pour éviter les division par 0 si jammais rand = 100
        $nb_fleet = count($this->units);
        $result = '';

        $i = 0;
        foreach ($this->units as $unit) {

            $unit_life = $unit['life'] * $unit['quantity'];
            $units_nb = $unit['quantity'];

            // si on a déjà plus d'unités, on reporte les dommages aux unités suivantes
            if ($units_nb <= 0)
                continue;

            switch ($i) {
                case $nb_fleet - 1 : // la dernière encaise tous les dommages restants
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
            $losses = round($units_nb * $cur_damage / $unit_life);

            // dégats restants
            $damage_left -= $cur_damage;

            // cas particulier si toutes les unités sont détruites (noter que les dégats sont alors gaspillés)

            if ($units_nb - $losses <= 0) {
                $losses = $units_nb;
                $this->units[$unit['id']]['quantity'] = 0;
            } else {
                $this->units[$unit['id']]['quantity'] -= $losses;
            }

            // log
            $result .= $unit['name'] . ' : ' . $units_nb;
            if ($losses > 0) $result .= '(-' . $losses . ')'; // affiche les pertes
            if ($i != $nb_fleet) $result .= ', '; // ajoute une virgule entre les unités

            // TODO : augmentation du score de l'attaquant si destruction d'unités

            // suppression des unités dans la base
            $this->empire->remove_units($unit['id'], $losses);

            // mise à jour des propriétés
            $this->total_units -= $losses;
            $this->total_damage -= $unit['damage'] * $losses;

            $i ++;
        }
        return $result . '<br>Dégats reçu :' . $damage;
    }
}