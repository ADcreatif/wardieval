<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 28/08/15
 * Time: 16:12
 */
class Queue {

    private $user_id;

    function __construct($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * retourne la file d'attente en cours de l'utilisateur
     * @param int
     * @return Array
     */
    public function get_all_queues() {
        $sql = "SELECT q.id, q.position, q.time_left, q.quantity, i.name FROM queue q
        JOIN units_infos i ON i.id = q.unit_id
        WHERE user_id = :user_id ORDER BY position ASC";
        $req = Db::prepare($sql);
        $req->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        $req->execute();
        $queue = $req->fetchAll(PDO::FETCH_ASSOC);

        // format de la date pour le décompte javascript
        foreach ($queue as $key => $item)
            $queue[$key]['end_time'] = date("m/d/Y h:i:s a", time() + $item['time_left']);

        return $queue;
    }

    /**
     * ajoute un item à la queue
     * @param $unit_id
     * @param $user_id
     * @param $quantity
     * @param $building_time
     * @return array last added item
     */
    public function add_to_queue($unit_id, $user_id, $quantity, $building_time) {
        $queue = $this->get_all_queues();
        $position = count($queue);
        if ($position < $this->get_queue_limit()) {
            $sql = "INSERT INTO queue (unit_id, user_id, quantity, position, time_left) VALUES (:unit_id, $user_id, :quantity, $position, $building_time )";
            $req = Db::prepare($sql);
            $req->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
            $req->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $req->execute();

            return [
                'id'        => Db::getLastInsertId(),
                'unit_id'   => $unit_id,
                'position'  => $position,
                'time_left' => $building_time,
                'quantity'  => $quantity
            ];
        }
        return [];
    }

    /**
     * retourne un item en cours de construction
     * @param $item_id
     * @return array
     */
    public function get_item_from_queue($item_id) {
        // récupération des infos sur la queue
        $sql = "SELECT id, unit_id, quantity FROM queue WHERE id = :queue_id";
        $req = Db::prepare($sql);
        $req->bindParam(':queue_id', $item_id, PDO::PARAM_INT);
        $req->execute();
        return $req->fetch(PDO::FETCH_ASSOC);
    }

    private function get_first_item_from_queue() {
        // récupération des infos sur la queue
        $sql = "SELECT id, unit_id, quantity, time_left FROM queue WHERE position = 0 AND user_id = $this->user_id";
        $req = Db::query($sql);

        if ($req->rowCount() > 0)
            return $req->fetch(PDO::FETCH_ASSOC);
        return [];
    }

    /**
     * Annule une construction en cours
     * @param $item_id
     * @return string
     */
    public function remove_item_from_queue($item_id) {
        // on efface la ligne
        $sql = "DELETE FROM queue WHERE  id = :queue_id";
        $req = Db::prepare($sql);
        $req->bindParam(':queue_id', $item_id, PDO::PARAM_INT);
        $req->execute();

        $queue = $this->get_all_queues();
        if (!empty($queue)) {
            $items = [];
            foreach ($queue as $item)
                $items[] = 'queueID_' . $item['id'];
            $this->sort_queue($items);
        }
    }

    public function sort_queue($items) {
        $sql = "UPDATE queue SET position = :position WHERE id = :queue_id";
        $req = Db::prepare($sql);
        $req->bindParam(':position', $position, PDO::PARAM_INT);
        $req->bindParam(':queue_id', $id, PDO::PARAM_INT);

        foreach ($items as $position => $string) {
            $id = intval(substr(strrchr($string, '_'), 1));
            $req->execute();
        }
    }

    /**
     * déduit le temps écoulé des éléments en construction
     * @param $time_diff int
     */
    public function update_queue($time_diff) {
        $item = $this->get_first_item_from_queue();

        if (!empty($item)) {
            $time_left = $item['time_left'] - $time_diff;

            if ($time_left > 0) {
                $sql = "UPDATE queue SET time_left = time_left - :time_diff WHERE id = {$item['id']}";
                $req = Db::prepare($sql);
                $req->bindParam(':time_diff', $time_diff, PDO::PARAM_INT);
                $req->execute();
            } else {

                // ajout de la flotte au stock et mise à jour du score
                $fleet = new Army($this->user_id);
                $fleet->add_troop($item['unit_id'], $item['quantity'], -1, true);

                // effacement de l'élement de la file d'attente
                $this->remove_item_from_queue($item['id']);

                // on met à jour l'élément suivant de la file d'attente
                $this->update_queue(abs($time_left));
            }
        }
    }

    public function get_queue_limit() {
        $req = Db::query("SELECT queue_size FROM modifiers WHERE user_id = $this->user_id");
        return intval($req->fetchColumn());
    }
}
