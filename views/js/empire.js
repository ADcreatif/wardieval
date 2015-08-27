$(function () {

    /**
     * calcul du prix des unités
     * @param unit_id
     * @param quantity
     * @returns {number}
     */
    var get_price = function (unit_id, quantity) {
        // les modifiers et units infos sont définis par dans Empire.view
        return quantity * unit_infos[unit_id].price * modifiers.build_price;
    };

    // affichage des prix à la modification des champs
    $('input[type="number"]').on('blur change', function () {
        // récupération de l'id de l'unité avec l'attribut data
        var unit_id = $(this).data('unitId');

        // récupération de la valeur du formulaire
        var quantity = parseInt($(this).val());

        // récupération de la zone d'affichage
        var span_info = $('#unit_' + unit_id + ' .js-span-info');

        // calcul du prix (unit_list est défini dans la vue par php)
        var price = get_price(unit_id, quantity);

        // on masque la zone s'il n'y a pas de calcul à faire;
        if (price < 1) {
            span_info.hide();
        } else {
            // on boucle sur le tableau des unités qui est crée dans empire.view.php
            span_info.show();
            span_info.text(price + ' $');
        }
    });

    /****************************************
     *              COMPTEURS
     ****************************************/

    // Ajouté manuellement en retour ajax
    var set_countdown = function (item) {
        var $this = $(item);
        var finalDate = $(item).data('countdown');
        $this.countdown(finalDate,function (event) {
            var format = '%-Ssec';
            if (event.offset.minutes > 0)
                format = '%-Mmin ' + format;
            if (event.offset.hours > 0)
                format = '%-Hh ' + format;
            if (event.offset.days > 0)
                format = '%-D jrs ' + format;
            $this.html(event.strftime(format));
        }).on('finish.countdown', function () {
            $(this).html('terminé');
        });
    };

    // ajoute un compteur automatiquement au chargement de la page
    $('[data-countdown]').each(function (index, span) {
        set_countdown(span);
    });

    /****************************************
     *           COMMANDES AJAX
     ****************************************/


    /**
     *
     * @param action string nom de l'action à réaliser
     * @param item_id int  ID de l'item sur lequel on applique l'action
     * @param item_to_delete can be an array of jquery objects
     */
    var ajax = function (action, item_id, item_to_delete, callback) {
        $.post('', {ajax: true, action: action, item_id: item_id}, function (data) {
            if (item_to_delete) {
                if (item_to_delete.isArray) item_to_delete.each(function (key, item) {
                    item.remove()
                });
                else item_to_delete.remove();
            }
            if (callback)
                callback(JSON.parse(data));
        });
    };
    var ajax_simple_request = function (action, item_id, item_to_delete, confirmation, callback) {
        if (confirmation) {
            $.prompt('êtes vous sur ?', { buttons: { "Oui": true, "annuler": false }, submit: function (e, v) {
                if (v) ajax(action, item_id, item_to_delete, callback);
            }});
        } else ajax(action, item_id, item_to_delete, callback);
    };


    // création des unités en ajax
    $('form.unit-factory').on('submit', function (e) {
        // désactivation de l'envoi du formulaire
        e.preventDefault();

        var qty_input = $(this.quantity);
        var unit_id = qty_input.data('unitId');
        var quantity = qty_input.val();
        var span_info = $('#unit_' + unit_id + ' .js-span-info');
        var price = get_price(unit_id, quantity);

        if (price > 0) {
            $.ajax({
                type: 'POST',
                data: {ajax: true, action: 'build', unit_id: unit_id, quantity: quantity},
                dataType: 'json',
                success: function (data) {
                    if (data.status == 'error') {
                        span_info.show();
                        span_info.text(data.message);
                    } else {
                        //remise à zero du formulaire
                        qty_input.val(0);
                        span_info.hide();

                        //mise à jour de la queue dans le DOM
                        var q = data.queue;
                        var queue = $('#js-queue');
                        queue.find('.alert.alert-info').hide();
                        queue.append('<li id="queue_' + q.queue_id + '">' + q.name + ' - ' + q.quantity + ' (<span data-countdown="' + q.arrival_time + '">' + q.time_left + '</span>) <a class="alert alert-error" href="#" data-queue-id="' + q.queue_id + '">X</a></li>');

                        // lancement du compteur
                        var item = $('#queue_' + q.queue_id).find('span');
                        set_countdown(item);

                        // on met à jour la variable globale pour l'affichage des ressouces dans l'en-tête
                        ressources = data.new_ressources;
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    span_info.show();
                    span_info.text('erreur lors de la création des unités');
                    console.log(xhr.status);
                    console.log(thrownError);
                }
            });
        }
    });

    // annuler une construction en cours
    $('#js-queue').on('click tap', 'a', function (e) {
        e.preventDefault();
        ajax_simple_request('remove_queue', $(this).data('queueId'), $(this).closest('li'), true, update_ressources);
    });

    // callback après avoir annulé une construction
    var update_ressources = function (data) {
        ressources = parseInt(data.new_ressources);
    };


    // annuler une attaque en cours
    $('#js-fleet').on('click tap', 'a.alert-error', function (e) {
        e.preventDefault();
        ajax_simple_request('remove_fleet', $(this).data('fleetId'), $(this).closest('li'), true)
    });

    // déplie un message et le marque comme lu
    $('tr.topic').each(function () {
        $(this).on('click tap',function () {
            $(this).removeClass('unread').next('tr').toggle('slow');
            ajax_simple_request('mark_as_read', $(this).data('mailId'))
        }).find('a').click(function (e) {
            e.preventDefault();
            // le mode multiple bug, pour l'instant on efface qu'une ligne;
            $(this).next('tr.message').hide('slow').remove();
            ajax_simple_request('delete_mail', $(this).data('mailId'), $(this).closest('tr.topic'), true)
        });
    });
});