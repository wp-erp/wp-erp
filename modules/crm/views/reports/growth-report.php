<?php
if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
    // die();
}

$data         = [];
$start        = !empty( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : false;
$end          = !empty( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : date( 'Y-m-d' );
$filter_type  = !empty( $_POST['filter_type'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_type'] ) ) : 'this_year';

$reports      = erp_crm_growth_reporting_query( $start, $end, $filter_type );
$life_stages  = erp_crm_get_life_stages_dropdown_raw();

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
                <?php foreach ( $life_stages as $life_stage ) : ?>
                    <th><?php echo esc_html( $life_stage ); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ( $reports as $key => $report ) : ?>
                <tr>
                    <td><?php printf( esc_html__( '%s', 'erp' ), $key ); ?></td>
                    <?php foreach ( $life_stages as $slug => $title ) : ?>
                        <td><?php array_key_exists( $slug, $report ) ? printf( esc_html__( '%s', 'erp' ), $report[ $slug ] ) : esc_html_e( '0', 'erp' ); ?></td>
                    <?php endforeach; ?>
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
