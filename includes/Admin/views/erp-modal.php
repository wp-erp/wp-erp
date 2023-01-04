<div id="erp-modal">
    <div class="erp-modal">

        <span id="modal-label" class="screen-reader-text"><?php esc_html_e( 'Modal window. Press escape to close.', 'erp' ); ?></span>
        <a href="#" class="close">× <span class="screen-reader-text"><?php esc_html_e( 'Close modal window', 'erp' ); ?></span></a>

        <form action="" class="erp-modal-form" method="post">
            <header class="modal-header">
                <h2>&nbsp;</h2>
            </header>

            <div class="content-container modal-footer">
                <div class="content"><?php esc_html_e( 'Loading', 'erp' ); ?></div>
            </div>

            <footer>
                <ul>
                    <li>
                        <div class="erp-loader erp-hide"></div>
                    </li>
                    <li>
                        <span class="activate">
                            <button type="submit" class="button-primary"></button>
                        </span>
                    </li>
                </ul>
            </footer>
        </form>
    </div>
    <div class="erp-modal-backdrop"></div>
</div>
