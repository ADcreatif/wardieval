<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 26/08/15
 * Time: 14:42
 */

class Mail extends ObjectModel {

    protected $table = 'mails';
    protected static $definition = [
        ['name' => 'author', 'type' => self::TYPE_INT],
        ['name' => 'recipient', 'type' => self::TYPE_INT],
        ['name' => 'message', 'type' => self::TYPE_HTML],
        ['name' => 'topic', 'type' => self::TYPE_STRING],
        ['name' => 'send_date', 'type' => self::TYPE_STRING],
        ['name' => 'unread', 'type' => self::TYPE_BOOL],
    ];

    function __construct($id = null) {
        parent::__construct($id);
    }

    /**
     * fonction d'envoi de messages
     *
     * @param $to int id du destinataire
     * @param $message string contenu du message (possible html)
     * @param $topic string sujet du message (rogné à 50 caractères)
     * @param $from int (optional) id de l'auteur, "admin" si non défini
     *
     * @return string statut du message
     */
    public static function send_mail($to, $message, $topic = '', $from = 0) {

        if (! is_numeric($to))
            $to = User::get_id_from_pseudo($_POST['to']);

        if (! self::exists_in_database('id', $to, 'users'))
            return false;

        if (strlen($topic) >= 50)
            $topic = trim(substr($topic, 0, 50) . '...');

        $sql = "INSERT INTO mails (author, recipient, send_date, message, topic) VALUES (:from, :to, now(), :message, :topic)";
        $req = Db::prepare($sql);
        $req->bindParam(':from', $from, PDO::PARAM_INT);
        $req->bindParam(':to', $to, PDO::PARAM_INT);
        $req->bindParam(':message', $message, PDO::PARAM_STR);
        $req->bindParam(':topic', $topic, PDO::PARAM_STR);
        $req->execute();
        $req->closeCursor();

        return true;
    }

    public static function get_mails($user_id) {
        $user_id = intval($user_id);
        $mails = [];
        $sql = "SELECT * FROM mails WHERE recipient = $user_id ORDER BY unread DESC, send_date DESC LIMIT 30";
        $req = Db::query($sql);
        if ($req->rowCount() > 0) {
            $result = $req->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $mail) {
                $mails[$mail['id']] = $mail;
                if ($mail['author'] == 0) {
                    $mails[$mail['id']]['author'] = 'admin';
                } else {
                    $sender = new User($mail['author']);
                    $mails[$mail['id']]['author'] = $sender->pseudo;
                }
            }
        }
        return $mails;
    }

    public function mark_as_read() {
        $sql = "UPDATE $this->table SET unread=0 WHERE id = $this->id";
        Db::exec($sql);
    }
} 
