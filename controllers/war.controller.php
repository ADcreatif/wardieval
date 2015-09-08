<?php

$select_fleet = 0;
$select_target = 0;

if (!User::isLogged())
    $errors[] = "erreur vous n'êtes plus connecté";
else {
    // lancement de l'attaque
    if (isset($_POST['start_war'])) {

        User::exists_in_database('id', $_POST['target_id'], 'users');
        $target = new User($_POST['target_id']);

        if (!Combat::is_available_target($user->score, $target->score)) {
            $errors[] = "$target->pseudo n'est pas a votre portée";
            return;
        }
        /* TODO : décommenter pour interdire l'attaque sur soi
            if ($user->id == $defender->id) {
                $errors[] = "C'est une mutinerie ? Non sérieusement vous ne pouvez pas vous attaquer.";
                return;
            }
        */

        $target = new User($_POST['target_id']);

        $combat = new Combat();
        $combat->hydrate([
            'attacker_id'  => $user->id,
            'target_id'    => $target->id,
            'arrival_time' => date("Y-m-d H:i:s", time('now') + abs($user->score - $target->score))
        ]);

        $combat_id = intval($combat->add());

        $army_moving = new Army($user->id, $combat_id);
        $army_owned = new Army($user->id);

        foreach ($_POST as $post_key => $quantity) {
            $post_arr = explode('_', $post_key);
            if ($post_arr[0] == 'unitID' && $quantity > 0) {
                $army_moving->add_troop(intval($post_arr[1]), intval($quantity), $combat_id);
                $army_owned->add_troop(intval($post_arr[1]), intval(-$quantity));
            }
        }

        // redirection pour éviter le renvoi d'une nouvelle flotte en actualisant la page
        /* TODO décommenter en prod
        $url = 'Location:' . _ROOT_ . 'war';
        header("$url");
        */

        // fleet selection
    } elseif (isset($_GET['action']) && $_GET['action'] == 'attack' && isset($_GET['param'])) {
        $army_owned = new Army($user->id);

        $target = new User(intval($_GET['param']));

        if (!Combat::is_available_target($user->score, $target->score))
            $errors[] = clean_html($target->pseudo) . " n'est pas a votre portée";

        $select_fleet = 1;

        // target selection
    } else {
        $select_target = 1;
        $targets = Combat::get_available_target($user->score);
    }
}
