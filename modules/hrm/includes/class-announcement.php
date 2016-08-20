<?php
namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 *  Announcement class HR
 *
 *  Announcement for employees
 *
 *  @since 0.1
 *
 *  @author weDevs <info@wedevs.com>
 */
class Announcement {

    use Hooker;

    private $post_type = 'erp_hr_announcement';
    private $assign_type = array();

    /**
     *  Load autometically all actions
     */
    function __construct() {
        $this->assign_type = array(
            ''                  => __( '-- Select --', 'erp' ),
            'all_employee'      => __( 'All Employees', 'erp' ),
            'selected_employee' => __( 'Selected Employee', 'erp' )
        );

        $this->action( 'init', 'post_types' ) ;
        $this->action( 'do_meta_boxes', 'do_metaboxes' );
        $this->action( 'save_post', 'save_announcement_meta', 10, 2 );

        $this->filter( 'manage_edit-erp_hr_announcement_columns', 'add_type_columns' );
        $this->filter( 'manage_erp_hr_announcement_posts_custom_column', 'assign_type_edit_columns', 10, 2 );

        // $this->filter( 'parent_file', 'fix_parent_file', 999 );
        // $this->filter( 'submenu_file', 'submenu_file', 999 );

        $this->action( 'admin_menu', 'remove_menu_item', 19 );
    }

    /**
     * Remove the menu item inserted by WordPress
     *
     * Had to do this because when `show_in_menu` is set to false, HR Managers
     * can't create new announcement due to weird permission issue.
     *
     * @return void
     */
    function remove_menu_item() {
        remove_menu_page( 'edit.php?post_type=erp_hr_announcement' );
    }

    /**
     * Fix parent file
     *
     * @param  string  $parent_file
     *
     * @return string
     */
    function fix_parent_file( $parent_file ) {
        global $current_screen;

        if ( $current_screen->post_type == $this->post_type ) {
            $parent_file = 'erp-hr';
        }

        return $parent_file;
    }

    /**
     * Set submenu file
     *
     * @param  string  $submenu_file
     *
     * @return string
     */
    function submenu_file( $submenu_file ) {
        global $current_screen;

        if ( $current_screen->post_type == $this->post_type ) {
            $submenu_file = 'edit.php?post_type=erp_hr_announcement';
        }

        return $submenu_file;
    }

    /**
     * Register Announcement post type
     *
     * @since 0.1
     *
     * @return void
     */
    function post_types() {
        $capability = 'erp_hr_manager';

        register_post_type( $this->post_type, array(
            'label'               => __( 'Announcement', 'erp' ),
            'description'         => '',
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'rewrite'             => array( 'slug' => '' ),
            'query_var'           => false,
            'supports'            => array( 'title', 'editor' ),
            'capabilities'        => array(
                'edit_post'          => $capability,
                'read_post'          => $capability,
                'delete_posts'       => $capability,
                'edit_posts'         => $capability,
                'edit_others_posts'  => $capability,
                'publish_posts'      => $capability,
                'read_private_posts' => $capability,
                'create_posts'       => $capability,
                'delete_post'        => $capability,
            ),
            'labels'          => array(
                'name'               => __( 'Announcement', 'erp' ),
                'singular_name'      => __( 'Announcement', 'erp' ),
                'menu_name'          => __( 'HR Announcement', 'erp' ),
                'add_new'            => __( 'Add Announcement', 'erp' ),
                'add_new_item'       => __( 'Add New Announcement', 'erp' ),
                'edit'               => __( 'Edit', 'erp' ),
                'edit_item'          => __( 'Edit Announcement', 'erp' ),
                'new_item'           => __( 'New Announcement', 'erp' ),
                'view'               => __( 'View Announcement', 'erp' ),
                'view_item'          => __( 'View Announcement', 'erp' ),
                'search_items'       => __( 'Search Announcement', 'erp' ),
                'not_found'          => __( 'No Announcement Found', 'erp' ),
                'not_found_in_trash' => __( 'No Announcement found in trash', 'erp' ),
                'parent'             => __( 'Parent Announcement', 'erp' )
            ),
        ) );
    }

    /**
     * Initialize metabox for ERP HR announcement post type
     *
     * @since 0.1
     *
     * @return void
     */
    function do_metaboxes() {
        add_meta_box( 'erp-hr-announcement-meta-box', __('Announcement Settings', 'erp'), array( $this, 'meta_boxes_cb' ), $this->post_type, 'advanced', 'high' );
    }

    /**
     * Announcement metabox callback function
     *
     * @param  integer $post_id
     *
     * @return void
     */
    function meta_boxes_cb( $post_id ) {
        global $post;

        $employees = erp_hr_get_employees( [ 'number' => -1, 'no_object' => true ] );

        $announcement_type     = get_post_meta( $post->ID, '_announcement_type', true );
        $announcement_users    = get_post_meta( $post->ID, '_announcement_selected_user', true );
        $announcement_employee = ( $announcement_users ) ? $announcement_users : array();

        ?>
            <table class="form-table erp-hr-announcement-meta-wrap-table">
                <tr>
                    <th><?php _e( 'Send Announcement To', 'erp' ); ?></th>
                    <td>
                        <select name="hr_announcement_assign_type" id="hr_announcement_assign_type" style="width:60%">
                            <?php foreach ( $this->assign_type as $key => $type ): ?>
                                <option value="<?php echo $key; ?>" <?php selected( $announcement_type, $key ); ?>><?php echo $type; ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>

                <tr class="selected_employee_field">
                    <th><?php _e( 'Select Employees', 'erp' ); ?></th>
                    <td>
                        <select name="hr_announcement_assign_employee[]" data-placeholder= '<?php echo __( 'Select Employees...', 'erp' ); ?>' id="hr_announcement_assign_employee" class="erp-select2" multiple="multiple">
                            <option></option>
                            <?php
                            foreach ( $employees as $user ) {
                                if ( $user->user_id == get_current_user_id() ) {
                                    continue;
                                }
                                ?>
                                    <option <?php echo in_array( $user->user_id, $announcement_employee ) ? 'selected="selected"' : ''; ?> value='<?php echo $user->user_id  ?>'><?php echo $user->display_name; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <?php do_action( 'hr_announcement_table_last', $post ); ?>

            </table>
            <?php wp_nonce_field( 'hr_announcement_meta_action', 'hr_announcement_meta_action_nonce' ); ?>

            <script>
                (function($){
                    $(document).ready( function() {

                        $('table.erp-hr-announcement-meta-wrap-table').on( 'change', 'select#hr_announcement_assign_type', function() {
                            var self = $(this);

                            if ( self.val() == 'selected_employee' ) {
                                $( 'tr.selected_employee_field' ).show();
                            } else {
                                $( 'tr.selected_employee_field' ).hide();
                            }
                        });

                        $('select#hr_announcement_assign_type').trigger('change')
                    });
                })(jQuery);
            </script>
            <style>
                #hr_announcement_assign_employee {
                    width: 315px;
                }
                tr.selected_employee_field{
                    display: none;
                }
            </style>
        <?php
    }

    /**
     * Add custom column label
     *
     * @since  0.1
     *
     * @param array $columns
     */
    function add_type_columns( $columns ) {
        unset( $columns['date'] );

        $columns['assign_type'] = __( 'Sent To', 'erp' );
        $columns['send_type']   = __( 'Type', 'erp' );
        $columns['date']        = __( 'Date', 'erp' );

        return $columns;
    }

    /**
     * Render custom column content
     *
     * @since  0.1
     *
     * @param  string $column
     * @param  integer $post_id
     *
     * @return void
     */
    function assign_type_edit_columns( $column, $post_id ) {
        global $post;

        if ( $column == 'assign_type' ) {
            $assign_type = get_post_meta( $post_id, '_announcement_type', true );

            if ( $assign_type ) {
                echo $this->assign_type[$assign_type];
            } else {
                _e( 'No employee assigned!', 'erp' );
            }
        }

        if ( 'send_type' == $column ) {
            echo '<i class="fa fa-envelope-o fa-lg"></i>';

            do_action( 'hr_announcement_send_type', $column, $post_id );
        }
    }

    /**
     * Save Announcement post meta
     *
     * @since  0.1
     *
     * @param  integer $post_id
     * @param  object $post
     *
     * @return void
     */
    function save_announcement_meta( $post_id, $post ) {

        if ( ! isset( $_POST['hr_announcement_meta_action_nonce'] ) ) {
            return $post_id;
        }

        if ( ! wp_verify_nonce( $_POST['hr_announcement_meta_action_nonce'], 'hr_announcement_meta_action' ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $post_type = get_post_type_object( $post->post_type );

        if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return $post_id;
        }

        if ( !current_user_can( 'erp_manage_announcement' ) ) {
            return $post_id;
        }

        $announcement_assign_type           = ( isset( $_POST['hr_announcement_assign_type'] ) ) ? $_POST['hr_announcement_assign_type']: '';
        $announcement_assign_employee       = ( isset( $_POST['hr_announcement_assign_employee'] ) ) ? $_POST['hr_announcement_assign_employee']: array();
        $announcement_assign_employee_array = array();

        update_post_meta( $post_id, '_announcement_type', $announcement_assign_type );
        update_post_meta( $post_id, '_announcement_selected_user', $announcement_assign_employee );

        do_action( 'hr_annoucement_save', $post_id );

        if ( $announcement_assign_type == 'selected_employee' ) {

            $this->process_employee_announcement_data( $announcement_assign_employee, $post_id );

        } elseif ( $announcement_assign_type == 'all_employee' ) {

            $employees = erp_hr_get_employees( array( 'no_object' => true ) );

            if ( $employees ) {
                foreach ( $employees as $user ) {
                    $announcement_assign_employee_array[] = $user->user_id;
                }
            }

            $this->process_employee_announcement_data( $announcement_assign_employee_array, $post_id );
        }
    }

    /**
     * Proce employee announcement data
     *
     * @since  2.1
     *
     * @param  array $announcement_employee
     * @param  integer $post_id
     *
     * @return void
     */
    function process_employee_announcement_data( $announcement_employee, $post_id ) {

        $inserted_employee_id = $this->get_assign_employee( $post_id );

        if ( !empty( $inserted_employee_id ) ) {
            foreach ( $inserted_employee_id as $key => $value) {
                $db[] = $value['user_id'];
            }
        } else {
            $db = array();
        }

        $employees         = $announcement_employee;
        $existing_employee = $new_employee = $del_employee = array();

        foreach( $employees as $employee ) {
            if ( in_array( $employee, $db ) ) {
                $existing_employee[] = $employee;
            } else {
                $new_employee[] = $employee;
            }
        }

        $del_employee = array_diff( $db, $existing_employee );

        if ( $del_employee ) {
            $this->delete_assign_employee( $del_employee, $post_id );
        }

        if ( $new_employee ) {
            $this->insert_assign_employee( $new_employee, $post_id );
        }
    }

    /**
     * Get assign Employee
     *
     * @since  0.1
     *
     * @param  integer $post_id
     *
     * @return array
     */
    function get_assign_employee( $post_id ) {

        $results = \WeDevs\ERP\HRM\Models\Announcement::select( ['user_id'] )
                        ->where( ['post_id' => $post_id ] )
                        ->get()
                        ->toArray();

        if ( $results ) {
            return $results;
        } else {
            return array();
        }
    }

    /**
     * Insert assing Employee
     *
     * @since 0.1
     *
     * @param  array $employee_array
     * @param  integer $post_id
     *
     * @return void
     */
    function insert_assign_employee( $employee_array, $post_id ) {
        global $wpdb;

        $values     = '';
        $table_name = $wpdb->prefix.'erp_hr_announcement';
        $i          = 0;

        foreach ( $employee_array as $key => $employee_id ) {
            $sep    = ( $i == 0 ) ? '':',';
            $values .= sprintf( "%s ( %d, %d, '%s')", $sep, $employee_id, $post_id, 'unread' );

            $i++;
        }

        $sql = "INSERT INTO {$table_name} (`user_id`, `post_id`, `status` ) VALUES $values";
        $wpdb->query( $sql );

        $this->send_email( $employee_array, $post_id );

        do_action( 'hr_announcement_insert_assignment', $employee_array, $post_id );

    }

    /**
     * Delete assign Employee
     *
     * @since  0.1
     *
     * @param  array $employee_array
     * @param  integer $post_id
     *
     * @return void
     */
    function delete_assign_employee( $employee_array, $post_id ) {
        if ( ! is_array( $employee_array ) ) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix.'erp_hr_announcement';
        $values     = '';
        $i          = 0;

        foreach ( $employee_array as $key => $employee_id ) {
            $sep    = ( $i == 0 ) ? '' : ',';
            $values .= sprintf( "%s( %d, %d )", $sep, $employee_id, $post_id );

            $i++;
        }

        $sql = "DELETE FROM {$table_name} WHERE (`user_id`, `post_id` ) IN ($values)";

        if ( $values ) {
            $wpdb->query( $sql );
        }
    }

    /**
     * Send Announcement Email
     *
     * @return void
     */
    public function send_email( $employee_ids, $post_id ) {
        $announcement_email = new Emails\HR_Announcement_Email();
        $announcement_email->trigger( $employee_ids, $post_id );
    }
}

