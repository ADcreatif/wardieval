<?php


class MailController extends FrontController {

    protected function display() {

        $userSession = new UserSession();
        if (!$userSession->isLogged())
            redirect();

        $user = new UserModel($_SESSION['user']['id']);

        return [
            'mails' => MailModel::get_mails($user->id)
        ];
    }

    public function ajax() {
        switch ($_POST['action']) {
            case 'mark_as_read' :
                $mail = new MailModel(intval($_POST['mail_id']));
                echo $mail->markAsRead();
                break;

            case 'toggleFavourite':
                $mail = new MailModel(intval($_POST['mail_id']));
                echo $mail->toggleFavourite();
                break;

            case 'send_mail' :
                $mail = new MailModel();
                echo $mail->send_mail($_POST['recipient'], $_POST['message'], $_POST['subject'], intval($_SESSION['user']['id']), $_POST['mail_id']);
                break;

            case 'delete_mail' :
                $mail = new MailModel(intval($_POST['mail_id']));
                echo $mail->delete();
                break;

            case 'get_contact_list':
                echo json_encode(UserModel::get_user_list($_POST['startWith'], 15));
                break;

        }
    }
}
