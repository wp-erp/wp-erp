<?php if ( $notes ) { ?>
    <?php foreach( $notes as $note ) { ?>
        <li>
            <div class="avatar-wrap">
                <?php echo get_avatar( $note->user->user_email, 64 ); ?>
            </div>

            <div class="note-wrap">
                <div class="by">
                    <a href="#" class="author"><?php echo $note->user->display_name;; ?></a>
                    <span class="date"><?php echo erp_format_date( $note->created_at, __( 'M j, Y \a\t g:i a', 'wp-erp' ) ); ?></span>
                </div>

                <div class="note-body">
                    <?php echo wpautop( $note->comment ); ?>
                </div>

                 <div class="row-actions">
                    <span class="delete"><a href="#" class="delete_note" data-note_id="<?php echo $note->id; ?>"><?php _e( 'Delete Permanently', 'wp-erp' ); ?></a></span>
                </div>
            </div>
        </li>
    <?php } ?>
<?php } ?>
