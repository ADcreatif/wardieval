$(function(){
    /*
     $( ".card" ).draggable({
     //connectWith:'.slot',
     snap: ".slot",          //émente l'élement
     snapMode: "inner",      // aimenter l'intérieur du receveur
     snapTolerance: 50,
     stack:".card",          //met l'élément au dessus
     cursor: "move",
     revert:"invalid"        // remet en place si la destination est invalide
     });
     $('#cards, .slot').sortable({
     connectWith: '#cards,.slot'
     });
     */
    $('#cards, .slot').sortable({
        connectWith : '.slot',
        activeClass : "highlight",
        scroll : false,
        stack : ".card",
        revert : true,
        receive : function(event, ui){
            var $this = $(this);
            console.log($this.hasClass('slot'));
            if($this.hasClass('slot') && $this.children('li').length > 1){
                console.log('il y a déjà une carte dans ce slot');
                $(ui.sender).sortable('cancel');
            }
        }
    });

    /*
     $('.slot').droppable({
     activeClass : "highlight",
     tolerance : "fit",

     drop : function(event,ui){
     console.log('objet déposé');
     }
     });
     */

    $('.special').droppable({
        accept : ".special",
        connectWith : '#cards',
        drop : function(){
            console.log('objet déposé');
        }
    });

    $("div, span, ul, li, img, p, h4, h3").disableSelection();

});
