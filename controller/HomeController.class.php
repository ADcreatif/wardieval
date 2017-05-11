<?php

class HomeController extends FrontController {

    protected function display() {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'logout':
                    $session = new UserSession();
                    $session->destroy();
                    break;
                default:
                    echo $_GET['action'];
            }
        }

        $db = new Db();
        $top_ten = $db->query('SELECT pseudo, score FROM users ORDER BY score DESC LIMIT 10 ');
        return ['top_ten' => $top_ten];
    }


}