<?php

if (!empty($_POST)) {
    $user = new User();
    $errors = [];
    if (empty($_POST['pseudo'])) {
        $errors[] = 'Veuillez entrer un login';
    }
    if (empty($_POST['pass'])) {
        $errors[] = 'Veuillez entrer un password';
    }
    if (empty($errors))
        $errors[] = User::add_user($_POST['pseudo'], $_POST['pass']);
}
/* exemple avec try catch()
try {
    if (!empty($_POST)) {
        $user = new User();
        $errors = [];
        if (empty($_POST['pseudo'])) {
            throw new Exception('Veuillez entrer un login');
        }
        if (empty($_POST['pass'])) {
            throw new Exception('Veuillez entrer un password');
        }
        if (empty($errors))
            $errors[] = User::add_user($_POST['pseudo'], $_POST['pass']);
    }
} catch (Exception $e) {
    $msgErreur = $e->getMessage();
    require 'vueErreur.php';
}
*/