<?php

$data         = [];
$start        = !empty( $_POST['start'] ) ? $_POST['start'] : false;
$end          = !empty( $_POST['end'] ) ? $_POST['end'] : date('Y-m-d');
$filter_type  = !empty( $_POST['filter_type'] ) ? $_POST['filter_type'] : 'this_year';

$reports      = erp_crm_growth_reporting_query( $start, $end, $filter_type );

?><div class="wrap">
    <h2 class="report-title"><?php _e( 'Growth Report', 'erp' ); ?></h2>
    <div class="erp-crm-report-header-wrap">
        <?php erp_crm_growth_report_filter_form(); ?>
        <button class="print" onclick="window.print()">Print</button>
    </div>

    <div class="growth-chart-container">
        <canvas id="growth-chart"></canvas>
    </div>

    <table class="table widefat striped">
        <thead>
            <tr>
                <th><?php _e( 'Label', 'erp' ); ?></th>
                <th><?php _e( 'Subscriber', 'erp' ); ?></th>
                <th><?php _e( 'Opportunity', 'erp' ); ?></th>
                <th><?php _e( 'Lead', 'erp' ); ?></th>
                <th><?php _e( 'Customer', 'erp' ); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach( $reports as $key => $report ) : ?>

                <tr>
                    <td><?php echo $key ?></td>
                    <td><?php echo !empty( $report['subscriber'] )  ? $report['subscriber']  : 0; ?></td>
                    <td><?php echo !empty( $report['opportunity'] ) ? $report['opportunity'] : 0; ?></td>
                    <td><?php echo !empty( $report['lead'] )        ? $report['lead']        : 0; ?></td>
                    <td><?php echo !empty( $report['customer'] )    ? $report['customer']    : 0; ?></td>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<style>
    .report-title {
        padding-bottom: 10px !important;
    }

    .erp-crm-report-filter-form {
        float: left;
        display: flex;
    }

    .erp-crm-report-header-wrap {
        height: 40px;
    }

    .print {
        float: right;
    }

    .table.widefat.striped {
        margin-top: 10px;
    }

    .growth-chart-container {
        height: 400px;
        margin-bottom: 50px;
    }

    @media print {
        .report-title {
            text-align: center;
        }

        .erp-crm-report-header-wrap {
            display: none;
        }

        .table.widefat.striped {
            margin-top: 20px;
        }
    }
</style>
