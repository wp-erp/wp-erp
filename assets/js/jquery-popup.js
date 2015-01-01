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
            button: 'Submit',
            extraClass: '',
            onReady: function() {},
            onSubmit: function() {}
        };

    // The actual plugin constructor
    function Plugin(options) {
        this.settings = $.extend({}, defaults, options);
        this.element = $(this.settings.content);
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
            $('.erp-modal-backdrop, .erp-modal .close').on('click', $.proxy(this.closeModal, this) );
            $('.erp-modal form.erp-modal-form').on('submit', $.proxy(this.formSubmit, this) );
            $( window ).on( 'keydown', $.proxy(this.onEscapeKey, this) );
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

            this.settings.onSubmit.call( $(e.currentTarget), this );
        },

        /**
         * Show modal form
         *
         * @return {void}
         */
        show_modal: function() {
            var $modal = $( '.erp-modal' );

            if ( this.settings.extraClass !== '' ) {
                $modal.addClass( this.settings.extraClass );
            }

            $( '.erp-modal-backdrop' ).show();
            $modal.find('h2').text( this.settings.title );
            $modal.find('.button-primary').text( this.settings.button );
            $modal.find( '.content').empty().html(this.element);
            $modal.show();

            // call the onReady callback
            this.settings.onReady.call( $modal, this );
        },

        /**
         * If pressing ESC close the modal
         *
         * @param  {event}
         *
         * @return {void}
         */
        onEscapeKey: function(e) {
            if ( 27 === e.keyCode ) {
                this.closeModal();
            }
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

            // empty and hide the modal
            $('.erp-modal .content').empty();
            $('.erp-modal-backdrop, .erp-modal').hide();

            // remove the event handler
            $('.erp-modal form.erp-modal-form').off('submit', this.formSubmit);

            if ( this.settings.extraClass !== '' ) {
                $('.erp-modal').removeClass( this.settings.extraClass );
            }
        }
    });

    $.erpPopup = function(options) {
        new Plugin(options);
    };

})(jQuery, window, document);