<?php

class UserSession {

    function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function create($user_id, $pseudo) {

        $_SESSION['user'] = [
            'pseudo' => htmlentities($pseudo),
            'id' => $user_id,
        ];

    }

    public static function destroy() {
        $_SESSION = [];
        session_destroy();

        header('Location: ' . _HOME_);
        exit();
    }

    public function isLogged() {
        if (array_key_exists('user', $_SESSION) == true) {
            if (empty($_SESSION['user']) == false) {
                return true;
            }
        }

        return false;
    }

    public function addFlashBag($message, $status = false) {
        if (!array_key_exists('flashBag', $_SESSION))
            $_SESSION['flashBag'] = [];
        array_push($_SESSION['flashBag'], ['message' => $message, 'status' => $status]);
    }

    public function fetchFlashBag() {
        $messages = $_SESSION['flashBag'];
        $_SESSION['flashBag'] = [];
        return $messages;

    }

    public function haveFlashBag() {
        return array_key_exists('flashBag', $_SESSION) && count($_SESSION['flashBag']);
    }
}