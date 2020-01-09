<div class="note-tab-wrap erp-grid-container">
    <h3><?php esc_html_e( 'Notes', 'erp' ) ?></h3>

    <form action="" class="note-form row" method="post">
        <?php erp_html_form_input( array(
            'name'        => 'note',
            'required'    => true,
            'placeholder' => __( 'Add a note...', 'erp' ),
            'type'        => 'textarea',
            'custom_attr' => array( 'rows' => 3, 'cols' => 30 )
        ) ); ?>

        <input type="hidden" name="user_id" value="<?php echo esc_html( $employee->get_user_id() ); ?>">
        <input type="hidden" name="action" id="erp-employee-action" value="erp-hr-employee-new-note">

        <?php wp_nonce_field( 'wp-erp-hr-employee-nonce' ); ?>
        <?php submit_button( __( 'Add Note', 'erp' ), 'primary' ); ?>
        <span class="erp-loader erp-note-loader"></span>
    </form>

    <?php
    $no_of_notes = 10;

    $notes = $employee->get_notes( $no_of_notes );

    $total_notes = $employee->notes->count();

    if ( $notes ) {
        ?>
        <ul class="erp-list notes-list">
            <?php foreach( $notes as $note ) { ?>
            <li>
                <div class="avatar-wrap">
                    <?php echo get_avatar( $note->user->user_email, 64 ); ?>
                </div>

                <div class="note-wrap">
                    <div class="by">
                        <a href="#" class="author"><?php echo esc_html( $note->user->display_name ); ?></a>
                        <span class="date"><?php echo esc_html( erp_format_date( $note->created_at, __( 'M j, Y \a\t g:i a', 'erp' ) ) ); ?></span>
                    </div>

                    <div class="note-body">
                        <?php echo wp_kses_post( wpautop( $note->comment ) ); ?>
                    </div>
                    <?php if ( current_user_can( 'manage_options' ) OR (wp_get_current_user()->ID == $note->comment_by ) ) { ?>
                        <div class="row-action">
                            <span class="delete"><a href="#" class="delete_note" data-note_id="<?php echo esc_html( $note->id ); ?>"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    <?php } ?>
                </div>
            </li>
            <?php } ?>
        </ul>

    <?php } ?>

    <?php $display_class = ( $total_notes > $no_of_notes ) ? 'show':'hide' ; ?>
    <div class="wperp-load-more-btn <?php echo esc_html( $display_class );?>">
            <?php submit_button(
                'Load More',
                false,
                'erp-load-notes',
                true,
                array(
                    'id' => 'erp-load-notes',
                    'data-total_no' => $total_notes,
                    'data-offset_no' => $no_of_notes,
                    'data-user_id' => $employee->get_user_id() )
                ); ?>
    </div>

</div>
