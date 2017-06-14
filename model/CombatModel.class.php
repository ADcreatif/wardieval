<?php

/**
 * Cette classe gère les combats de façon globale, elle vérifie si la flotte est arrivée à destination et
 * envoit les rapports de combats aux joueurs avant d'effacer la flotte
 * ses méthodes sont statiques car elles sont appelées sans forcément qu'un joueur soit instancié ni connecté
 */
class CombatModel {
    public $attacker_id;
    public $target_id;
    private $arrival_time;
    private $id;

    function __construct($id = null) {
        if ($id) {
            $this->id = intval($id);
            $this->get();
        }
    }

    function get() {
        $db = new Db();
        $combat = $db->queryOne('SELECT attacker_id, target_id, arrival_time FROM combats WHERE id = ?', [$this->id]);
        $this->attacker_id = $combat['attacker_id'];
        $this->target_id = $combat['target_id'];
        $this->arrival_time = $combat['target_id'];
    }

    function add($attacker_id, $target_id, $arrival_time) {
        $db = new Db();
        return $db->exec("INSERT INTO combats VALUES ('', ?, ?, ?)", [$attacker_id, $target_id, $arrival_time]);
    }

    function delete() {
        $db = new Db();

    }

    /**
     * résolution des combats (chargé depuis index.php)
     **/
    public function solve_combats() {

        // identification de l'attaquant et du défenseur
        $attacker = new UserModel($this->attacker_id);
        $defender = new UserModel($this->target_id);

        // ... et de leur armée
        $army_att = new ArmyModel($attacker->id, $this->id);
        $army_def = new ArmyModel($defender->id);

        // infos pour le rapport
        $att['pseudo'] = $attacker->pseudo;
        $def['pseudo'] = $defender->pseudo;
        $att['start'] = ['troops' => $army_att->get_troops(), 'quantity' => $army_att->get_total_units()];
        $def['start'] = ['troops' => $army_def->get_troops(), 'quantity' => $army_def->get_total_units()];

        $turns = [];

        if ($army_def->get_total_units() > 0) {
            // résolution du combat en 6 tours (on boucle tant qu'il reste des unités à un joueur
            $i = 0;
            while ($i < 6 && $army_att->get_total_units() > 0 && $army_def->get_total_units() > 0) {
                $i++;

                // temporisation de la première attaque (pour le coup de retour)
                $dommages = $army_def->get_total_damage();

                // tours de combat et log
                $turns[$i] = [
                    'def' => $army_def->split_damage($army_att->get_total_damage()),
                    'att' => $army_att->split_damage($dommages)
                ];
            }
        }

        $loot_amount = 0;

        // si l'attaquant gagne
        if ($army_def->get_total_units() <= 0) {
            $available = round($defender->ressources / 3);
            $can_take = round($army_att->get_total_life());
            $loot_amount = $available - $can_take > 0 ? $can_take : $available;
            $attacker->increase_ressource($loot_amount);
            $defender->increase_ressource(-$loot_amount);
        }

        // TODO : $this->reset_army();

        // envoi les rapports de combats
        $mail = new MailModel();

        $att['end'] = ['quantity' => $army_att->get_total_units()];
        $def['end'] = ['quantity' => $army_def->get_total_units()];

        $template_vars = [
            'att' => $att,
            'def' => $def,
            'turns' => $turns,
            'loot_amount' => $loot_amount
        ];

        $message = $mail->get_content('combat', $template_vars);
        $mail->send_mail($attacker->id, $message, 'Rapport de combat (' . $defender->pseudo . ')');
        $mail->send_mail($defender->id, $message, 'Rapport de combat (' . $defender->pseudo . ')');
    }

    /** Cette fonction transfert les unités en attaque au stock et efface le combat
     * elle retourne la nouvelle armée pour l'affichage en ajax
     * @return array new updated Army_owned list
     */
    public function reset_army() {
        $army = new ArmyModel($this->attacker_id);
        $army_mobile = $army->get_troops($this->id);
        $army_owned = $army->get_troops();

        if ($army->get_total_units() > 0) {
            foreach ($army_mobile->troops as $unit_id => $troop) {
                if ($troop->quantity > 0)
                    $army_owned->add_troop($unit_id, $troop->quantity);

                $troop = new TroopModel($troop->id);
                $troop->delete();
            }
        }
        $this->delete();
        return $army_owned->getTroops();
    }

    /** récupère la liste des attaques à réaliser
     * @return array
     */
    public static function get_arrived_troops() {
        $db = new Db();
        return $db->query('SELECT id, attacker_id, target_id FROM combats WHERE arrival_time <= NOW()');
    }

    /**
     * récupère toutes les flottes en cours d'attaque de l'utilisateur
     * @param $user_id int
     * @param $arrived bool
     * @return array
     */
    public function get_attacking_troops($user_id, $arrived = false) {
        $where = $arrived ? 'AND arrival_time <= NOW()' : 'AND arrival_time > NOW()';

        $sql = "SELECT c.arrival_time, c.id, u.pseudo FROM combats c
                JOIN users u on u.id = c.target_id
                WHERE attacker_id = ? $where";
        $db = new Db();

        return $db->query($sql, [$user_id]);
    }

    /** retourne les invasions en approche
     * @param $user_id
     * @return array
     */
    public function get_incoming_fleets($user_id) {
        $sql = "SELECT u.pseudo, arrival_time FROM combats
                JOIN users u ON u.id = attacker_id
                WHERE arrival_time > NOW() AND target_id = ?
                ORDER BY arrival_time";

        $db = new Db();
        return $db->query($sql, [$user_id]);
    }

    /**
     * Retourne la liste des utilisateurs si leur score est compris entre 95% et 120% (
     * @param $score int le score de l'utilisateur attaquant
     * @param $user_id int le score de l'utilisateur attaquant
     * @return array liste des utilisateurs à portée
     */
    public static function get_available_target($score, $user_id) {
        $min = intval(($score - $score * .1) - 1000);
        $max = intval(($score + $score * .4) + 5000);

        $sql = "SELECT id,pseudo,score FROM users WHERE id != ? AND score BETWEEN $min AND $max ORDER BY score DESC";

        if (_DEBUG_) {
            $sql = "SELECT id,pseudo,score FROM users WHERE id != ?";
        }

        $db = new Db();
        return $db->query($sql, [$user_id]);
    }

    public static function is_available_target($attaquer_score, $defender_score) {
        $min = intval(($attaquer_score - $attaquer_score * .1) - 1000);
        $max = intval(($attaquer_score + $attaquer_score * .4) + 5000);

        return $defender_score > $min && $defender_score < $max || _DEBUG_;
    }

}
