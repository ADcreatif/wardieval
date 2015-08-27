<?php

class User {

    public $ressources;
    public $score;
    public $id;
    public $pseudo;

    function User($id = null) {
        if ($id != null) {
            $this->getInfos($id);
        }
    }

    private function getInfos($id) {
        global $errors;
        $sql = 'SELECT id, pseudo, ressources, last_refresh, score  FROM users WHERE id = :id' ;
        $req = Db::prepare($sql);
        $req->bindValue(':id', intval($id), PDO::PARAM_INT);
        $req->execute();
        if($req->rowCount()>0){
            $res = $req->fetch();

            $this->id = intval($res['id']);
            $this->pseudo = ucfirst(htmlentities($res['pseudo']));
            $this->ressources = intval($res['ressources']);
            $this->score = intval($res['score']);
            $this->update_ressources($res['last_refresh']);
        } else $errors[] =  "ceci n'est pas un id utilisateur valide ($id)";
    }

    private function update_ressources($last_refresh) {
        $factor = 1;
        $this->ressources += get_time_diff($last_refresh, 'now') * $factor * 1;

        // insert les nouvelles valeurs dans la base
        $sql = "UPDATE users SET last_refresh = NOW(), ressources = $this->ressources WHERE id = $this->id";
        $req = Db::prepare($sql);
        $req->execute();
        $req = Db::prepare($sql);
        $req->execute();
    }

    public static function login($pseudo, $pass) {
        $sql = "SELECT id, score, ressources FROM users WHERE pseudo = :pseudo AND pass = :pass";
        $req = Db::prepare($sql);
        $req->bindValue(':pseudo', trim($pseudo), PDO::PARAM_STR);
        $req->bindValue(':pass', sha1(trim($pass)), PDO::PARAM_STR);
        $req->execute();

        if ($req->rowCount()) {
            $res = $req->fetch();
            $_SESSION['user'] = [
                'pseudo' => htmlentities($pseudo),
                'id'     => $res['id'],
            ];
            $url = 'Location:' . _ROOT_ . 'empire';
            header("$url");
            die();
        }
        return 'Mauvais identifiants';
    }

    public static function logout() {
        session_unset();
        session_destroy();
        $url = 'Location:' . _ROOT_ . 'home';
        header("$url");
        die();
    }

    public static function isLoggued() {
        if (! empty($_SESSION) && ! empty($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $sql = "SELECT id FROM users WHERE id = :id ";
            $req = Db::prepare($sql);

            $req->bindValue(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
            $req->execute();
            if ($req->rowCount())
                return true;
        }
        return false;
    }

    /** ajout d'un utilisateur à la base et auto login
     * @param $pseudo String pseudo unique
     * @param $pass String pass non encodé
     *
     * @return string
     */
    public static function add_user($pseudo, $pass) {
        $sql = "SELECT * FROM users WHERE pseudo = :pseudo LIMIT 1";
        $req = Db::prepare($sql);
        $req->bindValue(':pseudo', trim($_POST['pseudo']), PDO::PARAM_STR);
        $req->execute();
        if ($req->rowCount() == 0) {
            $sql = "INSERT INTO users (pseudo, pass, last_refresh, ressources) VALUES (:pseudo, :pass, NOW(), 20000)";
            $req = Db::prepare($sql);
            $req->bindValue(':pseudo', trim($pseudo), PDO::PARAM_STR);
            $req->bindValue(':pass', sha1(trim($pass)), PDO::PARAM_STR);
            $req->execute();

            $_SESSION['user'] = [
                'pseudo' => htmlentities($pseudo),
                'id'     => Db::getLastInsertId(),
            ];

            $url = 'Location:' . _ROOT_ . 'empire';
            header("$url");
            die();
        }
        return "un utilisateur porte déjà ce nom";
    }

    /** Récupère la liste de tous les utilisateurs */
    public static function get_users_list($limit = 200, $order_by = 'pseudo', $asc = 'ASC') {
        $order_by = @mysql_real_escape_string($order_by) . ' ' . @mysql_real_escape_string($asc);
        $sql = "SELECT id,pseudo,score FROM users ORDER BY $order_by LIMIT :limit ";
        $res = Db::prepare($sql);
        $res->bindParam(':limit', $limit, PDO::PARAM_INT);
        $res->execute();

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * décrémente les ressources de l'utilisateur
     * @param $amount int (peut être négatif)
     * @returns int retourne les ressources restantes
     */
    public function update_ressource($amount) {
        $sql = "UPDATE users SET ressources = (ressources + $amount ) WHERE id = {$this->id}";
        Db::exec($sql);
        return $this->ressources += $amount;
    }

    /** modifie le scrore de l'utilisateur
     * @param $amount int (peut être négatif)
     * @returns int le nouveau score
     */
    public function increase_score($amount){
        $sql = "UPDATE users SET score = (score + $amount) WHERE id = {$this->id}";
        Db::exec($sql);
        return $this->score += $amount;
    }
}