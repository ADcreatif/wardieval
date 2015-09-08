$(function(){

    /****************************************
     *              COMPTEURS
     ****************************************/

    // Ajouté manuellement en retour ajax
    var set_countdown = function(item){
        var $this = $(item);
        var finalDate = $(item).data('countdown');
        $this.countdown(finalDate,function(event){
            var format = '%-Ssec';
            if(event.offset.minutes > 0)
                format = '%-Mmin ' + format;
            if(event.offset.hours > 0)
                format = '%-Hh ' + format;
            if(event.offset.days > 0)
                format = '%-D jrs ' + format;
            $this.html(event.strftime(format));
        }).on('finish.countdown', function(){
            $(this).html('<a href="' + _root_ + 'empire"title="recharger la page">terminé</a>');
            location.reload();
        });
    };

    // ajoute un compteur automatiquement au chargement de la page
    $('[data-countdown]').each(function(index, span){
        set_countdown(span);
    });


    /****************************************
     *             SIMPLE AJAX              *
     ****************************************/

    /**
     * @param {string} action Nom de l'action à réaliser
     * @param {int} item_id ID de l'item sur lequel on applique l'action
     * @param {Object} [item_to_delete] Objet à effacer du DOM si requête à fonctionné, peut être un objet jquery ou un tableau d'objet
     * @param {function} [callback] Fonction a appeler si la requête à fonctionné
     */
    var simple_ajax = function(action, item_id, item_to_delete, callback){
        $.post('', {ajax : true, action : action, item_id : item_id}, function(data){
            if(item_to_delete){
                if(item_to_delete.length > 1) item_to_delete.forEach(function(item){
                    $(item).remove();
                }); else item_to_delete.remove();
            }

            if(callback && data)
                callback(JSON.parse(data));
        });
    };

    // idem que simple_ajax, mais avec un popup de confirmation
    var simple_ajax_confirm = function(action, item_id, item_to_delete, callback, message){
        message = message || 'êtes vous sur ?';
        $.prompt(message, { buttons : { "Oui" : true, "annuler" : false }, submit : function(e, v){
            if(v) simple_ajax(action, item_id, item_to_delete, callback);
        }});
    };


    /************************************
     *          CONSTRUCTIONS           *
     ************************************/

    // calcul du prix des unités
    var get_price = function(unit_id, quantity){
        // troops est défini dans Empire.view
        return Math.round(quantity * troops[unit_id]['price']) || 0;
    };

    // affichage des prix à la modification des champs
    $('input[type="number"]').on('blur change', function(){
        // récupération de l'id de l'unité avec l'attribut data
        var unit_id = $(this).data('unitId');

        // calcul du prix (unit_list est défini dans la vue par php)
        var price = get_price(unit_id, $(this).val());

        // récupération de la zone d'affichage
        var info = $('#unit_' + unit_id + ' .js-info');

        if(price < 1){
            info.hide();
        } else {
            info.show();
            info.text(price + ' $');
        }
    });

    // création des unités en ajax
    $('form.unit-factory').on('submit', function(e){
        e.preventDefault();

        var form = $(this);
        var unit_id = $(this.quantity).data('unitId');
        var quantity = $(this.quantity).val();
        var info = form.find('.js-info');
        var price = get_price(unit_id, quantity);

        if(price > 0 && quantity > 0){
            $.ajax({
                type : 'POST',
                data : {ajax : true, action : 'add_to_queue', unit_id : unit_id, quantity : quantity},
                dataType : 'json',
                success : function(data){
                    if(data.status == 'error'){
                        info.show();
                        info.text(data['message']);
                    } else {
                        //remise à zero du formulaire
                        form[0].reset();
                        info.hide();

                        //mise à jour de la queue dans le DOM
                        var q = data['queue'];

                        var queue = $('#js-queue');

                        // on efface le message d'info si la queue était vide
                        queue.find('.alert.alert-info').hide().remove();

                        var time_show = '';
                        if(q['position'] == 0)
                            time_show = '<span data-countdown="' + q['end_time'] + '"></span>'; else
                            time_show = sec_to_hms(q['time_left']);


                        // on insert de l'élément dans le DOM
                        queue.append('<li class="btn-wood" id="queueID_' + q['id'] + '">' + troops[q['unit_id']]['name'] + ' - ' + q['quantity'] + ' (' + time_show + ') ' + '<a class="js-del ui-icon ui-icon-trash" href="#" data-queue-id="' + q['id'] + '"></a></li>');

                        // on lance du compteur
                        if(q['position'] == 0){
                            var countdown = $('#queueID_' + q['id']).find('span');
                            set_countdown(countdown);
                        }

                        // on met à jour la variable globale pour l'affichage des ressouces dans l'en-tête
                        ressources = data['new_ressources'];
                    }
                },
                error : function(xhr, ajaxOptions, thrownError){
                    info.show();
                    info.text('erreur lors de la création des unités');
                    console.log(xhr.status);
                    console.log(thrownError);
                }
            });
        }
    });

    // annuler une construction en cours  / réorganise la file d'attente
    $('#js-queue').on('click tap', 'a.js-del',function(e){
        e.preventDefault();
        simple_ajax_confirm('remove_queue', $(this).data('queueId'), $(this).closest('li'), update_ressources, 'vous perdrez 20% des ressources investi');
    }).sortable({
        axis : 'y',
        cursor : 'move',
        opacity : 0.6,
        update : function(event, ui){
            var queue_id = $(this).sortable('toArray');

            $.post('', {ajax : true, action : 'sort_queue', positions : queue_id}, function(data){
                var queue = $('#js-queue');

                var new_li = '';
                data = JSON.parse(data);
                data.forEach(function(item){
                    var time_show = '';
                    if(item['position'] == 0)
                        time_show = '<span data-countdown="' + item['end_time'] + '"></span>'; else
                        time_show = sec_to_hms(item['time_left']);

                    // on insert de l'élément dans le DOM
                    new_li += '<li class="btn-wood" id="queueID_' + item['id'] + '">' + item['name'] + ' - ' + item['quantity'] + ' (' + time_show + ') ' + '<a class="js-del ui-icon ui-icon-trash" href="#" data-queue-id="' + item['id'] + '"></a></li>';
                });
                queue.html(new_li);

                // on lance du compteur
                var countdown = queue.find('li').first().find('span');
                set_countdown(countdown);

            });
        }
    });

    // callback après avoir annulé une construction
    // on remet à jour les ressources
    var update_ressources = function(data){
        ressources = parseInt(data['new_ressources']);
    };

    // réorganisation de la file d'attente
    //$('#js-queue')


    /************************************
     *             MESSAGES             *
     ************************************/

    // popup d'envoi du message
    var send_mail = {
        state0 : {
            title : 'A qui voulez vous parler ?',
            html : '<label><input type="text" name="pseudo" value="" placeholder="Pseudo"></label><br /><label><input type="text" name="topic" value="" placeholder="Sujet"></label><br /><textarea name="content"></textarea>',
            buttons : { "Envoyer" : true, "Annuler" : false },
            focus : "input[name='pseudo']",
            submit : function(e, v, m, f){
                if(f['pseudo'] != '' && f.topic != '' && f.content != ''){
                    $.post('', {ajax : true, action : 'send_mail', to : f['pseudo'], topic : f['topic'], content : f['content']}).done(function(data){
                        if(data){
                            e.preventDefault();
                            $.prompt.goToState('state1');
                        } else {
                            e.preventDefault();
                            console.log("message non envoyé, l'utilisateur n'existe pas");
                        }
                    }).fail(function(){
                        console.log("erreur lors de l'envoi de la requête")
                    });
                } else {
                    console.log('veuillez remplir tous les champs');
                    e.preventDefault();
                }
            }
        },
        state1 : {
            title : 'Votre message a bien été envoyé'
        }
    };

    // rédiger un nouveau message
    $('.js-new-mail').on('click tap', function(e){
        e.preventDefault();
        $.prompt(send_mail);
    });

    // déplier un message, le marquer comme lu
    $('tr.topic').on('click tap',function(){
        if($(this).hasClass('unread')){
            simple_ajax('mark_as_read', $(this).data('mailId'));
            $(this).removeClass('unread');
        }
        $(this).next('tr').toggle('slow');

        // supprimer un message
    }).on('click tap', 'a.js-del', function(e){
        e.preventDefault();
        var items = [$(this.parentNode.parentNode).next('tr.mail'), $(this).closest('tr.topic')];
        simple_ajax_confirm('delete_mail', $(this).data('mailId'), items)
    });


    /************************************
     *             ATTAQUES             *
     ************************************/

        // annuler une attaque en cours
    $('#js-fleet').on('click tap', 'a.js-del', function(e){
        e.preventDefault();
        simple_ajax_confirm('cancel_attack', $(this).data('fleetId'), $(this).closest('li'), update_fleets)
    });

    // callback après avoir annulé une attaque
    // on restore les quantités d'unités
    var update_fleets = function(data){
        data.forEach(function(item){
            if(item.quantity != null)
                $('figure#unit_' + item.unit_id).find('.js-quantity').html(item.quantity);
        });
    };


});