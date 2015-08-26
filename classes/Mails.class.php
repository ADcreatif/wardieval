<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 26/08/15
 * Time: 14:42
 */

class Mail extends ObjectModel {

    function __construct($id){
        $this->table = 'mails';
        $this->definition = [
            ['name'=>'author','type' => PDO::PARAM_INT],
            ['name'=>'recipient','type' => PDO::PARAM_INT],
            ['name'=>'message','type' => PDO::PARAM_STR],
            ['name'=>'topic','type' => PDO::PARAM_STR],
            ['name'=>'send_date','type' => PDO::PARAM_STR],
            ['name'=>'unread','type' => PDO::PARAM_BOOL],
        ];
        parent::__construct($id);

    }

    /**
     * fonction d'envoi de messages
     *
     * @param $to int recipient user id
     * @param $message string message (can be html)
     * @param $topic string message topic trucated after 50 caracters
     * @param $from int (optional) sender user id if not defines, it'll be from the admin
     */
    public static function send_mail($to, $message, $topic = '', $from = 0) {
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

    public function mark_as_read(){
        $sql = "UPDATE $this->table SET unread=0 WHERE id=$this->id";
        Db::exec($sql);
    }
} 
