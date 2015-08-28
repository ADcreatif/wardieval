<?php

class User extends ObjectModel {

    public $ressources;
    public $score;
    public $id;
    public $pseudo;

    protected $last_refresh;

    protected $table = 'users';
    protected static $definition = [
        ['name' => 'pseudo', 'type' => self::TYPE_STRING],
        ['name' => 'pass', 'type' => self::TYPE_STRING],
        ['name' => 'last_refresh', 'type' => self::TYPE_STRING],
        ['name' => 'ressources', 'type' => self::TYPE_INT],
        ['name' => 'score', 'type' => self::TYPE_INT],
    ];

    function User($id = null) {
        parent::__construct($id);
        $this->update_ressources($this->last_refresh);

    }

    /*
        protected function getInfos($id) {
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
                return true;
            }
            return false;
        }
    */
    private function update_ressources($last_refresh) {
        $empire = new Empire($this);
        $modifiers = $empire->get_modifiers();
        $this->increase_ressource(get_time_diff($last_refresh) * $modifiers['income_rate'], true);

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
        if (! self::exists_in_database('pseudo', $pseudo, 'users')) {
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
        $sql = "SELECT id, pseudo, score FROM users ORDER BY $order_by LIMIT :limit ";
        $res = Db::prepare($sql);
        $res->bindParam(':limit', $limit, PDO::PARAM_INT);
        $res->execute();

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_id_from_pseudo($pseudo) {
        $sql = "SELECT id FROM users WHERE pseudo = :pseudo";
        $req = Db::prepare($sql);
        $req->bindParam(':pseudo', $pseudo, PDO::PARAM_STR);
        $req->execute();
        return $req->fetchColumn();
    }

    /**
     * décrémente les ressources de l'utilisateur
     *
     * @param $amount int quantité a ajouter (peut être négatif)
     * @param $update_refresh bool actualise également last_refresh
     *
*@returns int retourne les ressources restantes
     */
    public function increase_ressource($amount, $update_refresh) {
        $refresh = $update_refresh ? ', last_refresh = NOW()' : '';
        $sql = "UPDATE users SET ressources = (ressources + $amount ) $refresh WHERE id = {$this->id}";
        Db::exec($sql);
        return $this->ressources += $amount;
    }

    /** modifie le score de l'utilisateur
     * @param $amount int (peut être négatif)
     * @returns int le nouveau score
     */
    public function increase_score($amount){
        $sql = "UPDATE users SET score = (score + $amount) WHERE id = {$this->id}";
        Db::exec($sql);
        return $this->score += $amount;
    }
}