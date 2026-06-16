<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Audit log handler class
 */
class Log {
    use Hooker;

    /**
     * Load autometically when class inistantiate
     *
     * @since 1.7.2
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_crm_log_customer_new', 'create_customer' );
        $this->action( 'erp_crm_log_customer_del', 'delete_customer', 10, 3 );
        $this->action( 'erp_crm_log_customer_edit', 'update_customer', 10, 2 );
        $this->action( 'erp_crm_log_customer_restore', 'restore_customer' );
        $this->action( 'erp_crm_log_activity_new', 'create_activity' );
        $this->action( 'erp_crm_log_contact_group_new', 'create_contact_group' );
        $this->action( 'erp_crm_log_contact_group_del', 'delete_contact_group' );
        $this->action( 'erp_crm_log_assign_contact_group', 'assign_contact_group', 10, 2 );
        $this->action( 'erp_crm_log_assign_contact_company', 'assign_contact_company', 10, 3 );
    }

    /**
     * Converts key to title
     *
     * @param string $str
     *
     * @return string
     */
    public function key_to_title( $str ) {
        $str = explode( '_', $str );

        $str = array_map( function( $w ) {
            return ucfirst( $w );
        }, (array) $str );

        return implode( ' ', $str );
    }

    /**
     * Add log when new customer created
     *
     * @since 1.7.2
     *
     * @param array $data
     *
     * @return void
     */
    public function create_customer( $data ) {
        $name = $data['type'] === 'company' ? $data['company'] : $data['first_name'] . ' ' . $data['last_name'];

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => ucfirst( $data['type'] ),
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s has been created', 'erp' ), $name, $data['type'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when a customer updated
     *
     * @since 1.7.2
     *
     * @param array $data
     * @param array $old_data
     *
     * @return void
     */
    public function update_customer( $data, $old_data ) {
        $type = $data['type'];
        $name = $type === 'company' ? $old_data['company']: $old_data['first_name'] . ' ' . $old_data['last_name'];
        unset( $old_data['avatar'], $old_data['types'], $old_data['assign_to'] );
        $old_data = erp_array_flatten( (array) $old_data );
        $changes  = erp_get_array_diff( $data, $old_data, true );

        array_walk( $changes, function ( &$key ) {
            if ( isset( $key['contact_owner'] ) ) {
                if ( $key['contact_owner'] ) {
                    $owner                = \get_user_by( 'ID', intval( $key['contact_owner'] ) );
                    $key['contact_owner'] = $owner->display_name;
                } else {
                    $key['contact_owner'] = __( 'No Owner', 'erp' );
                }
            }

            if ( isset( $key['life_stage'] ) ) {
                if ( $key['life_stage'] ) {
                    $life_stages       = erp_crm_get_life_stages_dropdown_raw();
                    $key['life_stage'] = $life_stages[ $key['life_stage'] ];
                } else {
                    $key['life_stage'] = __( 'No Life Stage', 'erp' );
                }
            }

            if ( isset( $key['source'] ) ) {
                if ( $key['source'] && $key['source'] != '-1' ) {
                    $sources       = erp_crm_contact_sources();
                    $key['source'] = $sources[ $key['source'] ];
                } else {
                    $key['source'] = __( 'No Source', 'erp' );
                }
            }

            unset( $key['type'], $key['photo_id'] );
        } );

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => ucfirst( $type ),
            'changetype'    => 'edit',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s has been updated', 'erp' ), $name, $type ),
            'created_by'    => get_current_user_id(),
            'old_value'     => $changes['old_value'] ? base64_encode( maybe_serialize( $changes['old_value'] ) ) : '',
            'new_value'     => $changes['new_value'] ? base64_encode( maybe_serialize( $changes['new_value'] ) ) : '',
        ] );
    }

    /**
     * Add log when a customer deleted
     *
     * @since 1.7.2
     *
     * @param array $data
     * @param string $type
     * @param bool $hard
     *
     * @return void
     */
    public function delete_customer( $data, $type, $hard ) {
        $name   = $type === 'company' ? $data['company'] : $data['first_name'] . ' ' . $data['last_name'];
        $action = $hard ? 'deleted' : 'trashed';

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => ucfirst( $type ),
            'changetype'    => 'delete',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s has been %3$s', 'erp' ), $name, $type, $action ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when a customer restored
     *
     * @since 1.7.2
     *
     * @param array $data
     *
     * @return void
     */
    public function restore_customer( $data ) {
        $customer = new Contact( intval( $data['id'] ) );

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => ucfirst( $data['type'] ),
            'changetype'    => 'delete',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s has been restored from trash', 'erp' ), $customer->get_full_name(), $data['type'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }


    /**
     * Add log when new activity created
     *
     * @since 1.7.2
     *
     * @param array $data
     *
     * @return void
     */
    public function create_activity( $data ) {
        $title         = '';
        $invited_users = '';
        $contact       = new Contact( $data['user_id'] );

        if ( isset( $data['invite_contact'] ) ) {
            foreach ( $data['invite_contact'] as $user_id ) {
                $invited        = get_userdata( $user_id );
                $invited_name[] = $invited->display_name;
            }

            $invited_users = implode( ', ', $invited_name );
            $invited_users = sprintf( 'inviting <strong>%s</strong>', $invited_users );
        }

        switch ( $data['type'] ) {
            case 'schedule':
                $title = sprintf( 'titled <strong>%s</strong>', $data['schedule_title'] );
                break;
            case 'tasks':
                $title = sprintf( 'titled <strong>%s</strong>', $data['task_title'] );
                break;
        }

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => 'Customer Activity',
            'changetype'    => 'add',
            'message'       => sprintf(
                __( 'A <strong>%1$s</strong> %2$s has been created for <strong>%3$s</strong> %4$s', 'erp' ),
                $this->key_to_title( $data['type'] ),
                $title,
                $contact->get_full_name(),
                $invited_users
            ),
            'created_by'    => $data['created_by'],
        ] );
    }

    /**
     * Add log when a contact group created
     *
     * @since 1.7.2
     *
     * @param array $data
     *
     * @return void
     */
    public function create_contact_group( $data ) {
        $type = $data['private'] ? 'private' : 'public';

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => 'Contact Group',
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s contact group has been created', 'erp' ), $data['name'], $type ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when a contact group deleted
     *
     * @since 1.7.2
     *
     * @param array $data
     *
     * @return void
     */
    public function delete_contact_group( $data ) {
        $type = $data['private'] ? 'private' : 'public';

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => 'Contact Group',
            'changetype'    => 'delete',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s contact group has been deleted', 'erp' ), $data['name'], $type ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when a customer assigned to a contact group
     *
     * @since 1.7.2
     *
     * @param array $group_id
     * @param array $contact_id
     *
     * @return void
     */
    public function assign_contact_group( $group_id, $contact_id ) {
        $customer = new Contact( intval( $contact_id ) );
        $group    = erp_crm_get_contact_group_by_id( intval( $group_id ) );

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => 'Contact Group',
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> has been subscribed to <strong>%2$s</strong> contact group', 'erp' ), $customer->get_full_name(), $group['name'] ),
            'created_by'    => get_current_user_id(),
        ] );
    }

    /**
     * Add log when a customer assigned to a contact group
     *
     * @since 1.7.2
     *
     * @param string $type
     * @param int $assigned_to
     * @param int $assigned
     * @return void
     */
    public function assign_contact_company( $type, $assigned_to_id, $assigned_id ) {
        $assigned_to = new Contact( intval( $assigned_to_id ) );
        $assigned    = new Contact( intval( $assigned_id ) );

        if ( $type === 'assign_company' ) {
            $sub_component    = 'Contact';
            $assigned_to_type = 'contact';
            $assigned_type    = 'company';
        } else if ( $type === 'assign_customer' ) {
            $sub_component    = 'Company';
            $assigned_to_type = 'company';
            $assigned_type    = 'contact';
        }

        erp_log()->add( [
            'component'     => 'CRM',
            'sub_component' => $sub_component,
            'changetype'    => 'add',
            'message'       => sprintf( __( '<strong>%1$s</strong> %2$s has been assigned to <strong>%3$s</strong> %4$s', 'erp' ), $assigned->get_full_name(), $assigned_type, $assigned_to->get_full_name(), $assigned_to_type ),
            'created_by'    => get_current_user_id(),
        ] );
    }
}
