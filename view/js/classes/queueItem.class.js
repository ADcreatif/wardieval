class QueueItem {

    $queue = $('#js-queue');


    constructor(queueID, unitID, name, quantity, position, time_left, end_time) {
        this.queueID = queueID;
        this.unitID = unitID;
        this.name = name;
        this.quantity = quantity;
        this.position = position;
        this.time_left = time_left;
        this.end_time = end_time;
    }

    getDomItem() {
        // on efface le message d'info si la $queue était vide

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

        return li;
    }
}