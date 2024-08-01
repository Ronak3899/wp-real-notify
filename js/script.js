jQuery(document).ready(function($) {
    const allowedRoles = real_notify_ajax.allowed_roles;
    const userType = real_notify_ajax.user_type;
    const userRoles = real_notify_ajax.user_roles;
    const hasRole = userRoles.some(role => allowedRoles.includes(role));
    const position = real_notify_ajax.position;
    const enableSound = real_notify_ajax.enable_sound;
    const bgColor= real_notify_ajax.bg_color;
    if ((userType == "logged_in" && !hasRole) || (userType == "logged_out" && userRoles.length > 0)) {
        return;
    }

    $('<div/>', { id: 'popup_container', class: position }).appendTo('body');
    $('body').on('click', '.real-notify-close', function () {
        $(this).closest('.real-notify').slideUp(200, function() {
            $(this).remove();
            process_queue();
        });
    });

    var notification_queue = [];
    var is_processing = false;

    function send_popup(content,image) {
        content = content !== '' ? content : '';
        var delay = 5000;

        var object = $('<div/>', {
            class: 'real-notify',
            html: `
                <div class="notification-style1" style="background-color: ${bgColor};">
                    <span class="close real-notify-close">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="15" height="15" x="0" y="0" viewBox="0 0 365.696 365.696" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                            <g>
                                <path d="M243.188 182.86 356.32 69.726c12.5-12.5 12.5-32.766 0-45.247L341.238 9.398c-12.504-12.503-32.77-12.503-45.25 0L182.86 122.528 69.727 9.374c-12.5-12.5-32.766-12.5-45.247 0L9.375 24.457c-12.5 12.504-12.5 32.77 0 45.25l113.152 113.152L9.398 295.99c-12.503 12.503-12.503 32.769 0 45.25L24.48 356.32c12.5 12.5 32.766 12.5 45.247 0l113.132-113.132L295.99 356.32c12.503 12.5 32.769 12.5 45.25 0l15.081-15.082c12.5-12.504 12.5-32.77 0-45.25zm0 0" fill="#000000" opacity="1" data-original="#000000" class=""></path>
                            </g>
                        </svg>
                    </span>
                    <img src="${image}" alt="Profile Image">
                    <div class="content">
                        ${content}
                    </div>
                </div>
            `
        });

        $('#popup_container').prepend(object);
        $(object).hide().fadeIn(500, function() {
            setTimeout(function() {
                $(object).slideUp(500, function() {
                    $(this).remove();
                    process_queue();
                });
            }, delay);
        });

        if (enableSound) {
            var audio = new Audio(real_notify_ajax.sound_url);
            audio.play();
        }
    }

    function process_queue() {
        if (notification_queue.length > 0) {
            var next_notification = notification_queue.shift();
            send_popup(next_notification.content, next_notification.image);
        } else {
            is_processing = false;
        }
    }

    function add_to_queue(content,image) {
        notification_queue.push({ content: content,image:image });
        if (!is_processing) {
            is_processing = true;
            process_queue();
        }
    }

    function check_notifications() {
        $.ajax({
            url: real_notify_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'real_notify_check_notifications'
            },
            success: function(response) {
                var data = JSON.parse(response);
                $.each(data, function(index, notification) {
                    add_to_queue(notification['content'],notification['image']);
                });
            }
        });
    }

    setInterval(check_notifications, real_notify_ajax.interval);
});