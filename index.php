<?php
header('content-type: text/html; charset=utf-8');
// set URLS
define('_ROOT_', __DIR__ . '/');
define('_HOME_', str_replace("index.php", "", $_SERVER['SCRIPT_NAME']));
define('_TPL_', _HOME_ . 'view/');

require_once 'config/config.inc.php';
require_once 'classes/Tools.php';
require_once 'classes/Rooting.class.php';

$routing = new Rooting();
$routing->bootstrap();
$userSession = new UserSession();

if (_DEBUG_) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set("log_errors", 1);
    ini_set("error_log", "errors.log");
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", 0);
    error_reporting(0);
}

$errors = [];

// page par défaut
$page = !empty($_GET['page']) ? $_GET['page'] : 'home';

if (!_MAINTENANCE_) {
    // Chargement du controller
    try {
        $className = ucfirst(strtolower($page)) . 'Controller';

        if (!class_exists($className))
            throw new DomainException("la classe <strong>$className</strong> est introuvable");

        // instanciation du controller
        $controller = new $className();

        if (!is_file(_ROOT_ . 'view/' . $page . 'View.phtml'))
            throw new DomainException("la page <strong>$page</strong> n'existe pas");

        // extration des variables de template
        extract($controller->tpl_vars, EXTR_OVERWRITE);

    } catch (Exception $e) {
        array_push($errors, $e->getMessage());
    }
} else {
    $page = 'maintenance';
}

// Mise à jour des données
if ($userSession->isLogged()) {
    $user = new UserModel($_SESSION['user']['id']);
    $queue = new QueueModel($user->id);
    $army = new ArmyModel($user->id);

    // mise à jour des ressources
    $user->update_ressources();

    // mise à jour des construction
    $queue->update_queue(get_time_diff($user->last_refresh));

    // résolution des combats
    foreach (CombatModel::get_arrived_troops() as $combat) {
        $combat = new CombatModel($combat['id']);
        $combat->solve_combats();
    }

    // mise à jour de l'armée (après les constructions et combats)
    //$troops = $army->getTroops(); // TODO uncomment : en fait pas sur qu'on se serve de ça

    // mise à jour de l'heure
    $user->has_refresh();
}

if (!isset($_POST['ajax']))
    include 'view/layout/layout.phtml';
