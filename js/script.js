jQuery(document).ready(function() {
    jQuery('<div/>', { id: 'popup_container' }).appendTo('body');
    jQuery('body').on('click', '.real-notify-close', function () {
        jQuery(this).parent().slideUp(200);
    });

    function send_popup(title, text, popup_class, delay) {
        title = title !== '' ? '<span class="title">' + title + '</span>' : '';
        text = text !== '' ? text : '';
        popup_class = popup_class !== '' ? popup_class : 'update';
        delay = typeof delay === 'number' ? delay : 5000;

        var object = jQuery('<div/>', {
            class: 'real_notify_popup_notification ' + popup_class,
            html: title + text + '<span class="real-notify-close">&times;</span>'
        });

        jQuery('#popup_container').prepend(object);
        jQuery(object).hide().fadeIn(500);
        setTimeout(function() {
            jQuery(object).slideUp(500);
        }, delay);
    }

    jQuery(document).on('heartbeat-send', function(e, data) {
        data['notify_status'] = 'ready';
    });

    jQuery(document).on('heartbeat-tick.real_notify_tick', function(e, data) {
        console.log("tick");
        if (!data['real_notify']) return;
        jQuery.each(data['real_notify'], function(index, notification) {
            send_popup(notification['title'], notification['content'], notification['type']);
        });
    });

    jQuery(document).on('heartbeat-error', function(e, jqXHR, textStatus, error) {
        console.log('BEGIN ERROR');
        console.log(textStatus);
        console.log(error);
        console.log('END ERROR');
    });
});
