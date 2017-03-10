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
            $session->addFlashBag('Hey ! content de te revoir ' . $userInfos['pseudo'] . ' !', true);
            redirect('empire');
        }
    }
}