<?php
namespace WeDevs\ERP;

use WeDevs\ERP\Framework\Traits\Hooker;

class Validate_Data {

    use Hooker;

    public function __construct() {
        $this->action( 'validate_csv_data', 'validate_csv_data', 10, 3 );
    }

    public function validate_csv_data( $csv_data, $fields, $type ) {
        echo '<pre>';
        print_r($csv_data);
        print_r($fields);
        print_r($type);
        die();
    }
}


