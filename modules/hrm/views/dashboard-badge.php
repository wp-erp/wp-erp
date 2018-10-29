<?php if ( current_user_can('erp_create_employee') ): ?>

<?php
$employees    = \WeDevs\ERP\HRM\Models\Employee::where('status', 'active')->count();
$departments  = \WeDevs\ERP\HRM\Models\Department::count();
$designations = \WeDevs\ERP\HRM\Models\Designation::count();

$announcements = get_posts( [
    'post_type' => 'erp_hr_announcement',
    'posts_per_page' => 4
] );

?>

<div class="erp-badge-box box-announce">
    <h2><?php _e( 'Latest Announcement', 'erp' ); ?>
        <a href="<?php echo admin_url( 'edit.php?post_type=erp_hr_announcement' ); ?>" class="btn"><?php _e( 'View All', 'erp' ); ?></a>
    </h2>

    <?php if ( ! $announcements ) : ?>
        <p class="erp-no-announce">No announcement found.</p>
    <?php else: ?>
    <ul class="erp-badge-announce">
        <?php
        foreach( $announcements as $announcement ) {
            echo '<li>' . $announcement->post_title . '</li>';
        }
        ?>
    </ul>
    <?php endif; ?>
</div>

<div class="erp-badge-box box-hr">
    <h2><?php _e( 'HR', 'erp' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=erp-hr' ); ?>" class="btn"><?php _e( 'Visit Dashboard', 'erp' ); ?></a>
    </h2>

    <ul class="erp-badge-hr-count">

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo number_format_i18n( $employees, 0 ); ?></h3>
                <p><?php _e( 'Employees', 'erp' ); ?></p>
            </div>

            <div class="count-footer">
                <a href="<?php echo erp_hr_employee_list_url(); ?>"><?php _e( 'View Employees', 'erp' ); ?></a>
            </div>
        </li><!-- .count-box -->

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo number_format_i18n( $departments, 0 ); ?></h3>
                <p><?php _e( 'Departments', 'erp' ); ?></p>
            </div>

            <?php if ( is_admin() ) : ?>
            <div class="count-footer">
                <a href="<?php echo admin_url( 'admin.php?page=erp-hr&section=department' ); ?>"><?php _e( 'View Departments', 'erp' ); ?></a>
            </div>
            <?php endif; ?>
        </li><!-- .count-box -->

        <li class="erp-count-box">
            <div class="count-inner">
                <h3><?php echo number_format_i18n( $designations, 0 ); ?></h3>
                <p><?php _e( 'Designations', 'erp' ); ?></p>
            </div>

            <?php if ( is_admin() ) : ?>
            <div class="count-footer">
                <a href="<?php echo admin_url( 'admin.php?page=erp-hr&section=designation' ); ?>"><?php _e( 'View Designations', 'erp' ); ?></a>
            </div>
            <?php endif; ?>
        </li><!-- .count-box -->

    </ul>
</div>

<?php endif ?>
