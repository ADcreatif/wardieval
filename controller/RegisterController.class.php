<?php

class RegisterController extends FrontController {

    function httpPost() {
        if (empty($_POST['pseudo']) OR
            empty($_POST['pass']) OR
            empty($_POST['email']) OR
            empty($_POST['gender'])
        ) {
            throw new DomainException('Merci de remplir tous les champs');
        }

        if (strlen($_POST['pseudo']) > 3 && preg_match('[^a-zA-Z0-9_\-]', $_POST['pseudo']))
            throw new DomainException('Longueur minimum 4 caractères. Seul les caractères alpha-numérique et "-" "_" sont acceptés');

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            throw new DomainException("Le format du mail est invalide, essayez une autre adresse mail");

        // création de l'utilisateur
        $user = new UserModel();
        $user_id = $user->add_user(
            $_POST['pseudo'],
            $_POST['pass'],
            $_POST['email'],
            $_POST['gender']
        );

        // création de la session utilisateur
        $userSession = new UserSession();
        $userSession->create($user_id, $_POST['pseudo']);
        $userSession->addFlashBag('Votre compte à bien été crée, bienvenue');

        // redirection vers la page d'accueil
        header('Location: ' . _ROOT_ . '/empire');
        exit();
    }

}