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
                            <details <?php echo $open ?>>
                                <summary><?php echo $item['name'] ?></summary>
                                <?php foreach ( $item['additional'] as $additional ) { ?>
                                    <p>
                                        <?php echo $additional['name'] ?>
                                        <?php echo $additional['balance'] ?>
                                    </p>
                                <?php } ?>
                            </details>
                        <?php } else { ?>
                            <span> <?php echo $item['name'] ?> </span>
                        <?php } ?>
                    </div>
                    <div class="right">
                        <span class="price"><?php echo $item['balance'] ?></span>
                    </div>
                </li>
            <?php } ?>
            <li class="total">
                <span class="account-title"><?php _e( 'Total Balance ', 'erp' ) ?></span> <span class="price"><a
                        href="#"><?php echo $balance ?></a></span>
            </li>
        </ul>
    </div>

</div>
