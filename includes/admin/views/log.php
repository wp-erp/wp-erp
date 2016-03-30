<div class="wrap erp erp-hr-audit-log">

    <h2><?php _e( 'Audit Log', 'erp' ); ?></h2>

    <div id="erp-audit-log-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-audit-log">
                <?php
                $audit_log = new \WeDevs\ERP\Admin\Auditlog_List_Table();
                $audit_log->prepare_items();
                $audit_log->views();

                $audit_log->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
