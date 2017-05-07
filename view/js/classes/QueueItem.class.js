class QueueItem {

    constructor(queueID, unitID, name, quantity, position, time_left, end_time) {
        this.queueID = queueID;
        this.unitID = unitID;
        this.name = name;
        this.quantity = quantity;
        this.position = position;
        this.time_left = time_left;
        this.end_time = end_time;
    }

    generateDomItem() {
        // on efface le message d'info si la $queue était vide

        let span = $('<span>')
            .data('countdown', this.end_time)
            .text('(' + formatTime(this.time_left) + ')');

        let hlink = $('<a>')
            .addClass('js-del ui-icon ui-icon-trash')
            .attr('href', '#')
            .data('queueID', this.queueID);


        return $('<li>').addClass('btn-wood item ui-sortable-handle').attr('id', 'queueID_' + this.queueID)
            .text(this.name + ' - ' + this.quantity + ' ')
            .append(span)
            .append(hlink);
    }

    addToDom(DOMcontainer) {
        DOMcontainer.append(this.generateDomItem());
    }


    static removeFromDom(itemID) {
        $.post('', {ajax: true, action: 'remove_item_from_queue', item_id: itemID},
            function (data) {
                // suppression de l'item dans la liste
                $('#queueID_' + itemID).remove();

                log(data['new_ressources']);

                // actualisation du bon compteur
                setCountdown('#js-queue');

                // actualisation des ressources
                setRessources(data['new_ressources']);
            }, 'json'
        );
    }
}