<?php

/**
 * Cette classe gère les combats de façon globale, elle vérifie si la flotte est arrivée à destination et
 * envoit les rapports de combats aux joueurs avant d'effacer la flotte
 * ses méthodes sont statiques car elles sont appelées sans forcément qu'un joueur soit instancié ni connecté
 */
class Combat extends ObjectModel {
    public $attacker_id;
    public $target_id;

    protected $table = 'combats';
    protected static $definition = [
        ['name' => 'attacker_id', 'type' => self::TYPE_INT],
        ['name' => 'target_id', 'type' => self::TYPE_INT],
        ['name' => 'arrival_time', 'type' => self::TYPE_DATE],
    ];

    /**
     * résolution des combats (chargé depuis index.php)
     **/
    public function solve_combats() {

        // identification de l'attaquant et du défenseur
        $attacker = new User($this->attacker_id);
        $defender = new User($this->target_id);

        // ... et de leur armée
        $army_att = new Army($this->attacker_id, $this->id);
        $army_def = new Army($this->target_id);

        // affichage de l'armée attaquante
        $mess_att = '';
        foreach ($army_att->troops as $unit) {
            if ($unit->quantity > 0)
                $mess_att .= '<li>' . $unit->quantity . ' ' . $unit->name . '</li>';
        }

        // affichage de l'armée en défence
        $mess_def = '';
        if ($army_def->total_units > 0)
            foreach ($army_def->troops as $unit) $mess_def .= '<li>' . $unit->quantity . ' ' . $unit->name . '</li>';
        else $mess_def .= "<li>Il n'y avait personne pour défendre cet empire</li>";

        $message = "<table><tr>" .
            "<th style=\"width:50%\">Attaquant : $attacker->pseudo<br>flotte : $army_att->total_units unité(s)</th>" .
            "<th style=\"width:50%\">Défenseur : $defender->pseudo<br>flotte : $army_def->total_units unité(s)</th>" .
            "</tr><tr><td><ul>$mess_att</ul></td><td><ul>$mess_def</ul></td></tr>";

        if ($army_def->total_units > 0) {
            // résolution du combat en 6 tours (on boucle tant qu'il reste des unités à un joueur
            $i = 0;
            while ($i < 6 && $army_att->total_units > 0 && $army_def->total_units > 0) {
                $i++;

                // il faut temporiser les dégats de l'attaquant pour qu'il puisse attaquer de toute sa force
                // car va lui détruire des unités dès sa première attaque
                $dommages = $army_def->total_damage;
                $def_res = $army_def->split_damage($army_att->total_damage);
                $att_res = $army_att->split_damage($dommages);

                $message .= "<tbody><tr><th colspan=\"2\">Tour $i</th></tr><tr><td>$att_res</td><td>$def_res</td></tr></tbody>";
            }
        }

        // résultat du combat
        if ($army_def->total_units <= 0) {
            $available = round($defender->ressources / 3);
            $can_take = round($army_att->total_life);
            $amount = $available - $can_take > 0 ? $can_take : $available;
            $attacker->increase_ressource($amount);
            $defender->increase_ressource(-$amount);
            $result = 'Vainqueur : ' . $attacker->pseudo . '<br>Ressources pillées : ' . $amount;
        } elseif ($army_att->total_units <= 0) {
            $result = 'Vainqueur : ' . $defender->pseudo;
        } else
            $result = 'Aucun vainqueur';

        $message .= "<tr><td colspan='2'>$result</td></tr></table>";

        $this->reset_army();

        // envoi les rapports de combats
        $mail = new Mail();
        $mail->send_mail($attacker->id, $message, 'Rapport de combat (' . $defender->pseudo . ')');
        // on changer uniquement le destinataire et on le renvoi
        $mail->recipient = $defender->id;
        $mail->add();

    }

    /** Cette fonction transfert les unités en attaque au stock et efface le combat
     * elle retourne la nouvelle armée pour l'affichage en ajax
     * @return array new updated Army_owned list
     */
    public function reset_army() {
        $army_mobile = new Army($this->attacker_id, $this->id);
        $army_owned = new Army($this->attacker_id);

        if (count($army_mobile->troops) > 0 && $army_mobile->total_units > 0) {
            foreach ($army_mobile->troops as $unit_id => $troop) {
                if ($troop->quantity > 0)
                    $army_owned->add_troop($unit_id, $troop->quantity);

                $troop = new Troop($troop->id);
                $troop->delete();
            }
        }
        $this->delete();
        return $army_owned->get_troops();
    }

    /** récupère la liste des attaques à réaliser
     * @return array
     */
    public static function get_arrived_troops() {
        $sql = "SELECT id, attacker_id, target_id FROM combats WHERE arrival_time <= NOW()";
        $query = Db::query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * /**
     * récupère toutes les flottes en cours d'attaque de l'utilisateur
     * @param $user_id int
     * @param $arrived bool
     * @return array
     */
    public static function get_attacking_troops($user_id, $arrived = false) {
        $where = $arrived ? 'AND arrival_time <= NOW()' : 'AND arrival_time > NOW()';

        $sql = "SELECT m.arrival_time, m.id, u.pseudo FROM combats m
                JOIN users u on u.id = m.target_id
                WHERE attacker_id = :user_id $where";
        $req = Db::prepare($sql);
        $req->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $req->execute();

        if ($req->rowCount() > 0)
            return $req->fetchAll(PDO::FETCH_OBJ);
        return [];
    }

    /** retourne les invasions en approche
     * @param $user_id
     * @return array
     */
    public static function get_incoming_fleets($user_id) {
        $sql = "SELECT u.pseudo, arrival_time FROM combats
                JOIN users u ON u.id = attacker_id
                WHERE arrival_time > NOW() AND target_id = $user_id
                ORDER BY arrival_time";
        $req = Db::query($sql);
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne la liste des utilisateurs si leur score est compris entre 95% et 120% (
     * @param $score int le score de l'utilisateur attaquant
     * @return array liste des utilisateurs à portée
     */
    public static function get_available_target($score) {
        $min = ($score - $score * 10 / 100) - 1000;
        $max = ($score + $score * 40 / 100) + 5000;
        $sql = "SELECT id,pseudo,score FROM users WHERE score BETWEEN $min AND $max ORDER BY score DESC";
        $res = Db::query($sql);
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function is_available_target($attaquer_score, $defender_score) {
        $min = ($attaquer_score - $attaquer_score * 10 / 100) - 1000;
        $max = ($attaquer_score + $attaquer_score * 40 / 100) + 5000;

        return $defender_score > $min && $defender_score < $max;
    }

}
