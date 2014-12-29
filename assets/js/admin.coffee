class Admin

    constructor: ->
        @events()

    events: ->
        $('#erp-employee-new').on('click', @modalNewEmployee)
        # @modalNewEmployee()

    modalNewEmployee: ->
        # @showModal
        console.log('new modal');

( ($) ->
    new Admin();
) jQuery