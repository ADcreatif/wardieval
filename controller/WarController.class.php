<?php

class WarController extends FrontController {

    function display() {
        $userSession = new UserSession();
        if (!$userSession->isLogged())
            redirect();

        $user = new UserModel($_SESSION['user']['id']);

        if (isset($_GET['action']) && $_GET['action'] = 'attack') {

            $target = new UserModel(intval($_GET['param']));

            if (!CombatModel::is_available_target($user->score, $target->score))
                throw new DomainException(clean_html($target->pseudo) . " n'est pas a votre portée");

            $army = new ArmyModel($user->id);
            $troops = $army->get_troops();
            return [
                'fleet_choice' => '',
                'troops' => $troops,
                'target' => $target
            ];
        }

        return [
            'target_selection' => '',
            'targets' => CombatModel::get_available_target($user->score, $user->id)
        ];
    }

    /**
     * @return array
     */
    protected function httpPost() {
        if (isset($_POST['start_war'])) {

            $targetId = !is_nan($_GET['param']) ? $_GET['param'] : $_POST['target_id'];

            $user = new UserModel($_SESSION['user']['id']);
            $target = new UserModel(intval($targetId));
            $combat = new CombatModel();
            $army = new ArmyModel($user->id);

            if (!CombatModel::is_available_target($user->score, $target->score))
                throw new DomainException("$target->pseudo n'est pas a votre portée");

            // interdire l'auto-attaque
            if ($user->id == $target->id && !_DEBUG_)
                throw new DomainException("C'est une mutinerie ? Non sérieusement vous ne pouvez pas vous attaquer.");

            // Création du combat en Bdd
            $arrival_time = date("Y-m-d H:i:s", time('now') + abs($user->score - $target->score));
            $combat_id = $combat->add($user->id, $target->id, $arrival_time);
            $new_army = new ArmyModel($user->id, $combat_id);

            // Transfert des units en Bdd
            $troops = $army->get_troops($user->id);
            foreach ($troops as $unitId => $troop) {
                var_dump($_POST);
                //exit;
                if (array_key_exists('unitID_' . $unitId, $_POST)) {
                    $unit_owned = $troop['quantity'];
                    $unit_sent = $_POST['unitID_' . $unitId];
                    if ($unit_owned > 0 && $unit_sent > 0) {
                        $army->transfert_units(
                            $unitId,
                            max([$unit_owned, $unit_sent]),
                            $troop['troop_id'],
                            $combat_id
                        );
                    }
                }
            }

            // redirection pour éviter le renvoi d'une nouvelle flotte en actualisant la page
            redirect('war');
        }
        return [
            'fleet_choice' => '',
            'user' => '',
            'target' => '',
        ];
    }
}