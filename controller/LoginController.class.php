<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 20/04/2016
 * Time: 00:25
 */
class LoginController extends FrontController {

    protected function display() {
        $userSession = new UserSession();

        // exceptionellement on interdit  cette page si l'utilisateur est déjà logué
        if ($userSession->isLogged())
            redirect();
    }

    protected function httpPost() {
        if (array_key_exists('submit', $_POST) && $_POST['submit'] == 'login') {
            try {
                if (empty($_POST['pseudo']))
                    throw new DomainException('Veuillez entrer un login');

                if (empty($_POST['pass']))
                    throw new DomainException('Veuillez entrer un password');

                $user = new UserModel();
                $userInfos = $user->login($_POST['pseudo'], $_POST['pass']);
            } catch (Exception $e) {
                $userSession = new UserSession();
                $userSession->addFlashBag($e->getMessage());
                redirect('login');
            }

            $session = new UserSession();
            $session->create($userInfos['id'], $userInfos['pseudo']);
            var_dump($userInfos['email']);

            // pour la migration des anciens comptes on demande d'ajouter l'email
            if ($userInfos['email'] == '') {
                $session->addFlashBag(
                    '<p>Hey ! content de te revoir ' . $userInfos['pseudo'] . ' !</p>' .
                    '<p>Pour le coup on a un petit service à te demander, peux-tu <strong>mettre à jour les infos de ton profil</strong> en ajoutant ton email</p>' .
                    '<p>Ne flippes pas! c\'est juste pour te prévenir quand ton compte se fait attaquer par exemple, ou encore si tu oublies ton mot de passe</p>'
                    , true);
                redirect('profile');
            }

            $session->addFlashBag('Hey ! content de te revoir ' . $userInfos['pseudo'] . ' !', true);
            redirect('empire');
        }
    }
}