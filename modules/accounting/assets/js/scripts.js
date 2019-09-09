/*!

 =========================================================
 * WPERP | Accounting - v1.0.0
 =========================================================

 * Product Page: https://wperp.com
 * Copyright 2018 Creative Tim (https://wperp.com)

 =========================================================

 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

 */


$(document).ready(function() {

    // handle dropdown menu
    $('.dropdown-trigger').click(function( e ) {
        e.preventDefault();
        e.stopPropagation();
        var parentElement = $(this).parents('.wperp-has-dropdown');
        // $('.wperp-has-dropdown').removeClass('dropdown-opened');
        parentElement.toggleClass('dropdown-opened');

        // if( parentElement.hasClass('wperp-open') ){
        //     parentElement.find('.wperp-dropdown-toggle').attr('aria-expanded', 'true');
        // }else {
        //     parentElement.find('.wperp-dropdown-toggle').attr('aria-expanded', 'false');
        // }
    });

    $(document).on("click", function(event){
        if( event.target.classList.contains('dropdown-menu') == false ) {
            $('.wperp-has-dropdown').removeClass('dropdown-opened');
        }
    });

    // active select2
    $('.wperp-is-select2').each(function(){
        $(this).select2();
    });

    $('.delete-row').click(function(){
        var confirmMessage = confirm("Press a button!");
        if ( confirmMessage ) {
            $(this).parents('tr').remove();
        }
    });


   /* $('.option-dropdown').click(function (e) {
        e.preventDefault();

        // $(this).attr('tabindex', 1).focus();
        $(this).toggleClass('active');
        $(this).find('.dropdown-content').slideToggle(300);
    });
    $('.option-dropdown').focusout(function () {
        $(this).removeClass('active');
        $(this).find('.dropdown-content').slideUp(300);
    });
    $('.option-dropdown .dropdown-content li a').click(function () {
        $(this).parents('.option-dropdown').find('span').html($(this).html());
        $(this).parents('.option-dropdown').find('input').attr('value', $(this).attr('id'));
    });*/
    /*End Dropdown Menu*/


    /*$('.dropdown-content li').click(function () {
        var input = '<strong>' + $(this).parents('.option-dropdown').find('input').val() + '</strong>',
            msg = '<span class="msg">Hidden input value: ';
        $('.msg').html(msg + input + '</span>');
    });*/


    // Toggle list table rows on small screens
    $( 'tbody' ).on( 'click', '.wperp-toggle-row', function() {
        $( this ).closest( 'tr' ).toggleClass( 'is-row-expanded' );
    });

    $('#cb-select-all').click(function() {
        var isChecked = $(this).prop("checked");

        $('.wperp-table tbody tr:has(th)').find('input[type="checkbox"]').prop('checked', isChecked);

        $(this).closest('.table-container').toggleClass('bulk-actions').find('.bulk-action').toggle();

    });

    $('tbody .form-check-input, thead .form-check-input').on('change', function(){
        if ($('.wperp-table tbody tr th input:checked').length > 1) {
            $(this).closest('.table-container').addClass('bulk-actions').find('.bulk-action').show();
        } else {
            $(this).closest('.table-container').removeClass('bulk-actions').find('.bulk-action').hide();
        }
    });

    $('.close-div').click(function(){
        $(this).closest('.table-container').removeClass('bulk-actions').find('.bulk-action').hide();
    });

    // invoice print trigger
    $('.print-btn').click(function(e){
        e.preventDefault();
        window.print();
    });

    // modal trigger
    $("[data-toggle='wperp-modal']").click(function( e ){
        e.preventDefault();
        var targetModal = $(this).data('target');
        $('#'+targetModal).addClass('wperp-modal-open');
        $('body').addClass('wperp-has-modal');
    });

    // modal dismiss
    $('.wperp-modal .wperp-close').click(function(e){
        e.preventDefault();
        $('body').removeClass('wperp-has-modal');
        $(this).parents('.wperp-modal').removeClass('wperp-modal-open');
    });

    // modal dimiss when click on body
    $(document).on('click', function( e ) {
        if ( e.target.classList.contains('wperp-modal') ){
            $('.wperp-modal .wperp-close').trigger('click');
        }
    });
    
    // dismiss modal when ESC pressed
    document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
            $('.wperp-modal .wperp-close').trigger('click');
        }
    };

});