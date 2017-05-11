<?php

class EmpireController extends FrontController {

    protected function display() {

        $userSession = new UserSession();
        if (!$userSession->isLogged())
            redirect();

        $user = new UserModel($_SESSION['user']['id']);
        $queue = new QueueModel($user->id);
        $troops = new ArmyModel($user->id);
        $combat = new CombatModel();

        return [
            'troops' => $troops->get_troops(),
            'troopsSent' => $combat->get_attacking_troops($user->id),
            'incomingAttacks' => $combat->get_incoming_fleets($user->id),
            'queues' => $queue->get_all_queues()
        ];
    }

    public function ajax() {

        $user = new UserModel($_SESSION['user']['id']);
        $queue = new QueueModel($user->id);
        $army = new ArmyModel($user->id);
        $troops = $army->get_troops();

        switch ($_POST['action']) {
            case 'get_queue':
                echo json_encode($queue->get_all_queues());
                break;

            case 'get_troops' :
                echo json_encode($troops);
                break;

            case 'get_item_from_queue' :
                echo json_encode($queue->get_item_from_queue($_POST['item_id']));
                break;

            case 'add_to_queue':

                $queueList = $queue->get_all_queues();

                if (count($queueList) < $queue->get_queue_limit()) {

                    $unit_id = intval($_POST['unit_id']);
                    $quantity = intval($_POST['quantity']);
                    $price = round($troops[$unit_id]['price'] * $quantity);
                    $building_time = round($troops[$unit_id]['building_time'] * $quantity);
                    $name = $troops[$unit_id]['name'];

                    if ($price <= $user->ressources) {
                        $item_added = $queue->add_to_queue($unit_id, $user->id, $name, $quantity, $building_time);
                        $new_ressources = $user->increase_ressource(-$price);
                        echo json_encode(['status' => true, 'new_ressources' => $new_ressources, 'queue' => $item_added]);
                    } else
                        echo json_encode(['status' => 'error', 'message' => "vous ne pouvez pas construire $quantity unité(s)"]);
                } else
                    echo json_encode(['status' => 'error', 'message' => "votre file d'attente est pleine"]);
                break;

            case 'remove_item_from_queue':
                $item = $queue->get_item_from_queue($_POST['item_id']);

                // si l'objet est toujours en queue (il est peut être terminé entre temps)
                if ($item) {
                    // on ne rend que 80% de la somme
                    $price = round($troops[$item['unit_id']]['price'] * $item['quantity'] * .8);
                    $new_ressources = $user->increase_ressource($price);

                    $queue->remove_item_from_queue($item['id']);
                    echo json_encode(['new_ressources' => $new_ressources]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => "L'entraînement est déjà terminé pour ces unitées."]);
                }
                break;

            case 'sort_queue':
                $queue->sort_queue($_POST['sortedID']);
                break;

            case 'cancel_attack':
                $combat = new CombatModel($_POST['item_id']);
                echo $combat->reset_army();
                break;
        }
    }
}


