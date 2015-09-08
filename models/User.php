<?php

class User extends ObjectModel {

    public $ressources;
    public $score;
    public $id;
    public $pseudo;

    public $last_refresh;

    protected $table = 'users';
    protected static $definition = [
        ['name' => 'pseudo', 'type' => self::TYPE_STRING],
        ['name' => 'pass', 'type' => self::TYPE_STRING],
        ['name' => 'last_refresh', 'type' => self::TYPE_DATE],
        ['name' => 'ressources', 'type' => self::TYPE_INT],
        ['name' => 'score', 'type' => self::TYPE_INT],
    ];

    public function update_ressources() {
        $sql = "SELECT income from modifiers WHERE user_id = $this->id";
        $req = Db::query($sql);
        $income = $req->fetchColumn();
        $this->increase_ressource(round(get_time_diff($this->last_refresh) * $income), true);
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

    public static function isLogged() {
        if (!empty($_SESSION) && !empty($_SESSION['user']) && isset($_SESSION['user']['id'])) {
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
     * @return string
     */
    public static function add_user($pseudo, $pass) {
        if (!self::exists_in_database('pseudo', $pseudo, 'users')) {
            $sql = "INSERT INTO users (pseudo, pass, last_refresh) VALUES (:pseudo, :pass, NOW())";
            $req = Db::prepare($sql);
            $req->bindValue(':pseudo', trim($pseudo), PDO::PARAM_STR);
            $req->bindValue(':pass', sha1(trim($pass)), PDO::PARAM_STR);
            $req->execute();
            $user_id = Db::getLastInsertId();

            // on crée la ligne des modifiers en fonction
            Db::exec("INSERT INTO modifiers SET user_id = $user_id");

            $_SESSION['user'] = [
                'pseudo' => htmlentities($pseudo),
                'id'     => $user_id,
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
     * @param $amount int quantité a ajouter (peut être négatif)
     * @returns int retourne les ressources restantes
     */
    public function increase_ressource($amount) {
        $this->update_value('ressources', $this->ressources + intval($amount));
        return $this->ressources;
    }

    /** modifie le score de l'utilisateur
     * @param $amount int (peut être négatif)
     * @returns int le nouveau score
     */
    public function increase_score($amount) {
        $this->update_value('score', $this->score + intval($amount));
        return $this->score += intval($amount);
    }
}