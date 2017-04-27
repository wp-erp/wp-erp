;(function($) {
   /**
     * Upload handler helper
     *
     * @param string {browse_button} browse_button ID of the pickfile
     * @param string {container} container ID of the wrapper
     * @param int {max} maximum number of file uplaods
     * @param string {type}
     */
    window.ERP_Uploader = function( action, browse_button, container, drop, type, allowed_type, max_file_size, callback ) {
        this.container = container;
        this.browse_button = browse_button;

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
            //multi_selection: false,
            //urlstream_upload: true,
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
        this.callback = callback;

        $('#' + container).on('click', 'a.attachment-delete', $.proxy(this.removeAttachment, this));
    };


    ERP_Uploader.prototype = {

        init: function (up, params) {
            //this.showHide();
        },

        executeFunctionByName: function (functionName, context, args ) {
            if ( typeof functionName == 'undefined' ) {
                return false;
            }
            var args       = [].slice.call(arguments).splice(2);
            var namespaces = functionName.split(".");
            var func       = namespaces.pop();

            for(var i = 0; i < namespaces.length; i++) {
                context = context[namespaces[i]];
            }
            if ( typeof context[func] === "function" ) {
                return context[func].apply(context, args);
            } else {
                return false;
            }
        },

        added: function (up, files) {

            var $container = $('#' + this.container).find('.erp-attachment-upload-filelist');
            var $container_wrap = $('#' + this.container).find('.erp-attachment-list');

            $.each(files, function(i, file) {

                $container_wrap.append('<li class="erp-image-wrap thumbnail '+file.id+'">'+
                    '<div class="attachment-name erp-img-progress">'+
                        '<div class="upload-item" id="' + file.id + '">'+
                            '<div class="progress progress-striped active">'+
                                '<div class="bar"></div>'+
                            '</div>'+
                            // '<div class="filename original">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>'+
                        '</div>'+
                    '</div>'+
                '</li>');
                // $container.append(
                //     '<div class="upload-item" id="' + file.id + '">'+
                //         '<div class="progress progress-striped active">'+
                //             '<div class="bar"></div>'+
                //         '</div>'+
                //          '<div class="filename original">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>'+
                //     '</div>');
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

            this.uploader.refresh();
        },

        uploaded: function ( up, file, response ) {
            var res = $.parseJSON(response.response),
                data  = {
                    up : up,
                    file: file,
                    response: response
                };

            var callback = this.executeFunctionByName( this.callback.after_uploaded, window, data );
            if ( callback !== false ) {
                return;
            }

            $('#' + file.id + " b").html("100%");
            $('#' + file.id).remove();

            if( res.success ) {
                $('.'+file.id).replaceWith(res.data);
                //$('.erp-pre-load-image-wrap').remove();
               // var $container = $('#' + this.container).find('.erp-attachment-list');
                //$container.append(res.data);
            } else {
                alert(res.error);
            }
        },

        removeAttachment: function(e) {
            e.preventDefault();

            var self = this,
            el = $(e.currentTarget);

            if ( confirm(wpErp.confirmMsg) ) {
                var data = {
                    attach_id : el.data('attach_id'),
                    custom_attr: el.closest('.erp-image-wrap').find('.erp-file-mime').data(),
                    _wpnonce: wpErp.nonce,
                    action : 'erp_file_del'
                };

                jQuery.post(wpErp.ajaxurl, data, function() {
                    el.parent().parent().remove();

                    self.uploader.refresh();
                });
            }
        }
    };
})(jQuery);
