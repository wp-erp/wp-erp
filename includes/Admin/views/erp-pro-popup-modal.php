<div class="pro-popup-container">
    <div class="pro-popup-body">
        <div class="pro-popup-left-content">
            <div class='pro-popup-top'>
                <div class='pro-popup-image'>
                    <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/pro-popup/pro-diamond.svg' ); ?>">
                </div>
                <div class='pro-popup-title'>
                    <span class='text-orange'>Upgrade to</span> WP ERP <b>Pro</b>
                </div>
                <div class="pro-popup-desc">
                    to experience even more powerful features 🎉
                </div>
            </div>
            <ul>
                <li class="pro-popup-list">
                    <span class='dashicons dashicons-yes'></span>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo '<div>' . __( '<b>Unlock 12+ premium HR extensions</b> and manage<br /> your employee’s <b>recruitment, payroll, attendance,</b><br /> and more', 'erp' ) . '</div>'; ?>
                </li>
                <li class="pro-popup-list">
                    <span class='dashicons dashicons-yes'></span>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo '<div>' . __( 'Nurture B2B & regular clients with <b>8+ CRM<br /> Integrations</b> like - <b>HubSpot, Mailchimp,<br /> Salesforce, Help Scout,</b> etc.', 'erp' ) . '</div>'; ?>
                </li>
                <li class="pro-popup-list">
                    <span class='dashicons dashicons-yes'></span>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo '<div>' . __( 'From <b>creating invoice to calculating taxes</b>; take<br /> full control of your company’s finances with the<br /> <b>Accounting module</b>', 'erp' ) . '</div>'; ?>
                </li>
                <li class="pro-popup-list">
                    <span class='dashicons dashicons-yes'></span>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo '<div>' . __( '<b>Boost your WooCommerce store</b> with powerful<br /> <b>CRM and Accounting</b> premium integrations ', 'erp' ) . '</div>'; ?>
                </li>
            </ul>
        </div>
        <div class="pro-popup-right-content">
            <ul class='slides'>
                <input type='radio' name='radio-btn' id='img-1' checked />
                <li class='slide-container'>
                    <div class='slide'>
                        <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/pro-popup/erp-pro.svg' ); ?>" id='slide-1' />
                    </div>
                </li>

                <input type='radio' name='radio-btn' id='img-2' />
                <li class='slide-container'>
                    <div class='slide'>
                        <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/pro-popup/crm.svg' ); ?>" id='slide-2' />
                    </div>
                </li>

                <input type='radio' name='radio-btn' id='img-3' />
                <li class='slide-container'>
                    <div class='slide'>
                        <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/pro-popup/accounting.svg' ); ?>" id='slide-3' />
                    </div>
                </li>

                <input type='radio' name='radio-btn' id='img-4' />
                <li class='slide-container'>
                    <div class='slide'>
                        <img src="<?php echo esc_url_raw( WPERP_ASSETS . '/images/pro-popup/woo.svg' ); ?>" id='slide-4' />
                    </div>
                </li>

                <li class='nav-dots'>
                    <label for='img-1' class='nav-dot' id='img-dot-1'></label>
                    <label for='img-2' class='nav-dot' id='img-dot-2'></label>
                    <label for='img-3' class='nav-dot' id='img-dot-3'></label>
                    <label for='img-4' class='nav-dot' id='img-dot-4'></label>
                </li>
            </ul>
        </div>
    </div>
    <div class="pro-popup-footer">
        <div class="pro-popup-footer-button">
            <a href="https://wperp.com/pricing/?nocache=&utm_source=wpdashboard&utm_medium=popup" target="_blank" class="btn-upgrade-to-pro">Upgrade to PRO
                <svg width='16' height='13' viewBox='0 0 16 13' fill='none' xmlns='http://www.w3.org/2000/svg'>
                    <path d='M15.3705 3.8954C15.3729 3.93956 15.3697 3.9845 15.3586 4.02914L14.3717 9.18914C14.3219 9.38864 14.1437 9.52892 13.9388 9.53L8.01729 9.56H8.01501H2.09346C1.88746 9.56 1.70796 9.41924 1.6582 9.21872L0.671282 4.04372C0.659918 3.99788 0.656628 3.95168 0.659499 3.90638C0.277712 3.78572 0 3.42734 0 3.005C0 2.4839 0.422579 2.06 0.942056 2.06C1.46153 2.06 1.88411 2.4839 1.88411 3.005C1.88411 3.29846 1.75007 3.56102 1.54019 3.73448L2.77581 4.98332C3.08809 5.29898 3.52144 5.47994 3.96477 5.47994C4.48897 5.47994 4.98877 5.23022 5.30351 4.81184L7.33416 2.1128C7.16357 1.9418 7.05794 1.70552 7.05794 1.445C7.05794 0.9239 7.48052 0.5 8 0.5C8.51948 0.5 8.94206 0.9239 8.94206 1.445C8.94206 1.69772 8.84205 1.9271 8.68037 2.09684L8.68211 2.09894L10.698 4.80542C11.0127 5.22782 11.5143 5.48 12.04 5.48C12.4874 5.48 12.9081 5.30522 13.2244 4.98782L14.4678 3.74054C14.2535 3.56714 14.1159 3.30206 14.1159 3.005C14.1159 2.4839 14.5385 2.06 15.0579 2.06C15.5774 2.06 16 2.4839 16 3.005C16 3.41606 15.7365 3.76568 15.3705 3.8954ZM14.2754 10.97C14.2754 10.7215 14.0746 10.52 13.8268 10.52H2.22307C1.97533 10.52 1.77447 10.7215 1.77447 10.97V12.05C1.77447 12.2985 1.97533 12.5 2.22307 12.5H13.8268C14.0746 12.5 14.2754 12.2985 14.2754 12.05V10.97Z' fill='white' />
                </svg>
            </a>
        </div>
        <div class="footer-text">
            <div class="pro-popup-footer-content">
                <span class='dashicons dashicons-yes'></span> 10,000+ successful businesses
            </div>
            <div class="pro-popup-footer-content">
                <span class='dashicons dashicons-yes'></span> 14 days no questions asked refund policy
            </div>
            <div class="pro-popup-footer-content">
                <span class='dashicons dashicons-yes'></span> Industry leading 24x7 support
            </div>
        </div>
    </div>
</div>
