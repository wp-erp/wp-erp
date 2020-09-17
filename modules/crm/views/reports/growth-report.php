<?php
if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
    // die();
}

$pro          = false;
$data         = [];
$start        = !empty( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : false;
$end          = !empty( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ): date('Y-m-d');
$filter_type  = !empty( $_POST['filter_type'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_type'] ) ) : 'this_year';

$reports      = erp_crm_growth_reporting_query( $start, $end, $filter_type );

$life_stages  = [
                    ['title' => 'Subscriber'],
                    ['title' => 'Opportunity'],
                    ['title' => 'Lead'],
                    ['title' => 'Customer'],
                ];

if ( function_exists('erp_crm_get_all_life_stages') ) {
    $life_stages  = erp_crm_get_all_life_stages();
    $pro = true;
}

?><div class="wrap">
    <h2 class="report-title"><?php esc_attr_e( 'Growth Report', 'erp' ); ?></h2>
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
                <th><?php esc_attr_e( 'Label', 'erp' ); ?></th>
                <?php foreach( $life_stages as $life_stage ) : ?>
                    <th><?php esc_attr_e( $life_stage['title'], 'erp' ); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach( $reports as $key => $report ) : ?>
                <tr>
                    <td><?php echo esc_html( $key ) ?></td>
                    <?php if ( $pro ) : ?>
                        <?php foreach( $report as $stage => $val ) : ?>
                            <td><?php echo !empty( $report[ $stage ] )  ? esc_attr( $report[ $stage ] ) : 0; ?></td>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <td><?php echo !empty( $report['subscriber'] )  ? esc_attr( $report['subscriber'] )  : 0; ?></td>
                        <td><?php echo !empty( $report['opportunity'] ) ? esc_attr( $report['opportunity'] ) : 0; ?></td>
                        <td><?php echo !empty( $report['lead'] )        ? esc_attr( $report['lead'] )        : 0; ?></td>
                        <td><?php echo !empty( $report['customer'] )    ? esc_attr( $report['customer'] )   : 0; ?></td>
                    <?php endif; ?>
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
