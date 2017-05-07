'use strict';

$(function () {
    function onAjaxCancelAttack() {

        $.post('', {ajax: true, action: 'cancel_attack', item_id: $(this).data('fleetId')},
            function (data) {
                $(this).closest('li').remove();

                // après avoir annulé une attaque on restore les quantités d'unités
                data.each(function (index, item) {
                    if (item.quantity != null)
                        $('figure#unit_' + item.unit_id).find('.js-quantity').html(item.quantity);
                });
            }, 'json'
        );
    }

    function onClickCancelAttack(event) {
        event.preventDefault();

        $.confirm({
            title: "Confirmation",
            content: "<p>Êtes-vous sur de vouloir annuler cette attaque ?</p>",
            columnClass: 'col-4',
            buttons: {
                OUI: function () {
                    onAjaxCancelAttack()
                },
                non: function () {
                }
            }
        });
    }

    $('#js-fleet').on('click tap', 'a.js-del', onClickCancelAttack);

});