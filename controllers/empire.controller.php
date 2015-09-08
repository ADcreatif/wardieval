<?php

if (!User::isLogged())
    $errors[] = "erreur vous n'êtes plus connecté";
else {

    $army_owned = new Army($user->id);

    $mails = Mail::get_mails($user->id);
    $queues = $queue->get_all_queues();

    if (!empty($_POST) && isset($_POST['ajax'])) {
        switch ($_POST['action']) {

            case 'mark_as_read' :
                $mail = new Mail(intval($_POST['item_id']));
                $mail->update_value('unread', 0);
                break;

            case 'send_mail' :
                $mail = new Mail();
                echo $mail->send_mail($_POST['to'], $_POST['content'], $_POST['topic'], $user->id);
                break;

            case 'delete_mail' :
                $mail = new Mail(intval($_POST['item_id']));
                $mail->delete();
                break;

            case 'add_to_queue':
                if (count($queues) < $queue->get_queue_limit()) {
                    $unit_id = intval($_POST['unit_id']);
                    $quantity = intval($_POST['quantity']);
                    $price = round($army_owned->troops[$unit_id]->price * $quantity);
                    $building_time = round($army_owned->troops[$unit_id]->building_time * $quantity);
                    if ($price <= $user->ressources) {
                        $item_added = $queue->add_to_queue($unit_id, $user->id, $quantity, $building_time);
                        $new_ressources = $user->increase_ressource(-$price);
                        echo json_encode(['status' => 'ok', 'new_ressources' => $new_ressources, 'queue' => $item_added]);
                    } else
                        echo json_encode(['status' => 'error', 'message' => "vous ne pouvez pas construire $quantity unité(s)"]);
                } else
                    echo json_encode(['status' => 'error', 'message' => "votre file d'attente est pleine"]);
                break;

            case 'remove_queue':
                $item = $queue->get_item_from_queue($_POST['item_id']);

                // on ne rend que 80% de la somme
                $price = round($army_owned->troops[$item['unit_id']]->price * $item['quantity'] * .8);
                $new_ressources = $user->increase_ressource($price);

                $queue->remove_item_from_queue($item['id']);
                echo json_encode(['new_ressources' => $new_ressources]);
                break;

            case 'sort_queue':
                $queue->sort_queue($_POST['positions']);
                echo json_encode($queue->get_all_queues());
                break;

            case 'cancel_attack':
                $combat = new Combat($_POST['item_id']);
                echo $combat->reset_army();
                break;
        }
    }
}



