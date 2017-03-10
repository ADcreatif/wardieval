<?php

class LogoutController extends FrontController {

    function display() {
        $session = new UserSession();
        $session->destroy();
    }
}