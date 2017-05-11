<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 28/08/15
 * Time: 16:12
 */
class QueueModel {

    private $user_id;

    function __construct($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * retourne la file d'attente en cours de l'utilisateur
     * @param int
     * @return array
     */
    public function get_all_queues() {
        $sql = "SELECT q.id, q.unit_id, q.position, q.time_left, q.quantity, i.name FROM queue q
        JOIN units_infos i ON i.id = q.unit_id
        WHERE user_id = ? ORDER BY position ASC";

        $db = new Db();
        $queue = $db->query($sql, [$this->user_id]);

        // format de la date pour le décompte javascript
        foreach ($queue as $key => $item)
            $queue[$key]['end_time'] = date("m/d/Y h:i:s a", time() + $item['time_left']);

        //print_r($$queue);
        return $queue;
    }

    /**
     * ajoute un item à la $queue
     * @param $unit_id integer
     * @param $user_id integer
     * @param $name string
     * @param $quantity integer
     * @param $building_time integer
     * @return array last added item
     */
    public function add_to_queue($unit_id, $user_id, $name, $quantity, $building_time) {
        $queue = $this->get_all_queues();
        $position = count($queue);
        if ($position < $this->get_queue_limit()) {
            $db = new Db();
            $sql = "INSERT INTO queue (unit_id, user_id, quantity, position, time_left) VALUES (?,?,?,?,?)";
            $lastInsertedId = $db->exec($sql, [$unit_id, $user_id, $quantity, $position, $building_time]);

            return [
                'id' => $lastInsertedId,
                'name' => $name,
                'unit_id' => $unit_id,
                'position' => $position,
                'time_left' => $building_time,
                'end_time' => date("m/d/Y h:i:s a", time() + $building_time),
                'quantity' => $quantity
            ];
        }
        return [];
    }

    /**
     * retourne un item en cours de construction
     * @param $item_id  int l'ID de queue de l'objet en construction
     * @return array
     */
    public function get_item_from_queue($item_id) {
        $db = new Db();
        return $db->queryOne("SELECT id, unit_id, quantity FROM queue WHERE id = ?", [$item_id]);
    }


    /**
     * récupère le premier élément de la file d'attente
     * @return array|Boolean    retourn false si la queue est vide
     */
    private function get_first_item_from_queue() {
        $sql = "SELECT id, unit_id, quantity, time_left FROM queue WHERE user_id = ? ORDER BY position ";
        $db = new Db();
        return $db->queryOne($sql, [$this->user_id]);
    }

    /**
     * Annule une construction en cours
     * @param $item_id
     */
    public function remove_item_from_queue($item_id) {
        $db = new Db();
        $db->exec("DELETE FROM queue WHERE  id = ?", [$item_id]);

        $queue = $this->get_all_queues();
        if (!empty($queue)) {
            $items = [];
            foreach ($queue as $item)
                $items[] = 'queueID_' . $item['id'];
            $this->sort_queue($items);
        }
    }

    public function sort_queue($items) {
        $sql = "UPDATE queue SET position = ? WHERE id = ?";
        $db = new Db();

        foreach ($items as $position => $queue_id) {
            $db->exec($sql, [$position, $queue_id]);
            //echo $position .' '.$queue_id .' - ';
        }
    }

    /**
     * déduit le temps écoulé des éléments en construction
     * @param $time_diff int
     */
    public function update_queue($time_diff) {
        $item = $this->get_first_item_from_queue();

        if ($item) {
            $time_left = $item['time_left'] - $time_diff;
            /*echo
                'time left : '.$item['time_left'] .
                '<br> time past :' . $time_diff . '<br>';
            */

            if ($time_left > 0) {
                // si la construction n'est pas terminée on déduit le temps passé
                $db = new Db();
                $sql = "UPDATE queue SET time_left = ? WHERE id = ?";
                $db->exec($sql, [$time_left, $item['id']]);
            } else {
                // ajout de la flotte au stock et mise à jour du score
                $fleet = new ArmyModel($this->user_id);
                $fleet->add($item['unit_id'], $item['quantity'], true);

                // effacement de l'élement de la file d'attente
                $this->remove_item_from_queue($item['id']);

                // on met à jour l'élément suivant de la file d'attente
                $this->update_queue(abs($time_left));
            }
        }
    }

    public function get_queue_limit() {
        $db = new Db();
        return intval($db->queryOne("SELECT queue_size FROM modifiers WHERE user_id = ?", [$this->user_id], true));
    }
}
