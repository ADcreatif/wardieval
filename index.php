<?php
session_start();

include 'config/config.inc.php';

if (_DEBUG_) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
} else {
    ini_set("display_errors", 0);
    error_reporting(0);
}

$errors = [];

include 'classes/Tools.php';
include 'models/ObjectModel.php';
auto_include('classes/');
auto_include('models/');

$page = 'home';
if (!empty($_GET['page'])) {
    $page = $_GET['page'];
}

if (!_MAINTENANCE_) {
    // Chargement du controller
    $controller = 'controllers/' . strtolower($page) . '.controller.php';
    $template = 'views/' . strtolower($page) . '.view.phtml';

    if (!file_exists($controller) || !file_exists($template)) {
        header("HTTP/1.1 404 Not Found");
        include 'views/404.phtml';
        die;
    }
} else {
    $template = 'views/maintenance.view.phtml';
}
// Mise à jour des données
if (User::isLogged()) {
    $user = new User($_SESSION['user']['id']);
    $queue = new Queue($user->id);
    $army = new Army($user->id);

    // mise à jour des ressources
    $user->update_ressources();

    // mise à jour des construction
    $queue->update_queue(get_time_diff($user->last_refresh));

    // résolution des combats
    foreach (Combat::get_arrived_troops() as $combat) {
        $combat = new Combat($combat['id']);
        $combat->solve_combats();
    }

    // mise à jour de l'armée (après les constructions et combats)
    $troops = $army->get_troops();

    // mise à jour de l'heure
    $user->update_value('last_refresh', date("Y-m-d H:i:s"));
}

if (!_MAINTENANCE_) include $controller;
if (!isset($_POST['ajax']))
    include 'views/layout.phtml';