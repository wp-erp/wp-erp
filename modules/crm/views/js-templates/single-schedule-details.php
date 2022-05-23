<div id="erp-crm-single-schedule-details-wrap">
    <p class="title">
        <# if( data.schedule.created_by.ID == wpErpCrm.current_user_id ) { #>
            <?php esc_attr_e( 'You', 'erp' ); ?>
        <# } else { #>
            <strong>{{ data.schedule.created_by.display_name }}</strong>
        <# } #>

        <# if ( data.schedule.type == 'tasks' ) { #>
            <?php esc_attr_e( 'assigned a task', 'erp' ); ?>
        <# } else if( ( data.schedule.type == 'log_activity' ) && ( new Date() < new Date( data.schedule.start_date ) ) ) { #>
            <?php esc_attr_e( 'have scheduled', 'erp' ); ?>
        <# } else { #>
            <?php esc_attr_e( 'logged', 'erp' ); ?>
        <# } #>

        <# if( data.schedule.log_type == 'sms' || data.schedule.log_type == 'email' ) {  #>
            an {{ data.schedule.log_type }}
        <# } else { #>
            a {{ data.schedule.log_type }}
        <# } #>

        for
        <# if( _.contains( data.schedule.contact.types , 'company') ) { #>
            <strong>{{ data.schedule.contact.company }}</strong>
        <# } else { #>
            <strong>{{ data.schedule.contact.first_name }} {{ data.schedule.contact.last_name }}</strong>
        <# } #>

        <# if ( 'tasks' === data.schedule.type ) { #>

            <br><br>
            Assigned To:
            <#
                var names = data.schedule.extra.invited_user.map(function (user) {
                    return user.name;
                });
            #>

            <strong>{{{ names.join('</strong>, <strong>') }}}</strong>

        <# } else { #>
            <# if( data.schedule.extra.invited_user.length > 1 ) { #>
                and
                <# var users = data.schedule.extra.invited_user.map( function( elm ) {
                    if ( elm.id == wpErpCrm.current_user_id ) {
                        if (self.feed && self.feed.type == 'tasks' ) {
                            return 'Yourself';
                        }
                        return 'You';
                    } else {
                        return elm.name;
                    }
                } ).join("<br>") #>

                <strong class="erp-tips" title="{{ users }}">{{ data.schedule.extra.invited_user.length }} others</strong>

            <# } else if ( data.schedule.extra.invited_user.length == 1 ) { #>
                <# if ( data.schedule.extra.invited_user[0].id == wpErpCrm.current_user_id ) {
                        var users = 'You';
                    } else {
                        var users = data.schedule.extra.invited_user[0].name;
                    }
                #>
                and
                <strong>{{ users }}</strong>
            <# } #>
        <# } #>
    </p>
    <hr>
    <# if( data.schedule.log_type == 'email' ) { #>
        <span class='email_subject'>
                <?php esc_html_e( 'Subject', 'erp' ); ?> : {{ data.schedule.email_subject }}
        </span> |
    <# } #>

    <# if( data.schedule.extra.schedule_title ) { #>
        <span class='email_subject'>
            <i class="fa fa-bookmark"></i> {{ data.schedule.extra.schedule_title }}
        </span> |
    <# } #>
    <span class="header">
        <i class="fa fa-calendar-check-o"></i> {{ data.date }}
    </span>
    <hr>
    <div class="message">{{{ data.schedule.message }}}</div>
</div>
