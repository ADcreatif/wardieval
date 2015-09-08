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