/* jshint devel:true */

/**
 * A simple jQuery modal plugin for our own needs
 *
 * @author Tareq Hasan
 */
;(function($, window, document, undefined) {

    // Create the defaults once
    var pluginName = 'erpPopup',
        defaults = {
            title: '',
            content: '',
            id: '',
            button: 'Submit',
            extraClass: '',
            onReady: function() {},
            onSubmit: function() {},
            beforeClose: function() {}
        };

    // The actual plugin constructor
    function Plugin(options) {
        this.settings = $.extend({}, defaults, options);
        this.element = $(this.settings.content);
        this.id = this.settings.id;
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {

        /**
         * initialize the plugin
         *
         * @return {void}
         */
        init: function() {
            this.show_modal();
            this.bindEvents();
        },

        /**
         * Bind the DOM events
         *
         * @return {void}
         */
        bindEvents: function() {
            // close the modal window
            $('form.erp-modal-form', '#'+this.settings.id).on('submit', $.proxy(this.formSubmit, this) );
            $('.erp-modal-backdrop, .erp-modal .close').on('click', $.proxy(this.closeModal, this) );

            $('body').on( 'keydown', '#'+this.id, $.proxy(this.onEscapeKey, this) );
            $('#'+this.id).focus();

        },

        /**
         * form submit callback
         *
         * @param  {event}
         *
         * @return {void}
         */
        formSubmit: function(e) {
            e.preventDefault();
            $('.erp-modal-backdrop, .erp-modal' ).find( '.erp-loader' ).removeClass('erp-hide');
            this.settings.onSubmit.call( $(e.currentTarget), this );
        },

        /**
         * Show modal form
         *
         * @return {void}
         */
        show_modal: function() {

            if ( this.id === '' ) {
                return;
            }

            if ( $('#'+this.id).length ) {
                return;
            }

            var $modal = $( '#erp-modal' ).find( '.erp-modal' ),
                $clone_modal = $modal.clone();


            if ( this.settings.extraClass !== '' ) {
                $clone_modal.addClass( this.settings.extraClass );
            }

            $clone_modal.attr( 'id', this.id );
            $clone_modal.attr( 'tabindex', -1 );
            $clone_modal.addClass( 'erp-count-class' );
            $clone_modal.find('h2').text( this.settings.title );

            if ( this.settings.button === '' ) {
                $clone_modal.find('footer').remove();
            } else {
                $clone_modal.find('.button-primary').text( this.settings.button );
            }

            $clone_modal.find( '.content').empty().html(this.settings.content);

            $clone_modal.show();

            $('body').append( $clone_modal );


            var zindexContent = 600*$('body').find('.erp-count-class').length,
                zindexback = zindexContent-1;

            $("#"+this.id).css('z-index', zindexContent );
            $("#"+this.id).after( '<div style="z-index: '+zindexback+'" class="erp-modal-backdrop '+this.id+'"></div>');
            $('.'+this.id).show();

            // $( '.erp-modal-backdrop' ).show();
            // $modal.find('h2').text( this.settings.title );
            // $modal.find('.button-primary').text( this.settings.button );
            // $modal.find( '.content').empty().html(this.element);
            // $modal.show();

            // call the onReady callback
            this.settings.onReady.call( $clone_modal, this );

        },

        /**
         * If pressing ESC close the modal
         *
         * @param  {event}
         */
        onEscapeKey: function(e) {
            if ( 27 === e.keyCode ) {
                this.closeModal(e);
            }
        },

        /**
         * Disable the submit button
         *
         * @return {void}
         */
        disableButton: function() {
            $( 'button[type=submit]', '.erp-modal' ).attr( 'disabled', 'disabled' );
        },

        /**
         * Enable the submit button
         *
         * @return {void}
         */
        enableButton: function() {
            $( 'button[type=submit]', '.erp-modal' ).removeAttr( 'disabled' );
        },

        /**
         * Close the modal dialog
         *
         * @param  {event}
         *
         * @return {void}
         */
        closeModal: function(e) {
            if ( typeof e !== 'undefined' ) {
                e.preventDefault();
            }

            $('.erp-modal-backdrop, .erp-modal' ).find( '.erp-loader' ).addClass('erp-hide');

            this.settings.beforeClose.call( false, this );

            $('#' + this.id).remove();
            $('.' + this.id).remove();
            return;
            // var modal = $('.erp-modal' );

            // // empty and hide the modal
            // $( '.content', modal ).empty();
            // $( '.erp-modal-backdrop, .erp-modal').hide();

            // // remove the event handler
            // $( 'form.erp-modal-form', modal ).off('submit', this.formSubmit);

            // if ( this.settings.extraClass !== '' ) {
            //     modal.removeClass( this.settings.extraClass );
            // }

            // this.enableButton();
        },

        showError: function(message) {
            $('.erp-modal-backdrop, .erp-modal' ).find( '.erp-loader' ).addClass('erp-hide');
            alert(message);

        }
    });

    $.erpPopup = function(options) {
        new Plugin(options);
    };

})(jQuery, window, document);
