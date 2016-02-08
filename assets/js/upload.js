;(function($) {
   /**
     * Upload handler helper
     *
     * @param string {browse_button} browse_button ID of the pickfile
     * @param string {container} container ID of the wrapper
     * @param int {max} maximum number of file uplaods
     * @param string {type}
     */
    window.WPUF_Uploader = function (action, browse_button, container, drop, max, type, allowed_type, max_file_size) {
        console.log('mishu1', browse_button, container, drop); 
        this.container = container;
        this.browse_button = browse_button;
        this.max = max || 1;
        this.count = $('#' + container).find('.wpuf-attachment-list > li').length; //count how many items are there

        //if no element found on the page, bail out
        if( !$('#'+browse_button).length ) {
            return;
        }

        //instantiate the uploader
        this.uploader = new plupload.Uploader({
            dragdrop: true,
            drop_element: drop,
            runtimes : 'html5,html4',
            browse_button: browse_button,
            container: container,
           // multipart: true,
            multipart_params: {
                action: action, 
                file_id: $( '#' + browse_button ).data('file_id'),
                _wpnonce: wpErp.nonce
            },
            //multiple_queues: false,
           // multi_selection: false,
           // urlstream_upload: true,
           // file_data_name: 'wpuf_file',
            max_file_size: max_file_size + 'kb',
            url: wpErp.plupload.url + '&type=' + type,
            flash_swf_url: wpErp.flash_swf_url,
            filters: [{
                title: 'Allowed Files',
                extensions: allowed_type
            }],

            views: {
                list: true,
                thumbs: true, // Show thumbs
                active: 'thumbs'
            },
     
            // Flash settings
            flash_swf_url : '/plupload/js/Moxie.swf',
         
            // Silverlight settings
            silverlight_xap_url : '/plupload/js/Moxie.xap'

        });

        //attach event handlers
        this.uploader.bind('Init', $.proxy(this, 'init'));
        this.uploader.bind('FilesAdded', $.proxy(this, 'added'));
        this.uploader.bind('QueueChanged', $.proxy(this, 'upload'));
        this.uploader.bind('UploadProgress', $.proxy(this, 'progress'));
        this.uploader.bind('Error', $.proxy(this, 'error'));
        this.uploader.bind('FileUploaded', $.proxy(this, 'uploaded'));

        this.uploader.init();

        $('#' + container).on('click', 'a.attachment-delete', $.proxy(this.removeAttachment, this));
    };

    WPUF_Uploader.prototype = {

        init: function (up, params) {
            console.log('mishu2');
            this.showHide();
        },

        showHide: function () {
            console.log('mishu3');
            if ( this.count >= this.max) {
                $('#' + this.container).find('.file-selector').hide();

                return;
            };

            $('#' + this.container).find('.file-selector').show();
        },

        added: function (up, files) {
            var $container = $('#' + this.container).find('.erp-attachment-upload-filelist');

            this.count += 1;
            this.showHide();

            $.each(files, function(i, file) {
                $container.append(
                    '<div class="upload-item" id="' + file.id + '"><div class="progress progress-striped active"><div class="bar"></div></div><div class="filename original">' +
                    file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                    '</div></div>');
            });

            up.refresh(); // Reposition Flash/Silverlight
            up.start();
        },

        upload: function (uploader) {
            this.uploader.start();
        },

        progress: function (up, file) {
            var item = $('#' + file.id);

            $('.bar', item).css({ width: file.percent + '%' });
            $('.percent', item).html( file.percent + '%' );
            
        },

        error: function (up, error) {
            $('#' + this.container).find('#' + error.file.id).remove();

            var msg = '';
            switch(error.code) {
                case -600:
                    msg = 'The file you have uploaded exceeds the file size limit. Please try again.';
                    break;

                case -601:
                    msg = 'You have uploaded an incorrect file type. Please try again.';
                    break;

                default:
                    msg = 'Error #' + error.code + ': ' + error.message;
                    break;
            }

            alert(msg);

            this.count -= 1;
            this.showHide();
            this.uploader.refresh();
        },

        uploaded: function (up, file, response) {
            
            var res = $.parseJSON(response.response);
            
            $('#' + file.id + " b").html("100%");
            $('#' + file.id).remove();

            if(response.response !== 'error') {
                var $container = $('#' + this.container).find('.erp-attachment-list');
                $container.append(res.data);
            } else {
                alert(res.error);

                this.count -= 1;
                this.showHide();
            }
        },

        removeAttachment: function(e) {
            console.log('mishu7');
            e.preventDefault();

            var self = this,
            el = $(e.currentTarget);

            if ( confirm(wpErp.confirmMsg) ) {
                var data = {
                    'attach_id' : el.data('attach_id'),
                    'nonce' : wpErp.nonce,
                    'action' : 'wpuf_file_del'
                };

                jQuery.post(wpErp.ajaxurl, data, function() {
                    el.parent().parent().remove();

                    self.count -= 1;
                    self.showHide();
                    self.uploader.refresh();
                });
            }
        }
    };
})(jQuery);