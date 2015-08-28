<?php
if ($user)
    $empire = new Empire($user);

if (!empty($_POST) && isset($_POST['ajax'])) {
    switch($_POST['action']){

        case 'build':
            $unit_id = intval($_POST['unit_id']);
            $quantity = intval($_POST['quantity']);
            echo $empire->add_to_queue($unit_id, $quantity);
            break;

        case 'remove_fleet':
            $empire->remove_from_fleets( intval($_POST['item_id']));
            break;

        case 'remove_queue':
            echo $empire->remove_from_queue(intval($_POST['item_id']));
            break;

        case 'mark_as_read' :
            $mail = new Mail(intval($_POST['item_id']));
            $mail->update('unread', 0);
            break;

        case 'send_mail' :
            $mail = new Mail();
            echo $mail->send_mail($_POST['to'], $_POST['message'], $_POST['topic'], $user->id);
            break;

        case 'delete_mail' :
            $mail = new Mail(intval($_POST['item_id']));
            $mail->delete();
    }
}




