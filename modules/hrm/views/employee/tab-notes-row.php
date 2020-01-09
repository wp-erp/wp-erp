<?php if ( $notes ) { ?>
    <?php foreach( $notes as $note ) { ?>
        <li>
            <div class="avatar-wrap">
                <?php echo get_avatar( $note->user->user_email, 64 ); ?>
            </div>

            <div class="note-wrap">
                <div class="by">
                    <a href="#" class="author"><?php echo esc_html( $note->user->display_name ) ; ?></a>
                    <span class="date"><?php echo esc_html( erp_format_date( $note->created_at, __( 'M j, Y \a\t g:i a', 'erp' ) ) ); ?></span>
                </div>

                <div class="note-body">
                    <?php echo wp_kses_post( wpautop( $note->comment ) ); ?>
                </div>
                 <?php if( current_user_can( 'manage_options' ) OR (wp_get_current_user()->ID == $note->comment_by ) ) { ?>
                 <div class="row-action">
                    <span class="delete"><a href="#" class="delete_note " data-note_id="<?php echo esc_html( $note->id ); ?>"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                </div>
                 <?php } ?>
            </div>
        </li>
    <?php } ?>
<?php } ?>
