$(function(){
    /**
     * calcul du prix des unités
     * @param list tableau d'objets contenant les unitée ou batiment a construire
     * @param unit_id
     * @param quantity
     * @returns {number}
     */
    var get_price = function(list, unit_id, quantity){
        // TODO : ajouter le facteur modifieur
        for (var i = 0; i < list.length; i++){
            if(list[i].id == unit_id ){
                return quantity * list[i].price;
            }
        }
        return 0;
    };

    // affichage du prix en temps réel
    $('input[type="number"]').on('blur change', function(){
        // récupération de l'id de l'unité avec l'attribut data
        var unit_id = $(this).data('unitId');

        // récupération de la valeur du formulaire
        var quantity = parseInt($(this).val());

        // récupération de la zone d'affichage
        var span_info = $('#unit_' + unit_id + ' .js-span-info');

        // calcul du prix (unit_list est défini dans la vue par php)
        var price = get_price(unit_list, unit_id, quantity);

        // on masque la zone s'il n'y a pas de calcul à faire;
        if(price < 1) {
            span_info.hide();
        } else {
            // on boucle sur le tableau des unités qui est crée dans empire.view.php
            span_info.show();
            span_info.text(price + ' $');
        }
    });

    // Compteurs
    var set_countdown = function(item){
        var $this = $(item);
        finalDate = $(item).data('countdown');
        $this.countdown(finalDate, function(event){
            var format = '%-S sec';
            if(event.offset.minutes > 0)
                format = '%-M min ' + format;
            if(event.offset.hours > 0)
                format = '%-H h ' + format;
            if(event.offset.days > 0)
                format = '%-D jrs ' + format;
            $this.html(event.strftime(format));
        }).on('finish.countdown', function(event) {
            $(this).html('terminé');
        });
    }

    $('[data-countdown]').each(function(){
        var $this = $(this);
        finalDate = $(this).data('countdown');
        $this.countdown(finalDate, function(event){
            var format = '%-Ssec';
            if(event.offset.minutes > 0)
                format = '%-Mmin ' + format;
            if(event.offset.hours > 0)
                format = '%-Hh ' + format;
            if(event.offset.days > 0)
                format = '%-D jrs ' + format;
            $this.html(event.strftime(format));
        }).on('finish.countdown', function(event) {
            $(this).html('terminé');
        });
    });




    /****************************************
     *           COMMANDES AJAX
     ****************************************/

    // création des unités en ajax
    $('form.unit-factory').on('submit', function(e){
        // désactuvation de l'envoi du formulaire
        e.preventDefault();

        var qty_input = $(this.quantity);
        var unit_id = qty_input.data('unitId') ;
        var quantity = qty_input.val();
        var span_info = $('#unit_' + unit_id + ' .js-span-info');
        var price = get_price(unit_list, unit_id, quantity);

        if( price > 0 ){
            $.ajax({
                //url: $(this).attr('action'),
                type: 'POST',
                data: {ajax:true, action:'build', unit_id : unit_id, quantity : quantity},
                dataType: 'json',
                success: function(data) {
                    if(data.status == 'error'){
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
                        queue.append('<li id="queue_'+q.queue_id+'">'+q.name+' - '+ q.quantity +' (<span data-countdown="'+q.arrival_time+'">'+q.time_left+'</span>) <a class="alert alert-error" href="#" data-queue-id="'+q.queue_id+'">X</a></li>');

                        // lancement du compteur
                        var item =  $('#queue_'+q.queue_id).find('span');
                        set_countdown(item)

                        // on met à jour la variable globale pour l'affichage des ressouces dans l'en-tête
                        ressources = data.new_ressources;
                        $('#js-ressources').text(ressources);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError){
                    span_info.show();
                    span_info.text('erreur lors de la création des unités');
                    console.log(xhr.status);
                    console.log(thrownError);
                }
            });
        }
    });

    // annuler une construction en cours
    $('#js-queue').on('click','a', function(e){
        e.preventDefault();
        console.log($(this).data('queueId'));
        //TODO : effacer une ligne dans la file d'attente
    });

    // annuler une attaque en cours
    $('#js-fleet').on('click','a.alert-error', function(e){
        // TODO  : ajouter un message de confirmation avant d'effacer la ligne
        e.preventDefault();
        var fleet_id = $(this).data('fleetId');
        var $parent = $(this).closest('li');
        $.ajax({
            type: 'POST',
            data: {ajax:true, action:'remove_fleet', fleet_id : fleet_id},
            dataType: 'json',
            success: function() {
                $parent.remove();
            },
            error: function(xhr, ajaxOptions, thrownError){
                console.log(xhr.status);
                console.log(thrownError);
            }
        });
        //TODO : effacer une ligne dans la file d'attente'
    });
});