<?php if ( current_user_can( 'erp_create_employee' ) ) { ?>

<?php
$employees    = \WeDevs\ERP\HRM\Models\Employee::where( 'status', 'active' )->count();
$departments  = \WeDevs\ERP\HRM\Models\Department::count();
$designations = \WeDevs\ERP\HRM\Models\Designation::count();

$announcements = get_posts( [
    'post_type'      => 'erp_hr_announcement',
    'posts_per_page' => 4,
] );

?>

<div class="erp-badge-box box-announce">
    <h2><?php esc_html_e( 'Latest Announcement', 'erp' ); ?>
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=erp_hr_announcement' ) ); ?>" class="btn"><?php esc_html_e( 'View All', 'erp' ); ?></a>
    </h2>

    <?php if ( ! $announcements ) { ?>
        <p class="erp-no-announce"><?php esc_html_e( 'No announcement found.', 'erp' ); ?></p>
    <?php } else { ?>
    <ul class="erp-badge-announce">
        <?php
        foreach ( $announcements as $announcement ) {
            echo '<li>' . esc_html( $announcement->post_title ) . '</li>';
        }
        ?>
    </ul>
    <?php } ?>
</div>

<div class="erp-badge-box box-hr">
    <h2><?php esc_html_e( 'HR', 'erp' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr' ) ); ?>" class="btn"><?php esc_html_e( 'Visit Dashboard', 'erp' ); ?></a>
    </h2>

    <ul class="erp-badge-hr-count">

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo esc_html( number_format_i18n( $employees, 0 ) ); ?></h3>
                <p><?php esc_html_e( 'Employees', 'erp' ); ?></p>
            </div>

            <div class="count-footer">
                <a href="<?php echo esc_url( erp_hr_employee_list_url() ); ?>"><?php esc_html_e( 'View Employees', 'erp' ); ?></a>
            </div>
        </li><!-- .count-box -->

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo esc_html( number_format_i18n( $departments, 0 ) ); ?></h3>
                <p><?php esc_html_e( 'Departments', 'erp' ); ?></p>
            </div>

            <?php if ( is_admin() ) { ?>
            <div class="count-footer">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=people&sub-section=department' ) ); ?>"><?php esc_html_e( 'View Departments', 'erp' ); ?></a>
            </div>
            <?php } ?>
        </li><!-- .count-box -->

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo esc_html( number_format_i18n( $designations, 0 ) ); ?></h3>
                <p><?php esc_html_e( 'Designations', 'erp' ); ?></p>
            </div>

            <?php if ( is_admin() ) { ?>
            <div class="count-footer">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=people&sub-section=designation' ) ); ?>"><?php esc_html_e( 'View Designations', 'erp' ); ?></a>
            </div>
            <?php } ?>
        </li><!-- .count-box -->

    </ul>
</div>

<?php } ?>
