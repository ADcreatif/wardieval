<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 20/04/2016
 * Time: 00:25
 */
class ProfileController extends FrontController {

    protected function display() {
        $userSession = new UserSession();
        if (!$userSession->isLogged())
            redirect();
    }

    protected function httpPost() {

        if (array_key_exists('submit', $_POST)) {

            $user = new UserModel($_SESSION['user']['id']);
            $userSession = new UserSession();

            try {
                if (empty($_POST['email']))
                    throw new DomainException('Veuillez entrer un email');

                if ($user->email != $_POST['email']) {
                    $user->update_value('email', trim(strtolower($_POST['email'])));
                    $userSession->addFlashBag('Votre email à bien été mis à jour');
                }

            } catch (Exception $e) {
                $userSession->addFlashBag($e->getMessage());
                redirect('profile');
            }
        }
    }
}