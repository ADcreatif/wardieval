<?php
/**
 * Cette classe gère les combats de façon globale, elle vérifie si la flotte est arrivée à destination et
 * envoit les rapports de combats aux joueurs avant d'effacer la flotte
 */

require_once 'classes/Fleet.class.php';

class Combat {

    /**
     * Check if any combat is happening and then solve them
     */
    public static function solve_combats(){
        $sql = "SELECT id as fleet_id, user_id, target_id FROM fleets WHERE arrival_time < NOW()";
        $query = Db::query($sql);

        if($query->rowCount() > 0){
            $combats = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($combats as $combat){
                $i = 0;

                $attacker = new User($combat['user_id']);
                $target = new User($combat['target_id']);

                $f_att = new Fleet($combat['fleet_id']);
                $f_def = new Fleet($combat['fleet_id'],true);

                if($f_def->total_units <= 0){
                    $message = "Il n'y avait personne pour défendre cet empire, vous ressortez victorieux";
                } else {
                    $message = '<table><thead><tr><th style="width:50%">Attaquant : '.$attacker->pseudo.'<br>flotte : '.$f_att->total_units.'  unité(s)</th><th style="width:50%">Défenseur : '.$target->pseudo.'<br>flotte : '.$f_def->total_units.'  unité(s)</th></tr><tr><td><ul>';
                    foreach($f_att->units as $unit) $message .= '<li>'.$unit['quantity'].' '.$unit['name'].'</li>';
                    $message .='</ul></td><td><ul>';
                    foreach($f_def->units as $unit) $message .= '<li>'.$unit['quantity'].' '.$unit['name'].'</li>';
                    $message .= '</ul></td></tr></thead>';

                    // déroulement du combat en 6 tours
                    while($i<6 && $f_att->total_units > 0 && $f_def->total_units > 0 ){
                        $i ++;

                        $dommages = $f_def->total_damage;
                        $def_res = $f_def->split_damage($f_att->total_damage);
                        $att_res = $f_att->split_damage($dommages);

                        $message .= "<tbody><tr><th colspan=\"2\">Tour $i</th></tr><tr><td>$att_res</td><td>$def_res</td></tr></tbody>";
                    }

                    // résultat du combat
                    if($f_def->total_units <= 0){
                        $winner = 'Vainqueur : '. $attacker->pseudo;
                    } elseif($f_att->total_units <= 0)
                        $winner = 'Vainqueur : '. $target->pseudo;
                    else
                        $winner = 'Aucun vainqueur';

                    $message .= "<tfooter><tr><td colspan='2'>$winner</td></tr></tfooter></table>";
                }

                // Efface la flotte et remet les survivants dans la flotte de l'attaquant
                // TODO : actuellement les unitées sont directement ajoutées au stock, il faut leur donner un temps de retour et la possibilité d'annuler un combat lancé.
                $f_att->reset_fleet();

                send_mail($attacker->id, $message, 'Rapport de combat ('.$target->pseudo.')' );
                send_mail($target->id, $message, 'Vous avez été attaqué ('.$attacker->pseudo.')' );

                //echo $message;
            }
        }
    }

    /**
     * enregistre les unitées envoyées à l'ennemi dans la table fleet
     *
     * @param $user User
     * @param $target User
     *
     */
    public static function send_fleet($user, $target){
        global $errors;
        $units = [];

        $empire = new Empire($user);
        $units_owned = $empire->get_units_owned();

        // TODO : améliorer le calcul distance/temps qui sépare deux joueurs

        // récupération des flottes envoyées par l'attaquant
        // on en profite pour vérifier que les unités sont bien en stock;
        foreach($_POST as $input_name => $quantity){
            $array = explode('-',$input_name);
            $quantity + intval($quantity);

            if(isset($array[1]) &&  $array[0] == 'unit_id' && $quantity > 0){
                // on pense à sécuriser les données post avec intval
                $unit_id = intval($array[1]);
                if($units_owned[$unit_id]['quantity'] >= $quantity){
                    $units[$unit_id] = $quantity;
                    $empire->remove_units($unit_id, $quantity);
                } else {
                    $unit_name = Empire::$units_list[$unit_id]['name'];
                    $errors[] = "vous n'avez pas assez de $unit_name";
                    return;
                }
            }
        }

        $units = json_encode($units);
        $arrival_time = date("Y-m-d H:i:s", time('now') + abs($user->score - $target->score));
        $sql="INSERT INTO fleets (user_id, target_id, fleet, arrival_time) VALUES (:user_id, :target_id, :fleet, :arrival_time)";
        $req = Db::prepare($sql);
        $req->execute([
            ":user_id" => $user->id,
            ":target_id" => $target->id,
            ":fleet" => $units,
            ":arrival_time" => $arrival_time
        ]);
        $errors[] = 'Vos unitées ont bien étés envoyées. ';
        $errors[] = 'attaque prévue : '.$arrival_time;
    }

    /**
     * Retourne la liste des utilisateurs si leur score est compris entre 95% et 120% (
     * (plus ou moins 50 points tant qu'il y'a peu de joueurs)
     *
     * @param $score int le score de l'utilisateur attaquant
     *
     * @return array liste des utilisateurs à portée
     */
    public static function get_available_target($score){
        $score = intval($score);

        $min =  -50 - $score - $score * 5 / 100;
        $max =  50 + $score + $score * 20 / 100;
        $sql= "SELECT id,pseudo,score FROM users WHERE score BETWEEN $min AND $max ";
        $res = Db::query($sql);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function is_available_target($attaquer_score, $defender_score){
        $min =  -50 - $attaquer_score - $attaquer_score * 5 / 100;
        $max =  50 + $attaquer_score + $attaquer_score * 20 / 100;

        return $defender_score > $min && $defender_score < $max;
    }

} 