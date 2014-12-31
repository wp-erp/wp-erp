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

        $department = new \WeDevs\ERP\HRM\Department( $elem );
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
                    <span class="edit"><a href="#" title="Edit this item">Edit</a> | </span>
                    <span class="trash"><a class="submitdelete" title="Delete this item" href="#">Delete</a></span>
                </div>
            </td>
            <td class="col-"><?php echo $department->get_lead(); ?></td>
            <td class="col-"><?php echo $department->num_of_employees(); ?></td>
        </tr>
        <?php
    }
}