'use strict';

/* global ressources, troops */


function eventListeners(selector, trigger, callBack) {
    // on peut envoyer un objet jquery ou un selecteur CSS
    let items = (typeof selector == "string") ? $(selector) : selector;

    items.each(function (key, item) {
        $(item).on(trigger, callBack);
    })
}

function setRessources(amount) {
    ressources = parseInt(amount);
}


/**
 * this function starts a countdown if correctly set.
 * for record : <span data-countdown="12/09/2016 05:30:34 pm" >50m 0s</span>
 * @param container selector
 */
let timer;
countdown.setLabels('|s |m |h |jrs |sem | mois | an |||', '|s |m |h |jrs |sem | mois | ans |||', '', ', ', 'maintenant');

function setCountdown(container) {

    if (timer != undefined)
        window.clearInterval(timer);

    $(container).find('li').each(function (key, item) {
        let span = $(item).find('span');
        let date = span.data('countdown');

        // starts countdown on the first instance
        if (key == 0) {
            timer = countdown(
                new Date(date),
                function (ts) {
                    span.text('(' + ts.toString() + ')');

                    //refresh page when countdown is over
                    if (ts.value >= 0) {
                        window.clearInterval(timer);
                        setTimeout(function () {
                            location.reload(true);
                        }, 5000);
                    }
                },
                countdown.DAYS | countdown.HOURS | countdown.MINUTES | countdown.SECONDS
            );


            // display time left on the others
        } else {
            span.text('(' + countdown(
                    null, new Date(date),
                    countdown.DAYS | countdown.HOURS | countdown.MINUTES | countdown.SECONDS
                ).toString() + ')');
        }

    });

}

// retourne une date dans X secondes
/*function getJSEndTime(secondsToAdd){
 let now = new Date();
 return new Date(now.getSeconds() + secondsToAdd);
 }*/

function formatTime(seconds) {
    seconds = parseInt(seconds, 10); // don't forget the second param
    let h = Math.floor(seconds / 3600);
    let m = Math.floor((seconds - (h * 3600)) / 60);
    let s = seconds - (h * 3600) - (m * 60);

    let timeArray = [];

    if (h != 0) timeArray.push(h + 'h');
    if (m != 0) timeArray.push(m + 'm');
    if (s != 0) timeArray.push(s + 's');

    return timeArray.join(' ');
}

function log(item) {
    console.log(item);
}

$(function(){
    /* show hide .loading animation on ajax */
    let $loading = $('.loading');
    $(document).ajaxStart(function(){$loading.show();}).ajaxStop(function(){$loading.hide();});
});

