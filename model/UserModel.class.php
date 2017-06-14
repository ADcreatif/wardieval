<?php

class UserModel {

    public $ressources;
    public $score;
    public $id;
    public $pseudo;
    public $email;

    public $last_refresh;
    protected $table = 'users';

    /*
        protected static $definition = [
            ['name' => 'pseudo', 'type' => self::TYPE_STRING],
            ['name' => 'pass', 'type' => self::TYPE_STRING],
            ['name' => 'email', 'type' => self::TYPE_STRING],
            ['name' => 'gender', 'type' => self::TYPE_INT],
            ['name' => 'last_refresh', 'type' => self::TYPE_DATE],
            ['name' => 'ressources', 'type' => self::TYPE_INT],
            ['name' => 'score', 'type' => self::TYPE_INT],
        ];
    */
    function __construct($id = null) {
        $this->id = intval($id);
        $this->get_infos();
    }

    private function get_infos() {
        $sql = "
            SELECT id,pseudo,email,last_refresh,ressources,score
            FROM users 
            WHERE id = ?
        ";

        $db = new Db();
        $user = $db->queryOne($sql, [$this->id]);

        foreach ($user as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function get_ressources() {
        return intval($this->ressources);
    }

    public function update_ressources() {
        $modifiers = CardsModel::getModifiers($this->id);
        $this->increase_ressource(round(get_time_diff($this->last_refresh) * $modifiers['income']));
    }

    private function hashPassword($password) {
        $salt = '!v&242$' . substr(bin2hex(openssl_random_pseudo_bytes(32)), 0, 22);

        // Voir la documentation de crypt() : http://devdocs.io/php/function.crypt
        return crypt($password, $salt);
    }

    private function verifyPassword($clearPassword, $hashedPassword) {
        return crypt($clearPassword, $hashedPassword) == $hashedPassword;
    }

    public function login($pseudo, $pass) {
        $db = new Db();
        $user = $db->queryOne('SELECT id, pseudo, email, pass FROM users WHERE pseudo = ?', [$pseudo]);
        if (empty($user) && !$this->verifyPassword($pass, $user['pass'])) {
            throw new DomainException('Mauvais pseudo ou mot de passe');
        }
        return $user;
    }


    /** ajout d'un utilisateur à la base et auto login
     * @param $pseudo String pseudo unique
     * @param $pass String pass non encodé
     * @param $email String
     * @param $gender int 0:male, 1:female, 2:unkown
     * @return int  returns the new user ID
     */
    public function add_user($pseudo, $pass, $email, $gender) {
        if (self::exists_in_database('pseudo', $pseudo, 'users'))
            throw new DomainException("un utilisateur porte déjà ce nom");

        $db = new Db();

        $pass = $this->hashPassword($pass);

        $user_id = $db->exec('INSERT INTO users (pseudo, pass, email, gender, last_refresh) VALUES (?, ?, ?, ?, NOW())', [$pseudo, $pass, $email, $gender]);

        // on crée la ligne des modifiers
        $db->exec("INSERT INTO modifiers SET user_id = $user_id");

        return $user_id;
    }

    public static function get_id_from_pseudo($pseudo) {
        $db = new Db();
        $res = $db->queryOne('SELECT id FROM users WHERE pseudo = ?', [$pseudo]);
        return $res['id'];
    }

    /**
     * décrémente les ressources de l'utilisateur
     * @param $amount int quantité a ajouter (peut être négatif)
     * @returns int retourne les ressources restantes
     */
    public function increase_ressource($amount) {
        $db = new Db();
        $this->ressources += intval($amount);
        $db->exec('UPDATE users SET ressources = ? WHERE id= ?', [$this->ressources, $this->id]);
    }

    /** modifie le score de l'utilisateur
     * @param $amount int (peut être négatif)
     * @returns int le nouveau score
     */
    public function increase_score($amount) {
        $db = new Db();
        $this->score += intval($amount);
        $db->exec('UPDATE users SET score = ? WHERE id= ?', [$this->score, $this->id]);
        return $this->score;
    }

    public static function get_user_list($startWidth = '', $limit = 50) {
        $db = new Db();

        // la requete ne marche pas quand on prépare avec LIMIT
        $limit = intval($limit);

        $res = $db->query("SELECT pseudo FROM users WHERE pseudo LIKE :term LIMIT $limit", [':term' => '%' . $startWidth . '%']);
        return $res;
    }

    public function has_refresh() {
        $db = new Db();
        $db->exec('UPDATE users SET last_refresh = NOW() WHERE id= ?', [$this->id]);
        return $this->score;
    }
}