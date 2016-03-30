<?php
namespace WeDevs\ERP\HRM;

/**
 * Departments walker
 *
 * Walks down the parent-child relationships and renders the departments
 * table.
 *
 * @author Tareq Hasan
 */
class Department_Walker extends \Walker {

    public $db_fields = array ('parent' => 'parent', 'id' => 'id');

    /**
     * [start_el description]
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $elem Department data object.
     * @param int $depth Depth of Department. Used for padding.
     * @param int $current_page Department ID.
     * @param array $args
     *
     * @return void
     */
    public function start_el( &$output, $elem, $depth = 0, $args = array(), $id = 0 ) {
        static $alternate;

        $department = new Department( $elem );
        $alternate  = ( 'alternate' == $alternate ) ? 'even' : 'alternate';
        $padding    = str_repeat( '&#8212; ', $depth );

        ?>
        <tr class="<?php echo $alternate; ?>" id="erp-dept-<?php echo $department->id; ?>">
            <th scope="row" class="check-column">
                <input id="cb-select-1" type="checkbox" name="dept[]" value="1">
            </th>
            <td class="col-">

                <strong><a href="#"><?php echo $padding . $department->name; ?></a></strong>

                <div class="row-actions">
                    <span class="edit"><a href="#" data-id="<?php echo $department->id; ?>" title="<?php _e( 'Edit this item', 'erp' ); ?>"><?php _e( 'Edit', 'erp' ); ?></a> | </span>
                    <span class="trash"><a class="submitdelete" data-id="<?php echo $department->id; ?>" title="Delete this item" href="#"><?php _e( 'Delete', 'erp' ); ?></a></span>
                </div>
            </td>
            <td class="col-">
                <?php
                if ( $lead = $department->get_lead() ) {
                    echo $lead->get_link();
                } else {
                    echo '-';
                }
                ?>
            </td>
            <td class="col-"><?php echo $department->num_of_employees(); ?></td>
        </tr>
        <?php
    }
}