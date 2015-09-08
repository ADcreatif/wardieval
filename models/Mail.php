<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 26/08/15
 * Time: 14:42
 */
class Mail extends ObjectModel {
    public $author;
    public $recipient;
    public $content;
    public $topic;
    public $send_date;
    public $unread = 1;

    protected $table = 'mails';
    protected static $definition = [
        ['name' => 'author', 'type' => self::TYPE_INT],
        ['name' => 'recipient', 'type' => self::TYPE_INT],
        ['name' => 'content', 'type' => self::TYPE_HTML, 'length' => 1600],
        ['name' => 'topic', 'type' => self::TYPE_STRING, 'length' => 35],
        ['name' => 'send_date', 'type' => self::TYPE_STRING, 'nullValues' => true],
        ['name' => 'unread', 'type' => self::TYPE_BOOL, 'nullValues' => true],
    ];

    /**
     * fonction d'envoi de messages
     * @param $to int id du destinataire
     * @param $content string contenu du message (possible html)
     * @param $topic string sujet du message (rogné à 50 caractères)
     * @param $from int (optional) id de l'auteur, "admin" si non défini
     * @return string statut du message
     */
    public function send_mail($to, $content, $topic = '', $from = 0) {

        if (!is_numeric($to))
            $to = User::get_id_from_pseudo($_POST['to']);

        if (!self::exists_in_database('id', $to, 'users'))
            return false;

        $this->hydrate([
            'author'    => $from,
            'recipient' => $to,
            'content'   => $content,
            'topic'     => $topic,
        ]);

        $this->save();

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
                $mails[$mail['id']]['content'] = nl2br(clean_html($mail['content']));
                $mails[$mail['id']]['topic'] = htmlentities($mail['topic']);
                if ($mail['author'] == 0) {
                    $mails[$mail['id']]['author'] = 'admin';
                } else {
                    $sender = new User($mail['author']);
                    $mails[$mail['id']]['author'] = htmlentities($sender->pseudo);
                }
            }
        }
        return $mails;
    }

} 
