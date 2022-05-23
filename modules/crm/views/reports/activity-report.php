<?php

$data    = [];
$total   = 0;
$start   = ! empty( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : false;
$end     = ! empty( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : erp_current_datetime()->format( 'Y-m-d' );

$reports = erp_crm_activity_reporting_query( $start, $end );

foreach ( $reports as $report ) {
    switch ( $report->type ) {
        case 'email':
            $data['Emails'] = $report->total;
            break;

        case 'log_activity':
            $data['Schedules'] = $report->total;
            break;

        case 'tasks':
            $data['Tasks'] = $report->total;
            break;

        case 'new_note':
            $data['Notes'] = $report->total;
            break;
    }
}

?><div class="wrap">
    <h2 class="report-title"><?php esc_html_e( 'Activity Report', 'erp' ); ?></h2>
    <div class="erp-crm-report-header-wrap">
        <?php erp_crm_activity_report_filter_form(); ?>
        <button class="print" onclick="window.print()"><?php esc_html_e( 'Print', 'erp' ); ?></button>
    </div>
    <table class="table widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Types', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Count', 'erp' ); ?></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ( $data as $key => $value ) {
                echo '<tr><td>' . esc_html__( $key, 'erp' ) . '</td>';
                echo '<td>' . esc_html( $value ) . '</td></tr>';

                $total += $value;
            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <td><?php esc_html_e( 'Total', 'erp' ); ?></td>
                <td><?php echo esc_html( $total ); ?></td>
            </tr>
        </tfoot>
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
        height: 25px;
    }

    .print {
        float: right;
    }

    .table.widefat.striped {
        margin-top: 10px;
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
