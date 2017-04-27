jQuery(function($) {

	// Color picker
	$('.colorpick').wpColorPicker();

	$(".tips, .help_tip").tipTip({
    	'attribute' : 'data-tip',
    	'fadeIn' : 50,
    	'fadeOut' : 50,
    	'delay' : 200
    });

    var erp_Image_Uploader = {

        init: function() {
            $('a.erp-image-upload').on('click', this.imageUpload);
            $('a.erp-remove-image').on('click', this.removeBanner);
        },

        imageUpload: function(e) {
            e.preventDefault();

            var file_frame,
                self = $(this);

            if ( file_frame ) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: jQuery( this ).data( 'uploader_title' ),
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' )
                },
                multiple: false
            });

            file_frame.on( 'select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();

                var wrap = self.closest('td');

                wrap.find('input.erp-file-field').val(attachment.id);

                if ( typeof attachment.sizes.thumbnail !== 'undefined' ) {
                    wrap.find('img.erp-option-image').attr('src', attachment.sizes.thumbnail.url);
                } else {
                    wrap.find('img.erp-option-image').attr('src', attachment.url);
                }

                $('.image-wrap', wrap).removeClass('erp-hide');

                $('.button-area', wrap).addClass('erp-hide');
            });

            file_frame.open();

        },

        removeBanner: function(e) {
            e.preventDefault();

            var self = $(this);
            var wrap = self.closest('.image-wrap');
            var instruction = wrap.siblings('.button-area');

            wrap.find('input.erp-file-field').val('0');
            wrap.addClass('erp-hide');
            instruction.removeClass('erp-hide');
        },
    };

    erp_Image_Uploader.init();
});
