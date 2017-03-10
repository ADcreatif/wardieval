'use strict';


var formatTime = function (seconds) {
    seconds = parseInt(seconds, 10); // don't forget the second param
    var h = Math.floor(seconds / 3600);
    var m = Math.floor((seconds - (h * 3600)) / 60);
    var s = seconds - (h * 3600) - (m * 60);

    var timeArray = [];

    if (h != 0) timeArray.push(h + 'h');
    if (m != 0) timeArray.push(m + 'm');
    if (s != 0) timeArray.push(s + 's');

    return timeArray.join(' ');

};

var setRessources = function (amount) {
    ressources = parseInt(amount);
};

/**
 * this function starts a countdown if correctly set.
 * for instance : <span data-countdown="12/09/2016 05:30:34 pm" >50m 0s</span>
 * @param container selector
 */
var setCountdown = function (container) {
    // first timer of the queue list by default
    var $container = $(container);

    var firstItem = $container.find('li:first [data-countdown]');

    var end_at = firstItem.data('countdown');

    if ($container == undefined || firstItem.length == 0)
        return;

    firstItem.countdown(end_at, function (event) {
        var format = '%-Ss';
        var t = event.offset;
        if (t.minutes > 0) format = '%-Mm ' + format;
        if (t.hours > 0) format = '%-Hh ' + format;
        if (t.days > 0) format = '%-D jrs ' + format;
        firstItem.text(event.strftime(format));

    }).on('finish.countdown', function () {
        $(this).html('<a href="' + _root_ + 'empire" title="recharger la page">terminé</a>');
        location.reload();
    });
};


$(function () {
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
                if (item_to_delete.length > 1)
                    item_to_delete.forEach(function (item) {
                        $(item).remove();
                    });
                else
                    item_to_delete.remove();
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
    /*
    // annuler une construction en cours  / réorganise la file d'attente
     $('#js-queue').sortable({
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

     var time_show = '<span data-countdown="' + item['end_time'] + '">' + sec_to_hms(item['time_left']) + '</span>';

                    // on insert de l'élément dans le DOM
     new_li += '<li class="btn-wood" id="queueID_' + item['id'] + '">' + item['name'] + ' - ' + item['quantity'] + ' (' + time_show + ') ' + '<a class="js-del ui-icon ui-icon-trash" href="#" data-$queue-id="' + item['id'] + '"></a></li>';
                });
                queue.html(new_li);

                // on lance du compteur
     setCountdown('#js-queue');
     //mean ? empire.setCountdown()
            });
        }
    });
     */
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