<?php
$empire = new Empire($user);

if (!empty($_POST) && isset($_POST['ajax'])) {
    switch($_POST['action']){
        case 'build':
            $unit_id = intval($_POST['unit_id']);
            $quantity = intval($_POST['quantity']);
            echo $empire->add_to_queue($unit_id, $quantity);
            break;
        case 'remove_fleet':
            $fleet_id = intval($_POST['fleet_id']);
            $empire->remove_from_fleets($fleet_id);
            break;
        case 'remove_queue':
            echo $empire->remove_from_queue( intval($_POST['queue_id']));
            break;
    }
}




