'use strict';

var Empire = function () {
    this.$queue = $('#js-queue');
    this.forms = $('form.unit-factory');
    this.buildingQueue = [];
    this.troops = [];
};

Empire.prototype.initialize = function () {
    this.updateTroops();
    this.updateBuildingQueue();

    // event listeners;
    this.$queue.on('click tap', 'a.js-del', this.onClickRemoveItem.bind(this));
    this.$queue.sortable({
        axis: 'y',
        placeholder: "highlight",
        cursor: 'move',
        opacity: 0.6,
        //update: this.updateBuildingQueue()
    });

    this.forms.on('submit', this.onSubmitAddToQueue.bind(this));
    this.forms.find('input[type="number"]').on('blur change', this.onBlurShowPrice.bind(this));

    // Lancement des compteurs
    setCountdown('#js-queue');
};


Empire.prototype.updateBuildingQueue = function () {
    var empire = this;
    $.post('', {ajax: true, action: 'get_queue'}, function (queue_json) {
        empire.$queue.empty();
        $.each(queue_json, function (key, item) {
            empire.buildingQueue[key] = item;
            empire.addQueueToList(
                item['id'],
                item['unit_id'],
                item['name'],
                item['quantity'],
                item['position'],
                item['time_left'],
                item['end_time']
            );

        });
    }, 'json');

};


Empire.prototype.updateTroops = function () {
    var empire = this;
    $.post('', {ajax: true, action: 'get_troops'}, function (troops_json) {
        $.each(troops_json, function (key, item) {
            empire.troops.push(item);

        });
    }, 'json');
};

Empire.prototype.onAjaxSuccessUpdateBuildingQueue = function (data) {
    var queue = $('#js-queue');

    var new_li = '';
    data = JSON.parse(data);
    data.forEach(function (item) {

        var time_show = '<span data-countdown="' + item['end_time'] + '">' + sec_to_hms(item['time_left']) + '</span>';

        // on insert de l'élément dans le DOM
        new_li += '<li class="btn-wood" id="queueID_' + item['id'] + '">' + item['name'] + ' - ' + item['quantity'] + ' (' + time_show + ') ' + '<a class="js-del ui-icon ui-icon-trash" href="#" data-$queue-id="' + item['id'] + '"></a></li>';
    });
    queue.html(new_li);

    // on lance du compteur
    setCountdown('#js-queue');
    //mean ? empire.setCountdown()
};

Empire.prototype.onSortUpdateBuildingQueue = function (e, ui) {
    var sortedID = this.$queue.sortable('serialize');
    //console.log(sortedID);
    /*
     $.ajax({
     url:'',
     data : {ajax : true, action : 'sort_queue', sortedID : sortedID},
     success : this.onAjaxSuccessUpdateBuildingQueue()
     });
     */
};

/**
 * Insert new item into DOM as <li>
 * @param queueID int
 * @param unitID int
 * @param name string
 * @param quantity int
 * @param position int
 * @param time_left int
 * @param end_time Date
 */
Empire.prototype.addQueueToList = function (queueID, unitID, name, quantity, position, time_left, end_time) {
    // on efface le message d'info si la $queue était vide
    $(this.$queue).find('.alert.alert-info').hide().remove();

    var span = $('<span>')
        .data('countdown', end_time)
        .text('(' + formatTime(time_left) + ')');

    var hlink = $('<a>')
        .addClass('js-del ui-icon ui-icon-trash')
        .attr('href', '#')
        .data('queueID', queueID);

    var li = $('<li>')
        .addClass('btn-wood item ui-sortable-handle').attr('id', 'queueID_' + unitID)
        .text(name + ' - ' + quantity + ' - ')
        .append(span)
        .append(hlink);

    $(this.$queue).append(li);
};

/** SOME TOOLS _MOVE THEM LATER_ **/
Empire.prototype.getPrice = function (unit_id, quantity) {
    return Math.round(quantity * this.troops[unit_id]['price']) || 0;
};

/*
 // retourne une date dans X secondes
 Empire.prototype.getJSEndTime = function(secondsToAdd){
 var now = new Date();
 var newDate = new Date(now.getSeconds() + secondsToAdd);
 return newDate;
 };
 */

/** ENDOF TOOLS **/

Empire.prototype.onClickRemoveItem = function (event) {
    event.preventDefault();

    var unit_id = $(event.target).data('queueId');
    var $li_to_remove = $(event.target).closest('li');

    console.log($(event.target));

    $('<p>vous perdrez 20% des ressources investi</p>').confirm(function () {
        $.post(
            '',
            {ajax: true, action: 'remove_queue', item_id: unit_id},
            function (data) {
                // suppression de l'item dans la liste
                $li_to_remove.remove();

                // actualisation du bon compteur
                setCountdown('#js-queue');

                // actualisation des ressources
                setRessources(data['new_ressources']);
            }
        );
    });
};

Empire.prototype.onSubmitAddToQueue = function (event) {
    var empireInstance = this;
    event.preventDefault();
    var form = $(event.currentTarget);
    var input = form.find('[name=quantity]');
    var unit_id = input.data('unitId');
    var quantity = input.val();
    var info = form.find('.js-info');
    var price = this.getPrice(unit_id, quantity);

    if (price > 0 && quantity > 0) {
        $.post(
            '',
            {ajax: true, action: 'add_to_queue', unit_id: unit_id, quantity: quantity},
            function (data) {
                // data.queue : {id: 27, unit_id: 0, position: 2, time_left: 300, end_time: "11/05/2016 04:06:43 pm", quantity: 100}
                if (data.status == 'error') {
                    info.fadeIn();
                    info.text(data['message']);
                    window.setTimeout(function () {
                        info.fadeOut(1000)
                    }, 3000);
                } else {
                    //remise à zero du formulaire
                    form.trigger('reset');
                    info.fadeOut(1000);

                    // ajout de l'élément dans le DOM
                    var q = data.queue;
                    empireInstance.addQueueToList(q.id, q.unit_id, q.quantity, q.position, q['time_left'], q['end_time']);

                    // on lance du compteur s'il est en première position
                    setCountdown('#js-queue');
                    /* if(q.position == 0)
                     empireInstance.setCountdown(item);
                     */
                    // on met à jour la variable globale pour l'affichage des ressouces dans l'en-tête
                    setRessources(data['new_ressources']);
                }
            },
            'json'
        );
    }
};

/** Affiche le prix sous la zone d'achat **/
Empire.prototype.onBlurShowPrice = function (event) {
    var input = $(event.currentTarget);
    // récupération de l'id de l'unité avec l'attribut data
    var unit_id = input.data('unitId');

    // calcul du prix (unit_list est défini dans la vue par php)
    var price = this.getPrice(unit_id, input.val());

    // récupération de la zone d'affichage
    var info = $('#unit_' + unit_id + ' .js-info');

    if (price < 1) {
        info.fadeOut(1000);
    } else {
        info.fadeIn();
        info.text(price + ' $');
    }
};

