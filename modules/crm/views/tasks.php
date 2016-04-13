<div class="wrap erp erp-crm-tasks" id="wp-erp">

    <h1><?php _e( 'Tasks', 'erp' ); ?></h1>
    <hr>

    <div class="erp-crm-tasks-wrapper">

        <ul class="erp-crm-tasks-menu">
            <li><a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-tasks', 'tab' => 'my-tasks' ], admin_url( 'admin.php' ) ); ?>"><?php _e( 'My Tasks', 'erp' ); ?></a></li>
            <li><a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-tasks', 'tab' => 'my-tasks' ], admin_url( 'admin.php' ) ); ?>"><?php _e( 'Unassigned Tasks', 'erp' ); ?></a></li>
            <li><a href="<?php echo add_query_arg( [ 'page' => 'erp-sales-tasks', 'tab' => 'my-tasks' ], admin_url( 'admin.php' ) ); ?>"><?php _e( 'Completed Tasks', 'erp' ); ?></a></li>
        </ul>

    </div>
</div>
