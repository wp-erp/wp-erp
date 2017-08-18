<?php

namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Company;
use WeDevs\ERP\People;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Accounting invoice/sales statement generator class
 *
 * @since 1.2.4
 */
class Statement {

    public $transaction;
    public $customer;
    public $company;
    public $settings = [];


    public function __construct( $transaction, $context = 'admin-panel' ) {
        $this->transaction    = $transaction;
        $this->context        = $context;
        $this->company        = new Company();
        $this->customer       = new People( absint( $transaction->user_id ) );

        $this->invoice_number = isset( $transaction->invoice_number ) ? erp_ac_get_invoice_number( $transaction->invoice_number, $transaction->invoice_format ) : $transaction->id;
        $this->taxinfo        = erp_ac_get_tax_info();

        $this->set_settings();
    }

    private function set_settings() {
        $this->settings['hide_statement_title']  = false;
        $this->settings['statement_extra_class'] = [ 'acc-invoice', 'acc-' . $this->context ];

        if ( $this->company->logo ) {
            $company_logo = wp_get_attachment_image_src( $this->company->logo, 'full' );
            $this->settings['company_logo'] = $company_logo[0];

        } else {
            $this->settings['company_logo'] = null;
        }
    }

    public function print_statement() {
        $response = wp_remote_get('http://localhost/wp-erp/?query=readonly_invoice&trans_id=4&auth=bde41b3cd1b65858d01c3e27a4f292dcc408ae4fe5c01491a05879c9b1137ffc');
        // include WPERP_ACCOUNTING_VIEWS . '/sales/invoice.php';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';

        echo $response['body'];
    }

    public function generate_pdf() {
        $options = new Options();
        $options->set( 'isRemoteEnabled', true );

        $pdf = new DOMPDF( $options );

        ob_start();
        $this->print_statement();
        $html = ob_get_clean();

        $pdf->loadHtml( $html );
        $pdf->add_info( 'Producer', sprintf( 'WP ERP v%s <https://wperp.com/>', WPERP_VERSION ) );
        $pdf->output(['isRemoteEnabled' => true]);
        $pdf->render();

        return $pdf;
    }

}

