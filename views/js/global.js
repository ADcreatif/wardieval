
var refresh = function(){
    $('#js-ressources').text(ressources++);
};

$(function(){
    window.setInterval(refresh,1000);
});

