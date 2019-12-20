<div class="bank-accounts-section wperp-panel wperp-panel-default">
    <div class="wperp-panel-body pb-0">
        <ul class="wperp-list-unstyled list-table-content list-table-content--border">
            <?php
            $balance = 0;
            foreach ( $items as $item ) {
                $balance += (float) $item['balance'] ?>
                <li>
                    <div class="left">
                        <i class="flaticon-menu-1"></i>
                        <?php
                        $open = 'closed';
                        if ( ! empty( $item['additional'] ) ) {
                            $open = 'open';
                            ?>
                            <details <?php echo esc_html( $open ); ?>>
                                <summary><?php echo esc_html( $item['name'] ); ?></summary>
                                <?php foreach ( $item['additional'] as $additional ) { ?>
                                    <p>
                                        <?php echo esc_html( $additional['name'] ); ?>
                                        <?php echo esc_html( $additional['balance'] ); ?>
                                    </p>
                                <?php } ?>
                            </details>
                        <?php } else { ?>
                            <span> <?php echo esc_html( $item['name'] ); ?> </span>
                        <?php } ?>
                    </div>
                    <div class="right">
                        <span class="price"><?php echo esc_html( $item['balance'] ); ?></span>
                    </div>
                </li>
            <?php } ?>
            <li class="total">
                <span class="account-title"><?php esc_attr_e( 'Total Balance ', 'erp' ); ?></span> <span class="price"><a
                        href="#"><?php echo esc_html( $balance ); ?></a></span>
            </li>
        </ul>
    </div>

</div>
