<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! function_exists( 'erp_ac_get_manager_role' ) ) {

    /**
     * The manager role for HR employees
     *
     * @return string
     */
    function erp_ac_get_manager_role() {
        return apply_filters( 'erp_ac_get_manager_role', 'erp_ac_manager' );
    }

    /**
     * When a new administrator is created, make him HR Manager by default
     *
     * @param int $user_id
     *
     * @return void
     */
    function erp_ac_new_admin_as_manager( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        if ( $user && in_array( 'administrator', $user->roles, true ) ) {
            $user->add_role( erp_ac_get_manager_role() );
        }
    }

    /**
     * Check is current user is accounting manager
     *
     * @since 1.8.5
     *
     * @return bool
     */
    function erp_ac_is_current_user_manager() {
        $current_user_role = erp_ac_get_user_role( get_current_user_id() );

        if ( erp_ac_get_manager_role() != $current_user_role ) {
            return false;
        }

        return true;
    }

    /**
     * Return a user's HR role
     *
     * @param int $user_id
     *
     * @return string
     */
    function erp_ac_get_user_role( $user_id = 0 ) {

        // Validate user id
        $user = get_userdata( $user_id );
        $role = false;

        // User has roles so look for a HR one
        if ( ! empty( $user->roles ) ) {

            // Look for a ac role
            $roles = array_intersect(
                array_values( $user->roles ),
                array_keys( erp_ac_get_roles() )
            );

            // If there's a role in the array, use the first one. This isn't very
            // smart, but since roles aren't exactly hierarchical, and HR
            // does not yet have a UI for multiple user roles, it's fine for now.
            if ( ! empty( $roles ) ) {
                $role = array_shift( $roles );
            }
        }

        return apply_filters( 'erp_ac_get_user_role', $role, $user_id, $user );
    }

    /**
     * Get dynamic roles for HR
     *
     * @return array
     */
    function erp_ac_get_roles() {
        $roles = [
            erp_ac_get_manager_role() => [
                'name'         => __( 'Accounting Manager', 'erp' ),
                'public'       => false,
                'capabilities' => erp_ac_get_caps_for_role( erp_ac_get_manager_role() ),
            ],
        ];

        return apply_filters( 'erp_ac_get_roles', $roles );
    }

    function erp_ac_get_caps_for_role( $role = '' ) {
        $caps = [];

        // Which role are we looking for?
        switch ( $role ) {

            case erp_ac_get_manager_role():
                $caps = [
                    'read'                            => true,
                    'erp_ac_view_dashboard'           => true,
                    'erp_ac_view_customer'            => true,
                    'erp_ac_view_single_customer'     => true,
                    'erp_ac_view_other_customers'     => true,
                    'erp_ac_create_customer'          => true,
                    'erp_ac_edit_customer'            => true,
                    'erp_ac_edit_other_customers'     => true,
                    'erp_ac_delete_customer'          => true,
                    'erp_ac_delete_other_customers'   => true,
                    'erp_ac_view_vendor'              => true,
                    'erp_ac_view_other_vendors'       => true,
                    'erp_ac_create_vendor'            => true,
                    'erp_ac_edit_vendor'              => true,
                    'erp_ac_edit_other_vendors'       => true,
                    'erp_ac_delete_vendor'            => true,
                    'erp_ac_delete_other_vendors'     => true,
                    'erp_ac_view_sale'                => true,
                    'erp_ac_view_single_vendor'       => true,
                    'erp_ac_view_other_sales'         => true,
                    'erp_ac_view_sales_summary'       => true,
                    'erp_ac_create_sales_payment'     => true,
                    'erp_ac_publish_sales_payment'    => true,
                    'erp_ac_create_sales_invoice'     => true,
                    'erp_ac_publish_sales_invoice'    => true,
                    'erp_ac_view_expense'             => true,
                    'erp_ac_view_other_expenses'      => true,
                    'erp_ac_view_expenses_summary'    => true,
                    'erp_ac_create_expenses_voucher'  => true,
                    'erp_ac_publish_expenses_voucher' => true,
                    'erp_ac_create_expenses_credit'   => true,
                    'erp_ac_publish_expenses_credit'  => true,
                    'erp_ac_view_account_lists'       => true,
                    'erp_ac_view_single_account'      => true,
                    'erp_ac_create_account'           => true,
                    'erp_ac_edit_account'             => true,
                    'erp_ac_delete_account'           => true,
                    'erp_ac_view_bank_accounts'       => true,
                    'erp_ac_create_bank_transfer'     => true,
                    'erp_ac_view_journal'             => true,
                    'erp_ac_view_other_journals'      => true,
                    'erp_ac_create_journal'           => true,
                    'erp_ac_view_reports'             => true,
                ];

                break;
        }

        return apply_filters( 'erp_ac_get_caps_for_role', $caps, $role );
    }

    function erp_acct_is_hr_current_user_manager() {
        $current_user_hr_role = erp_hr_get_user_role( get_current_user_id() );

        if ( erp_hr_get_manager_role() !== $current_user_hr_role ) {
            return false;
        }

        return true;
    }

    //Customer
    function erp_ac_create_customer() {
        return current_user_can( 'erp_ac_create_customer' );
    }

    function erp_ac_current_user_can_edit_customer( $created_by = false ) {
        if ( ! current_user_can( 'erp_ac_edit_customer' ) ) {
            return false;
        }

        if ( ! $created_by ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $created_by === $user_id ) {
            return true;
        }

        if ( current_user_can( 'erp_ac_edit_other_customers' ) ) {
            return true;
        }

        return false;
    }

    function erp_ac_current_user_can_view_single_customer() {
        return current_user_can( 'erp_ac_view_single_customer' );
    }

    function erp_ac_view_other_customers() {
        return current_user_can( 'erp_ac_view_other_customers' );
    }

    function erp_ac_current_user_can_delete_customer( $created_by = false ) {
        if ( ! current_user_can( 'erp_ac_delete_customer' ) ) {
            return false;
        }

        if ( ! $created_by ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $created_by === $user_id ) {
            return true;
        }

        if ( current_user_can( 'erp_ac_delete_other_customers' ) ) {
            return true;
        }

        return false;
    }

    //vendor
    function erp_ac_create_vendor() {
        return current_user_can( 'erp_ac_create_vendor' );
    }

    function erp_ac_current_user_can_edit_vendor( $created_by = false ) {
        if ( ! current_user_can( 'erp_ac_edit_vendor' ) ) {
            return false;
        }

        if ( ! $created_by ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $created_by === $user_id ) {
            return true;
        }

        if ( current_user_can( 'erp_ac_edit_other_vendors' ) ) {
            return true;
        }

        return false;
    }

    function erp_ac_current_user_can_view_single_vendor() {
        return current_user_can( 'erp_ac_view_single_vendor' );
    }

    function erp_ac_view_other_vendors() {
        return current_user_can( 'erp_ac_view_other_vendors' );
    }

    function erp_ac_current_user_can_delete_vendor( $created_by = false ) {
        if ( ! current_user_can( 'erp_ac_delete_vendor' ) ) {
            return false;
        }

        if ( ! $created_by ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $created_by === $user_id ) {
            return true;
        }

        if ( current_user_can( 'erp_ac_delete_other_vendors' ) ) {
            return true;
        }

        return false;
    }

    //sale
    function erp_ac_view_other_sales() {
        return current_user_can( 'erp_ac_view_other_sales' );
    }

    function erp_ac_view_sales_summary() {
        return current_user_can( 'erp_ac_view_sales_summary' );
    }

    function erp_ac_create_sales_payment() {
        return current_user_can( 'erp_ac_create_sales_payment' );
    }

    function erp_ac_publish_sales_payment() {
        return current_user_can( 'erp_ac_publish_sales_payment' );
    }

    function erp_ac_create_sales_invoice() {
        return current_user_can( 'erp_ac_create_sales_invoice' );
    }

    function erp_ac_publish_sales_invoice() {
        return current_user_can( 'erp_ac_publish_sales_invoice' );
    }

    /**
     * Check capability to view expenses created by other managers
     *
     * @return bool
     *
     * @since 1.2.0 Fix capability spelling
     * @since 1.0.0
     */
    function erp_ac_view_other_expenses() {
        return current_user_can( 'erp_ac_view_other_expenses' );
    }

    function erp_ac_view_expenses_summary() {
        return current_user_can( 'erp_ac_view_expenses_summary' );
    }

    function erp_ac_create_expenses_voucher() {
        return current_user_can( 'erp_ac_create_expenses_voucher' );
    }

    function erp_ac_publish_expenses_voucher() {
        return current_user_can( 'erp_ac_publish_expenses_voucher' );
    }

    function erp_ac_create_expenses_credit() {
        return current_user_can( 'erp_ac_create_expenses_credit' );
    }

    function erp_ac_publish_expenses_credit() {
        return current_user_can( 'erp_ac_publish_expenses_credit' );
    }

    //accounts
    function erp_ac_view_single_account() {
        return current_user_can( 'erp_ac_view_single_account' );
    }

    function erp_ac_create_account() {
        return current_user_can( 'erp_ac_create_account' );
    }

    function erp_ac_edit_account() {
        return current_user_can( 'erp_ac_edit_account' );
    }

    function erp_ac_delete_account() {
        return current_user_can( 'erp_ac_delete_account' );
    }

    //bank accounts
    function erp_ac_create_bank_transfer() {
        return current_user_can( 'erp_ac_create_bank_transfer' );
    }

    //journal
    function erp_ac_create_journal() {
        return current_user_can( 'erp_ac_create_journal' );
    }

    function erp_ac_view_other_journals() {
        return current_user_can( 'erp_ac_view_other_journals' );
    }
}

/**
 * Removes the non-public AC roles from the editable roles array
 *
 * @param array $all_roles All registered roles
 *
 * @return array
 */
function erp_ac_filter_editable_roles( $all_roles = [] ) {
    $roles = erp_ac_get_roles();

    foreach ( $roles as $ac_role_key => $ac_role ) {
        if ( isset( $ac_role['public'] ) && false === $ac_role['public'] ) {

            // Loop through WordPress roles
            foreach ( array_keys( $all_roles ) as $wp_role ) {

                // If keys match, unset
                if ( $wp_role === $ac_role_key ) {
                    unset( $all_roles[ $wp_role ] );
                }
            }
        }
    }

    return $all_roles;
}
