$(function(){

    // mise à jour des ressources
    window.setInterval(function(){
        $('#js-ressources').text(ressources++);
    },1000);

});

