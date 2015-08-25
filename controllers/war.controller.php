<?php

$select_fleet = 0;
$select_target = 0;

// lancement de l'attaque
if(isset($_POST['start_war'])){
    $defender = new User($_POST['defender_id']);

    if(!Combat::is_available_target($user->score, $defender->score)){
        $errors[] = "$defender->pseudo n'est pas a votre portée";
        return;
    }

    if($user->id == $defender->id){
        $errors[] = "Vous ne pouvez pas vous attaquer";
        return;
    }

    Combat::send_fleet($user, $defender);

    // redirection pour éviter le renvoi d'une nouvelle flotte en actualisant la page
    $url = 'Location:' . _ROOT_ . 'war';
    header("$url");


// fleet selection
} elseif(isset($_GET['action']) && $_GET['action'] == 'attack' && isset($_GET['param'])){

    $defender = new User($_GET['param']);

    if(!Combat::is_available_target($user->score, $defender->score))
        $errors[] = "$defender->pseudo n'est pas a votre portée";

    $empire = new Empire($user);
    $fleet = $empire->get_units_owned();
    $select_fleet = 1;

// target selection
} else {
    $select_target = 1;
    $targets = Combat::get_available_target($user->id);
}
