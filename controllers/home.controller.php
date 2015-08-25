<?php
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login' :
            if (empty($_POST['pseudo'])) $errors[] = 'Veuillez entrer un login';
            if (empty($_POST['pass'])) $errors[] = 'Veuillez entrer un password';
            if (empty($errors)) $errors[] = User::login($_POST['pseudo'], $_POST['pass']);
            break;
        case 'logout':
            User::logout();
            break;
        default:
            echo $_GET['action'];
    }
}