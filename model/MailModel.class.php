<?php

/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 26/08/15
 * Time: 14:42
 */

require _ROOT_ . 'vendor/autoload.php';

class MailModel {

    function __construct($id = null) {
        //parent::__construct($id);

        if ($id != null) {
            $this->id = $id;
        }
    }

    function toggleFavourite() {
        $db = new Db();
        return $db->exec('UPDATE mails SET favourite = (favourite + 1) % 2 WHERE id=?', [$this->id]);
    }

    function markAsRead() {
        $db = new Db();
        return $db->exec('UPDATE mails SET unread = 0 WHERE id=?', [$this->id]);
    }

    function delete() {
        $db = new Db();
        return $db->exec('DELETE FROM mails WHERE id=? AND  favourite=0', [$this->id]);
    }

    /**
     * fonction d'envoi de messages
     * @param $to int id du destinataire
     * @param $content string contenu du message (possible html)
     * @param $topic string sujet du message (rogné à 50 caractères)
     * @param $from int (optional) id de l'auteur, "admin" si non défini
     * @return string statut du message
     */
    public function send_mail($to, $content, $topic = '', $from = 0) {

        // récupération de l'id du destinataire
        if (!is_numeric($to))
            $to = UserModel::get_id_from_pseudo($to);

        $author = new UserModel($from);
        $recipient = new UserModel($to);

        if (!$to || empty($recipient->email))
            throw new ErrorException("Erreur avec le destinataire n'as pas été trouvé ou n'as pas configuré d'email");

        /** AJOUT EN BASE DE DONNÈES**/
        $sql = "INSERT INTO mails (author, recipient, content, topic) VALUES (?,?,?,?)";
        $db = new Db();
        $db->exec($sql, [$from, $to, $content, $topic]);

        $subject = "un nouveau message de wardieval";
        $message_html = '<html><head></head><body><b>Bonjour ' . $recipient->pseudo . ' !</b><br>vous avez reçu un nouveau message de ' . $author->pseudo . ', vous pouvez le consulter sur votre <a href="' . _HOME_ . '/mail" title="messagerie">messagerie</a><br><br>À très très vite sur wardieval.com !</body></html>';
        $message_txt = 'Bonjour' . $recipient->pseudo . ' ! vous avez reçu un nouveau message de ' . $author->pseudo . ', vous pouvez le consulter sur votre messagerie http://' . _HOME_ . '/mail . À très très vite sur wardieval.com !';

        $mail = new PHPMailer();
        $mail->setFrom('hoolay@free.fr', 'Hoolay');
        $mail->addAddress($recipient->email, $recipient->pseudo);     // Add a recipient
        $mail->addReplyTo('hoolay@free.fr', 'Hoolay');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message_html;
        $mail->AltBody = $message_txt;

        if ($mail->send())
            return true;

        if (_DEBUG_ == 1) {
            throw new DomainException('Mailer : ' . $mail->ErrorInfo);
            exit;
        }

        return false;
    }

    public function get_content($template_name, $template_vars) {
        extract($template_vars);
        $template = "view/mails/$template_name.fr.phtml";

        ob_start();
        include_once $template;
        $view = ob_get_contents();
        ob_end_clean();

        return $view;
    }

    public static function get_mails($user_id) {
        $user_id = intval($user_id);

        $db = new Db();
        $sql = "SELECT IFNULL(pseudo,'Inconnu')AS pseudo, mails.id, content, topic, author, unread, send_date, favourite
                FROM mails
                LEFT JOIN users ON users.id = mails.author
                WHERE recipient = ? ORDER BY unread DESC, send_date DESC LIMIT 30";

        return $db->query($sql, [$user_id]);
    }

} 
