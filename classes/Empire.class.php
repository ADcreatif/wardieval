<?php
/**
 *
 * La classe qui gère l'empire du joueur
 * flottes posséedées, modifiers
 * permet de construire et détruire des unitées
 *
 */
class Empire {

    private $user;

    // unités possédées par le joueur
    private $units_owned = [];

    // informations globales des unités (similaire a tous les joueurs)
    public static $units_list = [];

    // unités et technologies en cours de construction
    private $queue = [];

    // flottes en cours de déplacement (attaque d'un joueur)
    private $fleets = [];

    // les messages de l'utilisateur;
    private $mails = [];

    // les modifiers : facteurs de modification en fonction des technologies recherchées
    public $modifiers = [
        'build_speed' => 1,
        'build_price' =>1,
        'unit_damage' =>1,
        'unit_life' => 1,
    ];

    function __construct(User $user){
        $this->user = $user;
        self::get_unit_list();
        $this->get_queue();
        $this->get_units_owned();
        $this->get_fleets();

        // TODO : implémenter les batiments et modifieurs
        // TODO : gérer les constructions dans une classe dédiée

    }

    public function get_mails(){
        if(count($this->mails) == 0)
            $this->mails = Mail::get_mails($this->user->id);
        return $this->mails;
    }

    public static function get_unit_list(){
        if(count(self::$units_list) == 0){
            $sql = 'SELECT id, name, description, price, building_time, damage, life, image_name FROM units';
            $req = Db::prepare($sql);
            $req->execute();
            self::$units_list = set_id_as_key($req->fetchAll(PDO::FETCH_ASSOC));
        }

        return self::$units_list;
    }

    public function get_units_owned(){
        if (count($this->units_owned) == 0) {
            $sql = "SELECT * FROM units
                    LEFT JOIN (
                        SELECT unit_id, quantity
                        FROM units_owned
                        WHERE user_id = {$this->user->id} AND quantity > 0
                    ) as units_owned ON units.id = unit_id";

            $req = Db::query($sql);
            $this->units_owned = set_id_as_key($req->fetchAll(PDO::FETCH_ASSOC));
        }
        return $this->units_owned;
    }

    private function get_price($unit_id, $quantity){
        return self::$units_list[$unit_id]['price'] * $quantity * $this->modifiers['build_price'];
    }

    private function can_afford($unit_id, $quantity){
        return $this->get_price($unit_id, $quantity) <= $this->user->ressources;
    }

    /**
     * @param $unit_id int
     * @param $quantity int
     *
     * @return Int retourne le temps de construction en secondes
     **/
    private function build_finished_at($unit_id, $quantity) {
        // tps de construction(sec)  * modifier * quantité
        $building_time = intval(self::$units_list[$unit_id]['building_time']) * intval($quantity) * $this->modifiers['build_speed'];

        // on calcule le temps a partir de maintenant
        $finished_at = date("Y-m-d H:i:s", time('now') + $building_time);

        return $finished_at;
    }

    /**
     * Requête pour ajax ajoutant une construction à la file d'attente
     * @param $unit_id
     * @param $quantity
     *
     * @return string
     */
    public function add_to_queue($unit_id, $quantity){

        if($this->can_afford($unit_id, $quantity)){
            $sql = 'INSERT INTO queue (unit_id, user_id, finished_at, quantity) VALUES (:unit_id, :user_id, :finished_at, :quantity )';
            $req = Db::prepare($sql);

            $finished_at = $this->build_finished_at($unit_id, $quantity);

            // on oublie pas de nettoyer les champ (envoyés en $_POST)
            $req->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
            $req->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $req->bindParam(':user_id', $this->user->id, PDO::PARAM_INT);
            $req->bindParam(':finished_at', $finished_at, PDO::PARAM_STR);
            $req->execute();

            $queue_id = Db::getLastInsertId();

            // on arrète la requète sql pour vider le cache PDO
            $req->closeCursor();

            // on déduit les ressources de l'utilisateur
            $new_ressources = $this->user->substract_ressource($this->get_price($unit_id, $quantity));

            return json_encode([
                'status' => 'ok',
                'new_ressources' => $new_ressources,
                'queue' => [
                    'queue_id'=>$queue_id,
                    'name'=>self::$units_list[$unit_id]['name'],
                    'quantity'=>$quantity,
                    'arrival_time' => $finished_at,
                ]
            ]);
        }
        return json_encode(['status' => 'error', 'message' =>"vous n'avez la somme suffisante pour construire $quantity unité(s)"]);
    }

    public function remove_units($unit_id, $quantity, $update_score = false){
        // TODO : add the option to uptate user's score

        if($this->units_owned[intval($unit_id)]['quantity'] - $quantity <= 0)
            $quantity = $this->units_owned[intval($unit_id)]['quantity'];

        $sql = "UPDATE units_owned SET quantity = quantity - :quantity WHERE unit_id = :unit_id AND user_id = :user_id";

        $req = Db::prepare($sql);
        $req->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $req->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
        $req->bindParam(':user_id', $this->user->id, PDO::PARAM_INT);

        $req->execute();
    }

    public function add_unit($unit_id, $quantity, $update_score = false){
        // TODO : add the option to uptate user's score

        $sql = 'INSERT INTO units_owned (unit_id, user_id, quantity)
                VALUES (:unit_id,:user_id,:quantity)
                ON DUPLICATE KEY UPDATE quantity = quantity +  :quantity';
        $req = Db::prepare($sql);
        $req->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
        $req->bindParam(':user_id', $this->user->id, PDO::PARAM_INT);
        $req->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $req->execute();
        $req->closeCursor();

        if($update_score){
            $unit =  self::$units_list[$unit_id];
            $score = $unit['life'] + $unit['damage'];
            $this->user->increase_score($score);
        }
    }


    /**
     * @return Array retourne la file d'attente en cours de l'utilisateur
     */
    public function get_queue(){
        if(count($this->queue) == 0){
            $sql = "SELECT q.id, unit_id, name, finished_at, quantity
                    FROM queue q
                    JOIN units ON units.id = unit_id
                    WHERE user_id = {$this->user->id}
                    ORDER BY finished_at ASC
            ";

            $req = Db::prepare($sql);
            $req->execute();

            if($req->rowCount() > 0){
                //on formate la durée
                foreach($req->fetchAll(PDO::FETCH_ASSOC) as $key => $queue_item){
                    // il faut formater
                    $this->queue[$key] = $queue_item;
                    $this->queue[$key]['time_left'] = sec_to_hms(get_time_diff('now',$queue_item['finished_at']));
                }
                //$this->queue = $queue;
            }
        }
        return $this->queue;
    }

    public function remove_from_queue($queue_id){
        return json_encode([$queue_id]);
    }

    /**
     * récupère toutes les flottes en cours d'attaque de l'utilisateur
     * @return array
     */
    public function get_fleets(){
        if(count($this->fleets) == 0){
            $sql = "SELECT f.id, u.pseudo as target, arrival_time FROM fleets f
                    JOIN users u ON u.id = target_id
                    WHERE arrival_time > NOW()
                    ORDER BY arrival_time
                    ";
            $req = Db::query($sql);

            if($req->rowCount() > 0){
                foreach($req->fetchAll(PDO::FETCH_ASSOC) as $key => $fleet){
                    $this->fleets[$key] = $fleet;
                    $this->fleets[$key]['time_left'] = sec_to_hms(get_time_diff('now',$fleet['arrival_time']));
                }
            }
        }
        return $this->fleets;
    }

    /**
     * efface une flotte en cours d'attaque et remet les unités en stock
     * @param $fleet_id
     */
    public function remove_from_fleets($fleet_id){
        // obligé d'avoir un json_encode en retour sans doute pour définir le header
        //echo json_encode($fleet_id);
        $fleet = new Fleet($fleet_id);
        $fleet->reset_fleet();
    }



    /**
     * Transfert les constructions en cours si leur temps est dépassé dans la table des constructions terminées
     */
    public static function building_time_is_over(){
        // on sélectionne toutes les entrées dont la date est dépassée
        $sql ="SELECT id, user_id, unit_id, quantity  FROM queue WHERE finished_at <= NOW()";
        $req = Db::prepare($sql);
        $req->execute();

        //s'il y a des résultats on déplace le résultat de queue à units_owned
        if($req->rowCount() > 0){
            $queued_items = $req->fetchAll(PDO::FETCH_ASSOC);
            //$req->closeCursor();

            // ajout des unitées
            $sql = 'INSERT INTO units_owned (unit_id, user_id, quantity) VALUES (:unit_id,:user_id,:quantity)
                    ON DUPLICATE KEY UPDATE quantity = quantity +  :quantity;
                    DELETE FROM queue WHERE id=:id';
            $req = Db::prepare($sql);

            foreach($queued_items as $q){
                // on ajoute les nouvelles unitées dans 'unit_owned'
                // on efface la ligne dans 'queue'
                $req->execute([
                    ':unit_id'      => $q['unit_id'],
                    ':user_id'      => $q['user_id'],
                    ':quantity'     => $q['quantity'] ,
                    ':id'           => $q['id'],
                ]);
                $req->closeCursor();

                // on met à jour le score du joueur
                $unit =  self::get_unit_list()[$q['unit_id']];
                $score = $unit['life'] + $unit['damage'];

                $user = new User($q['user_id']);
                $user->increase_score($score);
            }
        }
    }
}
