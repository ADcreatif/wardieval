//converts seconds to time
function sec_to_hms(t){
    var s = t % 60;
    t = (t - s) / 60;
    var m = t % 60;
    t = (t - m) / 60;
    var h = t % 60;
    var d = (t - h) / 24;

    d = d > 0 ? d + ' jrs ' : '';
    h = h > 0 ? h + 'h ' : '';
    m = h > 0 || m > 0 ? m + 'm ' : '';

    return d + h + m + s + 's';
}

$(function(){
    var $loading = $('.loading').hide();
    $(document).ajaxStart(function(){$loading.show();}).ajaxStop(function(){$loading.hide();});
});

