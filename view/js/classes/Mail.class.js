/************************************
 *             MESSAGES             *
 ************************************/

class Mails {

    constructor() {
        this.$mailForm = $('#js-mail-form');

        $('article.unread').on('click tap', this.onClickMarkAsRead);            // marquer comme lu
        $('.cat a').on('click tap', this.onClickCallToAction.bind(this));       // ajouter en favori, effacer, répondre
        $('#js-new-mail').on('click tap', this.onClickShowMailForm.bind(this)); // afficher le formulaire de mail
        $('.cancel').click(function () {
            this.$mailForm.fadeOut()
        }.bind(this));      // fermeture du formulaire
        this.$mailForm.submit(this.onSubmitSendMail.bind(this));                // envoi d'un mail

        // active l'accordéon pour l'affichage
        $('.accodion').accordion({
            header: 'header',
            collapsible: true,
            active: false,
        });


        // affiche le temps passé depuis l'envoi du mail
        $('.send_date').each(function (index, item) {
            $(item).text(countdown(
                new Date($(item).text()),
                null,
                countdown.YEARS | countdown.MONTHS | countdown.DAYS | countdown.HOURS | countdown.MINUTES | countdown.SECONDS,
                2).toString())
        });
    }

    // envoi du mail en ajax
    onSubmitSendMail(event) {
        event.preventDefault();

        let recipient = this.$mailForm.find('#recipient').val();
        let subject = this.$mailForm.find('#subject').val();
        let message = this.$mailForm.find('#message').val();
        let mail_id = this.$mailForm.find('#mail_id').val();
        if (recipient == "" || subject == "" || message == "") {
            $.alert('Merci de remplir tous les champs');
            return false;
        }
        $.post('', {
            ajax: true,
            action: 'send_mail',
            recipient: recipient,
            subject: subject,
            message: message,
            mail_id: mail_id
        }, function (data) {
            if (data == true) {
                $.alert('Votre message à bien été envoyé');
                this.$mailForm.fadeOut();
            } else {
                $.alert('Apparement votre message n\'est pas parti');
            }
        });
    }

    onClickShowMailForm(recipient, mail_id) {
        let $recipient = $('#recipient');
        let liste;

        this.$mailForm.trigger('reset');

        // si le destinataire à été précisé, on prérempli le champ et on le désactive
        if (typeof recipient == "string") {
            $recipient.val(recipient).prop('disabled', true);
            $('#mail_id').val(mail_id);
        } else {
            $recipient.prop('disabled', false).autocomplete({
                source: function (requete, reponse) {
                    $.post('', {ajax: true, action: "get_contact_list", startWith: $recipient.val()},
                        data => {
                            reponse($.map(data, objet => objet.pseudo));
                        }
                        , 'json');
                },
                minLength: 3
            });
        }

        this.$mailForm.fadeIn()
    }

    deleteMail(mail_id) {
        $.post('', {ajax: true, action: 'delete_mail', mail_id: mail_id},
            data => {
                if (data == true)
                    $('[data-mail-id=' + mail_id + ']').remove();
            }
            , 'json');
    }

    onClickMarkAsRead(event) {
        let mail_id = parseInt($(event.currentTarget).data('mailId'));
        $.post('', {ajax: true, action: 'mark_as_read', mail_id: mail_id},
            data => {
                if (data == true) {
                    $(event.currentTarget).off();
                    $('[data-mail-id=' + mail_id + ']').removeClass('unread');
                }
            }
        );
    }

    toggleFavourite(mail_id, target) {
        $.post('', {ajax: true, action: 'toggleFavourite', mail_id: mail_id},
            data => {
                if (data == true)
                    target.toggleClass('favourite');
            }
        );
    }

    onClickCallToAction(event) {
        let target = $(event.currentTarget);
        let mail_id = parseInt(target.closest('article').data('mailId'));

        event.preventDefault();
        switch (target.data('action')) {
            case 'toggleFavourite':
                this.toggleFavourite(mail_id, target);
                break;
            case 'deleteMail':
                this.deleteMail(mail_id);
                break;
            case 'answerMail':
                this.onClickShowMailForm(target.data('recipient'), mail_id);
                break;
        }
    }

}