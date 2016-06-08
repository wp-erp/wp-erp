<?php
namespace WeDevs\ERP\Accounting;

/**
 * Walker Class
 */
class Walker_Charts extends \Walker {

    public $db_fields = array ('parent' => 'parent', 'id' => 'id');

    public function start_el( &$output, $elem, $depth = 0, $args = array(), $id = 0 ) {
        $padding = str_repeat( '&#8212; ', $depth );

        $output .= sprintf( '<option value="%d"%s>%s%s</option>', $elem->id, selected( $args['selected'], $elem->id, false ), $padding, $elem->name ) . "\n";
    }
}