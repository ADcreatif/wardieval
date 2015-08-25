<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 22/08/15
 * Time: 01:43
 */

require_once 'classes/Fleet.class.php';

class War {

    public static function check_war(){
        $sql = "SELECT id FROM fleets WHERE arrival_time < NOW()";
        $query = Db::query($sql);
        if($query->rowCount() > 0){
            $combats = $query->fetchAll(PDO::FETCH_COLUMN);
            foreach($combats as $fleet_id){
                $i = 0;

                $f_att = new Fleet($fleet_id);
                $f_def = new Fleet($fleet_id,true);

                if($f_def->total_units <= 0){
                    $errors[] = "Il n'y avait personne pour défendre cet empire, vous ressortez victorieux";
                    //return;
                }

                $briefing = '
                    <thead>
                        <tr>
                            <th style="width:50%">Attaquant : '.$user->pseudo.'<br>flotte : '.$f_att->total_units.'  unité(s)</th>
                            <th style="width:50%">Défenseur : '.$defender->pseudo.'<br>flotte : '.$f_def->total_units.'  unité(s)</th>
                        </tr>
                        <tr>
                            <td>
                                <ul>';
                                    foreach($f_att->units as $unit) $fleet_html .= '<li>'.$unit['quantity'].' '.$unit['name'].'</li>';
                $briefing .='   </ul>
                            </td>
                            <td>
                                <ul>';
                                    foreach($f_def->units as $unit) $fleet_html .= '<li>'.$unit['quantity'].' '.$unit['name'].'</li>';
                $briefing .= '  </ul>
                            </td>
                        </tr>
                    </thead>';

                // déroulement du combat en 6 tours
                while($i<6 && $f_att->total_units > 0 && $f_def->total_units > 0 ){
                    $i ++;

                    $dommages = $f_def->total_damage;
                    $def_res = $f_def->split_damage($f_att->total_damage);
                    $att_res = $f_att->split_damage($dommages);

                    $briefing .= "
                    <tbody>
                        <tr>
                            <th colspan=\"2\">Tour $i</th>
                        </tr>
                        <tr>
                            <td>$att_res</td>
                            <td>$def_res</td>
                        </tr>
                    </tbody>";
                }
                //echo $briefing;
            }
        }
    }

    /**
     * enregistre les unitées envoyées à l'ennemi dans la table fleet
     *
     * @param $user User
     * @param $target User
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

            if(isset($array[1]) &&  $array[0] == 'unit_id' && $quantity > 0){
                // on pense à sécuriser les données post avec intval
                $unit_id = intval($array[1]);
                if($units_owned[$unit_id]['quantity'] >= $quantity){
                    $units[$unit_id] = intval($quantity);
                } else {
                    $unit_name = Empire::$units_list[$unit_id]['name'];
                    $errors[] = "vous n'avez pas assez de $unit_name";
                    return;
                }
            }
        }

        $units = json_encode($units);
        $distance = abs($user->score - $target->score);
        $sql="INSERT INTO fleets (user_id, target_id, fleet, arrival_time) VALUES (:user_id, :target_id, :fleet, :arrival_time)";
        $req = Db::prepare($sql);
        $req->execute([
            ":user_id" => $user->id,
            ":target_id" => $target->id,
            ":fleet" => $units,
            ":arrival_time" => date("Y-m-d H:i:s", time('now') + $distance)
        ]);
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