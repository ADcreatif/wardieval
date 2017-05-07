'use strict';

class Empire {
    constructor() {
        this.$queue = $('#js-queue');
        //this.buildingQueue = [];
        this.troops = [];

        this.updateTroops();
        this.updateBuildingQueue();

        // event listeners;
        this.$queue.on('click tap', 'a.js-del', this.onClickRemoveItem.bind(this));
        this.$queue.sortable({
            axis: 'y',
            placeholder: "highlight",
            cursor: 'move',
            opacity: 0.6,
            update: function () {
                this.onSortUpdateBuildingQueue()
            }.bind(this)
        });
        eventListeners('form.unit-factory', 'submit', this.onSubmitAddToQueue.bind(this));
        eventListeners('form.unit-factory input[type="number"]', 'blur change', this.onBlurShowPrice.bind(this));
    };

    /** Récupère la file de construction en ajax **/
    updateBuildingQueue() {
        $.post('', {ajax: true, action: 'get_queue'}, function (queue_json) {
            // si la file d'attente est vide
            if (queue_json.length == 0)
                return;

            // sinon on met à jour le dom
            this.$queue.empty();
            for (let index = 0; index < queue_json.length; index++) {
                let i = queue_json[index];

                let queueItem = new QueueItem(i['id'], i['unit_id'], i['name'], i['quantity'], i['position'], i['time_left'], i['end_time']);

                queueItem.addToDom(this.$queue);
            }
            setCountdown('#js-queue');
        }.bind(this), 'json');
    };

    /** Ajoute un élement à la file d'attente **/
    onSubmitAddToQueue(event) {
        event.preventDefault();
        let form = $(event.currentTarget);
        let input = form.find('[name=quantity]');
        let unit_id = input.data('unitId');
        let quantity = input.val();
        let info = form.find('.js-info');
        let price = this.getPrice(unit_id, quantity);

        if (price > 0 && quantity > 0) {
            $.post('', {ajax: true, action: 'add_to_queue', unit_id: unit_id, quantity: quantity},
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

                        // on efface le message d'info si la liste était vide
                        this.$queue.find('.alert').remove();

                        // ajout de l'élément dans le DOM
                        let q = data.queue;
                        let item = new QueueItem(q.id, q.unit_id, q.name, q.quantity, q.position, q.time_left, q.end_time);
                        item.addToDom(this.$queue);

                        // on lance du compteur s'il est en première position
                        setCountdown('#js-queue');

                        // on met à jour la variable globale pour l'affichage des ressouces dans l'en-tête
                        setRessources(data['new_ressources']);
                    }
                }.bind(this),
                'json'
            );
        }
    };

    /** Réorganisation de la liste d'attente en drag&drop **/
    onSortUpdateBuildingQueue() {
        let sortedID = [];
        $(this.$queue.find("li")).each(function (index, item) {
            sortedID.push($(item).find('a').data('queueID'));
        });

        $.post('', {ajax: true, action: 'sort_queue', sortedID: sortedID},
            function () {
                setCountdown('#js-queue');
            }
        );
    };

    /** Enlève un élément de la file d'attente **/
    onClickRemoveItem(event) {
        event.preventDefault();
        $.confirm({
            title: "Confirmation",
            content: "<p>Êtes vous sur de vouloir supprimer cette construction ?</p><p>vous perdrez 20% des ressources investies</p>",
            columnClass: 'col-4',
            buttons: {
                OUI: function () {
                    QueueItem.removeFromDom($(event.target).data('queueID'));
                    setCountdown('#js-queue');
                },
                non: function () {
                }
            }
        });
    };

    /** récupère les unités possédées **/
    updateTroops() {
        $.post('', {ajax: true, action: 'get_troops'}, function (troops_json) {
            this.troops = troops_json;
        }.bind(this), 'json');
    };

    getPrice(unit_id, quantity) {
        if (this.troops.length == 0)
            this.updateTroops();
        return Math.round(quantity * this.troops[unit_id]['price']) || 0;
    };

    onBlurShowPrice(event) {
        let input = $(event.currentTarget);
        // récupération de l'id de l'unité avec l'attribut data
        let unit_id = input.data('unitId');

        // calcul du prix (unit_list est défini dans la vue par php)
        let price = this.getPrice(unit_id, input.val());

        // récupération de la zone d'affichage
        let info = $('#unit_' + unit_id + ' .js-info');

        if (price < 1) {
            info.fadeOut(1000);
        } else {
            info.fadeIn();
            info.text(price + ' $');
        }
    };
}
