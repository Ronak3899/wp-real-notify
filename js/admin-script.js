jQuery(document).ready(function($) {

    document.querySelectorAll('input[name="real_notify_user_type"]').forEach(function(element) {
        element.addEventListener('change', function() {
            if (this.value === 'logged_in') {
                document.getElementById('real_notify_roles_row').style.display = 'table-row';
            } else {
                document.getElementById('real_notify_roles_row').style.display = 'none';
            }
        });
    });
    
    document.querySelectorAll('input[name="real_notify_image_source"]').forEach(function(element) {
        element.addEventListener('change', function() {
            if (this.value === 'library') {
                document.getElementById('image_selection_container').style.display = 'block';
            } else {
                document.getElementById('image_selection_container').style.display = 'none';
            }
        });
    });

    // Function to generate the preview popup
    function generatePreview() {
        var content = tinymce.activeEditor.getContent();
        var title = 'Post Title';
        var author = 'Post Author';
        var link = '<a href="javascript:void(0)">Post Link</a>';
        var image_url=$('#real_notify_image_url').val();
        var bgColor = $('#real_notify_bg_color').val();
        var message = content
                        .replace(/{title}/g, title)
                        .replace(/{author}/g, author)
                        .replace(/{link}/g, link);
        var imageHtml = image_url ? `<img src="${image_url}" alt="Image">` : '';
        var previewHtml = `
            <div class="real-notify">
                <div class="notification-style1" style="background-color: ${bgColor}">
                    <span class="close">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="15" height="15" x="0" y="0" viewBox="0 0 365.696 365.696" style="enable-background:new 0 0 512 512" xml:space="preserve" class="">
                            <g>
                                <path d="M243.188 182.86 356.32 69.726c12.5-12.5 12.5-32.766 0-45.247L341.238 9.398c-12.504-12.503-32.77-12.503-45.25 0L182.86 122.528 69.727 9.374c-12.5-12.5-32.766-12.5-45.247 0L9.375 24.457c-12.5 12.504-12.5 32.77 0 45.25l113.152 113.152L9.398 295.99c-12.503 12.503-12.503 32.769 0 45.25L24.48 356.32c12.5 12.5 32.766 12.5 45.247 0l113.132-113.132L295.99 356.32c12.503 12.5 32.769 12.5 45.25 0l15.081-15.082c12.5-12.504 12.5-32.77 0-45.25zm0 0" fill="#000000" opacity="1" data-original="#000000" class=""></path>
                            </g>
                        </svg>
                    </span>
                    ${imageHtml}
                    <div class="content">
                        ${message}
                    </div>
                </div>
            </div>
        `;
    
        $('#real_notify_preview').html(previewHtml);
    
        var position = $('select[name="real_notify_position"]').val();
        $('#real_notify_preview').attr('class', 'real_notify_popup_notification ' + position);
    }

    // Generate preview on page load
    generatePreview();

    $('select[name="real_notify_position"]').on('keyup change', generatePreview);
    $('#real_notify_bg_color').on('input', generatePreview);
    
    // Generate preview on template content change
    var editorId = 'real_notify_message_template';
    if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
        var editor = tinymce.get(editorId);
        editor.on('keyup change', function(e) {
            generatePreview();
        });
    }

    $('#upload_image_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function() {
            var uploaded_image = image.state().get('selection').first();
            console.log(uploaded_image);
            var image_url = uploaded_image.attributes.sizes.thumbnail.url;
            $('#upload_image_button').hide();
            $('#remove_image_button').show();
            $('#real_notify_image_url').val(image_url);
            $('#image_preview').html('<img src="' + image_url + '" style="max-width: 100px;" />');
            generatePreview();
        });
    });

    $(document).on('click', '#remove_image_button', function(e) {
        e.preventDefault();
        $('#upload_image_button').show();
        $('#remove_image_button').hide();
        $('#real_notify_image_url').val('');
        $('#image_preview').html('');
        generatePreview();
    });
});

