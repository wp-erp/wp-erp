<style type='text/css'>
    .wrap * {
        box-sizing: border-box;
    }

    .wrap .page_title h1 {
        padding: 0;
        margin: 20px 0;
        font-size: 24px;
        color: #000000;
        letter-spacing: 0.22px;
    }

    .modules_wrap {
        margin-bottom: 20px;
    }

    .module_items {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }

    .module_item_col {
        padding: 0 10px;
        flex-basis: 33.33%;
    }

    .module_item {
        padding: 20px;
        background: #FFFFFF;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
    }

    .module_item .icon {
        width: 44px;
        height: 44px;
        border-radius: 100%;
        margin-bottom: 14px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .module_item.hrm .icon {
        background-image: linear-gradient(180deg, #C2E998 0%, #02CC87 100%);
    }

    .module_item.crm .icon {
        background-image: linear-gradient(180deg, #72D0FF 0%, #349EFA 100%);
    }

    .module_item.accounting .icon {
        background-image: linear-gradient(180deg, #FEDB4D 0%, #FFB84E 100%);
    }

    .module_item h3 {
        font-size: 15px;
        color: #000000;
        letter-spacing: 0.14px;
        line-height: 21px;
        margin: 0 0 5px 0;
    }

    .module_item .subtitle {
        padding: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #788383;
        letter-spacing: 0.11px;
    }

    .module_item .switch {
        min-width: 50px;
        max-width: 50px;
    }

    .nav_wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #FFFFFF;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
        margin-bottom: 20px;
    }

    .nav_wrap ul {
        display: flex;
        margin: 0;
    }

    .nav_wrap ul li {
        margin: 0;
    }

    .nav_wrap ul button {
        background: transparent;
        border: none;
        font-size: 13px;
        cursor: pointer;
        color: #000000;
        letter-spacing: 0.12px;
        padding: 19px 20px;
        position: relative;
        display: flex;
        align-items: center;
    }

    .nav_wrap img {
        margin-right: 10px;
    }

    .nav_wrap ul button:after {
        content: '';
        width: 100%;
        height: 0;
        position: absolute;
        left: 0;
        bottom: 0;
        z-index: 9;
        transition: height .1s ease-in-out;
        background: #1A9ED4;
    }

    .nav_wrap ul button i {
        margin-right: 5px;
    }

    .nav_wrap .btn.active, .nav_wrap .btn:hover {
        background: transparent;
        color: #000;
    }

    .nav_wrap .btn.active:after, .nav_wrap .btn:hover:after {
        height: 2px;
    }

    .nav_right {
        display: flex;
        align-items: center;
    }

    .nav_right ul {
        position: relative;
        padding: 0;
        margin-right: 20px;
    }

    .nav_right ul:after, .nav_right ul:before {
        content: '';
        width: 1px;
        height: 21px;
        border-radius: 1px;
        background: #CACACA;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
    }

    .nav_right ul:after {
        right: -10px;
    }

    .nav_right ul:before {
        left: -10px;
    }

    .search-box {
        position: relative;
        display: flex;
        align-items: center;
        margin-right: 20px;
    }

    .search-box input {
        border: 0;
    }

    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        z-index: 9;
        transform: translateY(-50%);
    }

    .erp_addon_row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }

    .erp_addon_col {
        width: 33.33%;
        flex-basis: 33.33%;
        max-width: 33.33%;
        padding: 10px;
    }

    @media (min-width: 1441px) {
        .erp_addon_col {
            width: 25%;
            flex-basis: 25%;
            max-width: 25%;
        }
    }

    .erp_addon_item_row {
        display: flex;
    }

    .erp_addon_item_row_top {
        margin-bottom: 15px;
    }

    .erp_addon_item_row_bottom {
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        margin-bottom: 0;
    }

    .erp-addon-thumb {
        margin-right: 20px;
        min-width: 50px;
        width: 50px;
        height: 50px;
        overflow: hidden;
    }

    .erp-addon-thumb img {
        width: 100%;
        max-width: 100%;
        border-radius: 6px;
    }

    .erp-detail {
        margin-right: 20px;
    }

    .erp-detail .text {
        font-size: 12px;
        color: #788383;
        letter-spacing: 0.11px;
        line-height: 19px;
    }

    .erp-detail .title {
        font-size: 14px;
        color: #000000;
        letter-spacing: 0.13px;
        line-height: 21px;
        margin: 0;
    }

    .erp_addon .bulk_item {
        margin-left: auto;
    }

    .erp-detail .title a {
        color: inherit;
        text-decoration: none;
    }

    .erp-detail .title a:hover {
        color: #0090FF;
    }

    .erp_addon {
        background: #FFFFFF;
        border: 1px solid #E2E2E2;
        border-radius: 3px;
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 22px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: '';
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 2px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    input::placeholder {
        font-size: 12px;
        color: #A5ACB1;
        letter-spacing: 0.11px;
    }

    .search-box i {
        color: #95A5A6;
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .tablenav.top {
        margin-bottom: 20px;
    }

    .ext_cat span {
        display: inline-flex;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 13px;
        color: #0090FF;
        letter-spacing: 0.1px;
        margin-right: 9px;
    }

    .ext_cat span.crm {
        background: #DAE9F7;
        color: #0090FF;
    }

    .ext_cat span.hrm {
        background: #E0F8F5;
        color: #00AE6D;
    }

    .ext_cat span.accounting {
        background: #fdeca8;
        color: #9a7c00;
    }

    .ext_cat .doc_link {
        text-decoration: none;
        color: #0090FF;
        border-left: 1px solid #D8D8D8;
        padding-left: 10px;
    }

    .tablenav.top {
        margin: 0 0 0 -10px;
        padding: 0;
        display: flex;
        height: auto;
    }

    .tablenav.top.hide {
        display: none;
    }

    .tablenav ul, .tablenav li button, .tablenav label {
        display: flex;
        align-items: center;
    }

    .tablenav li button {
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 13px;
        color: #000000;
        letter-spacing: 0.12px;
    }

    .tablenav li button img.state_hover {
        display: none;
    }

    .tablenav li button:hover {
        background: #fff;
    }

    .tablenav li button:hover img.state_hover {
        display: inline-block;
    }

    .tablenav li button:hover img.state_normal {
        display: none;
    }

    .tablenav li button img, .tablenav li button input {
        margin-right: 10px;
    }

    .tablenav li button {
        padding: 5px 10px;
        border-radius: 4px;
        border: 2px solid transparent;
    }

    .tablenav li button:hover {
        border-color: #0090FF;
    }

    .tablenav li button input {
        margin-top: -1px;
    }

    .tablenav li {
        padding: 0 10px;
    }

    .tablenav li.close button {
        width: 34px;
        height: 34px;
        border-radius: 100%;
        background: #FFFFFF;
        border: 1px solid #DCDCDC;
        justify-content: center;
    }

    .tablenav li.close button:hover {
        background: #fe5d6d;
        border-color: #fe5d6d;
        color: #fff;
    }

    .tablenav li.close button img {
        margin-right: 0;
    }

    em.module_version {
        font-size: 11px;
        color: #788383;
        margin-left: 3px;
    }

    @media only screen and ( max-width: 767px ) {
        .module_item_col {
            max-width: 100%;
            flex-basis: 100%;
            margin-bottom: 20px;
        }

        .erp_addon_col {
            width: 100%;
            flex-basis: 100%;
            max-width: 100%;
        }
    }

    @media only screen and ( max-width: 1130px ) {
        .nav_wrap {
            flex-direction: column;
        }

        .nav_right {
            flex-direction: column;
        }

        .nav_right ul {
            margin-right: 0;
        }

        .search-box {
            border: 0.5px solid #ededed;
            border-radius: 3px;
            margin: 5px 0;
        }
    }

    @media only screen and ( max-width: 540px ) {
        .nav_right {
            display: none;
        }

        .search-box {
            display: none;
        }

        .nav_left {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .nav_left ul {
            display: contents;
            position: relative;
            padding: 0;
        }

        .nav_left ul li {
            margin-top: 10px;
        }
    }
</style>

<?php $is_pro_active = function_exists( 'wp_erp_pro' ) ? true : false; ?>

<div class="wrap">
    <div class="wrap_head">
        <div class="page_title">
            <h1><?php esc_html_e( 'Modules & Extensions', 'erp' ); ?></h1>
        </div>

        <div class="modules_wrap">
            <div class="module_items">
                <div class="module_item_col">
                    <div class="module_item hrm">
                        <div class="icon">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/hrm.svg' ); ?>" alt="<?php echo esc_attr( 'HRM' ); ?>" />
                        </div>
                        <h3 class="title"><?php esc_html_e( 'HR Management', 'erp' ); ?></h3>
                        <div class="subtitle">
                            <span><?php esc_html_e( 'Human Resource Management', 'erp' ); ?></span>
                            <label class="switch">
                                <input class="module_action" type="checkbox" data-module-id="hrm" <?php echo wperp()->modules->is_module_active( 'hrm' ) ? 'checked="checked"' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="module_item_col">
                    <div class="module_item crm">
                        <div class="icon">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/crm.svg' ); ?>" alt="<?php echo esc_attr( 'CRM' ); ?>" />
                        </div>
                        <h3 class="title"><?php esc_html_e( 'CR Management', 'erp' ); ?></h3>
                        <div class="subtitle">
                            <span><?php esc_html_e( 'Customer Relationship Management', 'erp' ); ?></span>
                            <label class="switch">
                                <input class="module_action" type="checkbox" data-module-id="crm" <?php echo wperp()->modules->is_module_active( 'crm' ) ? 'checked="checked"' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="module_item_col">
                    <div class="module_item accounting">
                        <div class="icon">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/accounting.svg' ); ?>" alt="<?php echo esc_attr( 'Accounting' ); ?>" />
                        </div>
                        <h3 class="title"><?php esc_html_e( 'Accounting', 'erp' ); ?></h3>
                        <div class="subtitle">
                            <span><?php esc_html_e( 'Accounting Management', 'erp' ); ?></span>
                            <label class="switch">
                                <input class="module_action" type="checkbox" data-module-id="accounting" <?php echo wperp()->modules->is_module_active( 'accounting' ) ? 'checked="checked"' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nav_wrap" id="filter">
            <div class="nav_left">
                <ul>
                    <?php if ( ! $is_pro_active ) : ?>
                        <li>
                            <button id="all" class="btn">
                                <span><?php esc_html_e( 'All', 'erp' ); ?></span>
                            </button>
                        </li>
                    <?php else : ?>
                        <li>
                            <button id="all" class="btn">
                                <span><?php esc_html_e( 'All', 'erp' ); ?></span>
                            </button>
                        </li>
                        <li>
                            <button id="purchased" class="btn">
                                <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/purchaged.svg' ); ?>" alt="<?php echo esc_attr( 'Purchased' ); ?>" />
                                <span><?php esc_html_e( 'Purchased', 'erp' ); ?></span>
                            </button>
                        </li>
                    <?php endif; ?>
                    <li>
                        <button id="hrm" class="btn">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/hrm-colored.svg' ); ?>" alt="<?php echo esc_attr( 'HRM' ); ?>" />
                            <span><?php esc_html_e( 'HRM', 'erp' ); ?></span>
                        </button>
                    </li>
                    <li>
                        <button id="crm" class="btn">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/crm-colored.svg' ); ?>" alt="<?php echo esc_attr( 'CRM' ); ?>" />
                            <span><?php esc_html_e( 'CRM', 'erp' ); ?></span>
                        </button>
                    </li>
                    <li>
                        <button id="accounting" class="btn">
                            <img src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/accounting-colored.svg' ); ?>" alt="<?php echo esc_attr( 'Accounting' ); ?>" />
                            <span><?php esc_html_e( 'Accounting', 'erp' ); ?></span>
                        </button>
                    </li>

                </ul>
            </div>

            <div class="nav_right">
                <ul>
                    <li>
                        <button id="right_all" class="btn">
                            <span><?php esc_html_e( 'All', 'erp' ); ?></span>
                        </button>
                    </li>
                    <?php if ( $is_pro_active ) : ?>
                        <li>
                            <button id="active" class="btn">
                                <span><?php esc_html_e( 'Active', 'erp' ); ?></span>
                            </button>
                        </li>
                        <li>
                            <button id="inactive" class="btn">
                                <span><?php esc_html_e( 'Inactive', 'erp' ); ?></span>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="search-box">
                    <input type="search" id="plugin-search-input" class="wp-filter-search" name="s" value="" placeholder="<?php esc_attr_e( 'Search extensions', 'erp' ); ?>" aria-describedby="live-search-desc" />
                    <i class="fa fa-search"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="tablenav top hide">
        <div class="alignleft actions bulkactions">
            <ul>
                <li class="all-select">
                    <button>
                        <input type="checkbox" id="select_all">
                        <label for="select_all"><?php esc_html_e( 'Select All', 'erp' ); ?></label>
                    </button>
                </li>
                <li class="enable" data-action="activate">
                    <button>
                        <img class="state_normal" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/enable.svg' ); ?>" alt="<?php echo esc_attr( 'Enable' ); ?>" />
                        <img class="state_hover" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/enable-colored.svg' ); ?>" alt="<?php echo esc_attr( 'Enable' ); ?>" />
                        <span><?php esc_html_e( 'Activate', 'erp' ); ?></span>
                    </button>
                </li>
                <li class="disable" data-action="deactivate">
                    <button>
                        <img class="state_normal" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/disable.svg' ); ?>" alt="<?php echo esc_attr( 'Disable' ); ?>" />
                        <img class="state_hover" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/disable-colored.svg' ); ?>" alt="<?php echo esc_attr( 'Disable' ); ?>" />
                        <span><?php esc_html_e( 'Deactivate', 'erp' ); ?></span>
                    </button>
                </li>
                <li class="close">
                    <button id="close_table_nav_btn">
                        <img class="state_normal" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/close.svg' ); ?>" alt="<?php echo esc_attr( 'Disable' ); ?>" />
                        <img class="state_hover" src="<?php echo esc_url( WPERP_ASSETS . '/images/icons/close-colored.svg' ); ?>" alt="<?php echo esc_attr( 'Disable' ); ?>" />
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div id="erp_addon_wrap" class="erp_addon_wrap">
        <?php
        $all_modules  = wperp()->modules->get_modules_extensions();
        $purchase_url = 'https://utm.guru/udfBI'; // URL with UTM for tracking

        if ( $is_pro_active ) {
            $active_modules = wp_erp_pro()->module->get_active_modules();
            $my_modules     = wp_erp_pro()->update->get_licensed_extensions();
            $license_id     = intval( wp_erp_pro()->update->get_license_id() );
            $purchase_url   = trailingslashit( wp_erp_pro()->update->get_base_url() ) . 'pricing?utm_source=wp-admin&utm_medium=link&utm_campaign=erp-pro-extension-page';
            $licensed_users = wp_erp_pro()->update->get_licensed_user();
            $existing_users = wp_erp_pro()->update->count_users();

            if ( ! empty( $license_id ) ) {
                $purchase_url .= "&license_id={$license_id}&action=upgrade";
            }
        }

        asort( $all_modules );

        if ( count( $all_modules ) > 0 ) :
            ?>
            <div class="erp_addon_row" id="view">
                <?php
                foreach ( $all_modules as $module_id => $module ) :
                    $module = (object) $module;
                    $cat_str = implode( ' ', $module->category );
                    $addon_url = $module->module_link . '?utm_source=wp-admin&utm_medium=link&utm_campaign=erp-pro-extension-page';
                    $doc_url = $module->doc_link . '?utm_source=wp-admin&utm_medium=link&utm_campaign=erp-pro-extension-page';

                    if (
                        $is_pro_active
                        && in_array( $module->id, $active_modules )
                        && file_exists( $module->module_file )
                        && wp_erp_pro()->update->is_valid_license()
                        && $licensed_users >= $existing_users
                    ) {
                        $checked   = 'checked="checked"';
                        $is_active = 'active';
                    } else {
                        $checked   = '';
                        $is_active = 'inactive';
                    }

                    $purchased_module = '';

                    if ( $is_pro_active ) {
                        if ( $module->is_pro || in_array( $module->path, $my_modules ) ) {
                            $purchased_module = 'purchased';
                        }
                    }
                    ?>
                    <div class="erp_addon_col <?php echo esc_attr( "$cat_str $is_active $purchased_module" ); ?>">
                        <div class="erp_addon">

                            <div class="erp_addon_item_row erp_addon_item_row_top">
                                <div class="erp-addon-thumb">
                                    <a href="<?php echo esc_url( $addon_url ); ?>" target="_blank">
                                        <img src="<?php echo esc_url( $module->thumbnail ); ?>" alt="<?php echo esc_attr( $module->name ); ?>" />
                                    </a>
                                </div>
                                <div class="erp-detail">
                                    <h3 class="title">
                                        <a href="<?php echo esc_url( $addon_url ); ?>" target="_blank"><?php echo esc_html( $module->name ); ?></a>
                                        <?php if ( $is_pro_active ) : ?>
                                            <em class="module_version">v<?php echo esc_html( $module->version ) ?></em>
                                        <?php endif; ?>
                                    </h3>

                                    <div class="text"><?php echo wp_kses_post( $module->description ); ?></div>
                                </div>
                                <?php if ( $is_pro_active && ! empty( $purchased_module ) ) : ?>
                                    <div class="bulk_item">
                                        <input class="item_check" type="checkbox" value="<?php echo esc_attr( $module->id ); ?>">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="erp_addon_item_row erp_addon_item_row_bottom">
                                <div class="ext_cat">
                                    <?php foreach ( $module->category as $cat ) : ?>
                                        <span class="<?php echo esc_attr( strtolower( $cat ) ); ?>"> <?php echo esc_html( strtoupper( $cat ) ); ?></span>
                                    <?php endforeach; ?>
                                    <a class="doc_link" href="<?php echo esc_url( $module->doc_link ); ?>" target="_blank"><?php esc_html_e( 'Docs', 'erp' ); ?></a>
                                </div>
                                <div class="erp-links">
                                    <?php if ( $is_pro_active && ( $module->is_pro || in_array( $module->path, $my_modules ) ) ) : ?>
                                        <label class="switch">
                                            <input class="extension_action" type="checkbox" <?php echo esc_attr( $checked ); ?>
                                                   data-module-id="<?php echo esc_attr( $module->id ); ?>">
                                            <span class="slider round"></span>
                                        </label>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url( $purchase_url ); ?>" class="button button-primary" target="_blank" title="<?php esc_attr_e( 'Get It', 'erp' ); ?>"><?php esc_html_e( 'Get It', 'erp' ); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        $('#filter').hide();
        $('#erp_addon_wrap').hide();
        $(window).on('load', function () {
            $('#erp_addon_wrap').fadeIn();
            $('#filter').fadeIn();
        });


        <?php if ( $is_pro_active ) : ?>
        $('.erp_addon_col').hide();
        $('.erp_addon_col.purchased').show();
        <?php endif; ?>

        function filterAddonsLeftTab (filterId, isActiveInactivePressed) {
            if (null === isActiveInactivePressed) {
                isActiveInactivePressed = 'right_all'
            }
            $('.nav_left button').removeClass('active');
            $(`#${filterId}`).addClass('active');

            $('.nav_right button').removeClass('active');
            $(`#${isActiveInactivePressed}`).addClass('active');

            $('.erp_addon_wrap').animate({ opacity: 0.1 }, 'fast', function () {
                $('.erp_addon_col').hide();

                if (filterId === 'all') {
                    if (isActiveInactivePressed === 'right_all') {
                        $('.erp_addon_col').show();
                    } else {
                        $('.erp_addon_col.' + isActiveInactivePressed).show();
                    }
                } else {
                    if (isActiveInactivePressed === 'right_all') {
                        $('.erp_addon_col.' + filterId).show();
                    } else {
                        $('.erp_addon_col.' + filterId + '.' + isActiveInactivePressed).show();
                    }
                }

                $('.erp_addon_wrap').animate({ opacity: 1 }, 'fast');
            });
        }

        function updateUrlHash (filterId, tab) {
            var url = location.href.split('#')[0];
            var fragment = location.hash.substring(1); // Get the current fragment identifier without the '#'

            if (fragment.includes(tab + '=')) {
                // If the tab parameter exists, replace its value with the new one
                fragment = fragment.replace(new RegExp(tab + '=[^&]+'), tab + '=' + filterId);
            } else {
                // If the tab parameter doesn't exist, add it with the new value
                fragment += '&' + tab + '=' + filterId;
            }

            history.pushState(null, null, url + '#' + fragment);
        }

        // by default uncheck all checkbox
        $('.item_check').prop('checked', false);

        $('.nav_left button').click(function () {
            var filterId = $(this).attr('id');
            var isActiveInactivePressed = $('.nav_right button.active').attr('id');

            updateUrlHash(filterId, 'left_tab');
            filterAddonsLeftTab(filterId, isActiveInactivePressed);
        });

        if (location.hash) {
            // Get the fragment identifier without the '#'
            var hash = location.hash.substring(1);
            var params = hash.split('&');
            var leftTabValue = null;
            var rightTabValue = null;

            for (var i = 0; i < params.length; i++) {
                var param = params[i].split('=');
                if (param[0] === 'left_tab') {
                    leftTabValue = param[1];
                } else if (param[0] === 'right_tab') {
                    rightTabValue = param[1];
                }
            }

            filterAddonsLeftTab(leftTabValue, rightTabValue);
        } else {
            $('.nav_left button').removeClass('active');
            $('#right_all button').removeClass('active');
            $('#purchased').addClass('active');
            $('#right_all').addClass('active');
        }

        $('.nav_right button').click(function () {
            var filterId = $(this).attr('id');
            updateUrlHash(filterId, 'right_tab');
            $('.nav_right button').removeClass('active');
            $(this).addClass('active');
            var filter_id = $(this).attr('id');
            var isTabPressed = $('.nav_left button.active').attr('id');

            $('.erp_addon_wrap').animate({ opacity: 0.1 }, 'fast', function () {
                if (filter_id == 'right_all') {
                    if (isTabPressed == 'all') {
                        $('.erp_addon_col').show();
                    } else {
                        $('.erp_addon_col').hide();
                        $('.erp_addon_col.' + isTabPressed).show();
                    }
                } else {
                    $('.erp_addon_col').hide();
                    if (isTabPressed == 'all') {
                        $('.erp_addon_col.' + filter_id).show();
                    } else {
                        $('.erp_addon_col.' + filter_id + '.' + isTabPressed).show();
                    }

                }
            });

            $('.erp_addon_wrap').animate({ opacity: 1 }, 'fast');
        });

        $('.module_action').click(function () {
            var module_id = $(this).data('module-id');
            var state = $(this).prop('checked');
            var toggle = (state) ? 'activate' : 'deactivate';

            toastr.success('<?php esc_html_e( 'Please wait!', 'erp' ); ?>', '', { timeOut: 1000 });

            wp.ajax.send('erp-toggle-module', {
                data: {
                    '_wpnonce': '<?php echo esc_attr(wp_create_nonce( 'wp-erp-toggle-module' ) ); ?>',
                    module_id: module_id,
                    toggle: toggle
                },
                success: function (resp) {
                    toastr.success(resp);
                    setTimeout(function () {
                        location.reload();
                    }, 1000)
                },
                error: function (response) {
                    toastr.error(response);
                }
            });

        });

        $('.extension_action').click(function () {
            var module_id = $(this).data('module-id');
            var state = $(this).prop('checked');
            var toggle = (state) ? 'activate' : 'deactivate';
            var th = $(this);

            toastr.success('<?php esc_html_e( 'Please wait!', 'erp' ); ?>', '', { timeOut: 1000 });

            wp.ajax.send('erp-pro-toggle-extension', {
                data: {
                    '_wpnonce': '<?php echo esc_attr(wp_create_nonce( 'wp-erp-pro-toggle-extension' ) ); ?>',
                    module_id: module_id,
                    toggle: toggle
                },
                success: function (resp) {
                    toastr.success(resp);
                    setTimeout(function () {
                        location.reload();
                    }, 1000)
                },
                error: function (response) {
                    toastr.error(response);
                    if (toggle === 'activate') {
                        th.prop('checked', false);
                    } else {
                        th.prop('checked', true);
                    }
                }
            });

        });

        $('.bulkactions .enable, .bulkactions .disable').click(function () {
            var modules = [];
            var toggle = $(this).data('action');

            $('.item_check:checked').each(function () {
                modules.push($(this).val());
            });

            toastr.success('<?php esc_html_e( 'Please wait!', 'erp' ); ?>', '', { timeOut: 1000 });

            wp.ajax.send('erp-pro-toggle-extension', {
                data: {
                    '_wpnonce': '<?php echo esc_attr(wp_create_nonce( 'wp-erp-pro-toggle-extension' ) ); ?>',
                    module_id: modules,
                    toggle: toggle
                },
                success: function (resp) {
                    toastr.success(resp);
                    setTimeout(function () {
                        location.reload();
                    }, 1000)
                },
                error: function (response) {
                    toastr.error(response);
                }
            });

        });

        $('#select_all').click(function () {
            var state = $(this).prop('checked');

            // by default uncheck all checkbox
            $('.item_check').prop('checked', false);

            // check checkbox based on selected menu
            var selected_tab = $('#filter button.active').first().attr('id');

            if (state && selected_tab === 'all') {
                $('.item_check').prop('checked', true);
            } else if (state && selected_tab != 'all') {
                $('div.' + selected_tab).find('.item_check').prop('checked', true);
            }
        });

        $('.item_check').change(function () {
            var ext_length = $('.item_check').length;
            var checked_length = $('.item_check:checked').length;

            if (checked_length > 0) {
                $('.tablenav').removeClass('hide');
            } else {
                $('.tablenav').addClass('hide');
            }

            if (checked_length == ext_length) {
                $('#select_all').prop('checked', true);
            } else {
                $('#select_all').prop('checked', false);
            }
        });

        $('#close_table_nav_btn').click(function () {
            $('.tablenav').addClass('hide');
            $('.item_check').prop('checked', false);
        });

        $('#plugin-search-input').keyup(function () {
            var query = $(this).val().trim().toLowerCase();

            $('.erp_addon').each(function () {
                var title = $(this).find('.title a').text();
                var desc = $(this).find('.text').text();
                var searchContext = title + ' ' + desc;
                searchContext = searchContext.toLowerCase();

                if (searchContext.search(query) === -1) {
                    $(this).parents('.erp_addon_col').hide();
                } else {
                    $(this).parents('.erp_addon_col').show();
                }

            });

        })
    });
</script>
