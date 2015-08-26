<?php
session_start();

$errors = [];
include 'config/config.inc.php';
include 'classes/Tools.php';
include 'classes/ObjectModel.php';
auto_include('classes/');

$page = 'home';
if (!empty($_GET['page'])) {
    $page = $_GET['page'];
}

// Chargement du controller
$controller = 'controllers/' . strtolower($page) . '.controller.php';
$template = 'views/' . strtolower($page) . '.view.php';

if (!file_exists($controller) || !file_exists($template)) {
    header("HTTP/1.1 404 Not Found");
    include 'views/404.php';
    die;
}

// en premier on construit les éventuels éléments terminés POUR TOUT LE MONDE
// afin que les scores soient à jour et les unités prettes en cas d'attaques
Empire::building_time_is_over();

// puis on résouds les éventuels combats
Combat::solve_combats();

// controller du header
if (User::isLoggued()) {
    $user = new User($_SESSION['user']['id']);
}

include $controller;
if(!isset($_POST['ajax']))
    include 'views/layout.php';