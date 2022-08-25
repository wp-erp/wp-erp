<?php

namespace WeDevs\ERP_PRO\Feature\CRM\Life_Stages;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Traits\Ajax as Trait_Ajax;

/**
 * Ajax action hooks
 *
 * @since 1.0.1
 */
class Ajax {

    use Hooker;
    use Trait_Ajax;

    /**
     * The class constructor
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_erp_crm_add_life_stage', 'add_life_stage' );
        $this->action( 'wp_ajax_erp_crm_list_life_stages', 'list_life_stages' );
        $this->action( 'wp_ajax_erp_crm_update_life_stage', 'update_life_stage' );
        $this->action( 'wp_ajax_erp_crm_delete_life_stage', 'delete_life_stage' );
        $this->action( 'wp_ajax_erp_crm_update_life_stage_order', 'update_life_stage_order' );
    }

    /**
     * Lists all life stages
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function list_life_stages() {
        $this->verify_nonce( 'erp-life-stages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $life_stages = Helpers::get_life_stages();

        $this->send_success( $life_stages );
    }

    /**
     * Inserts life stages
     *
     * @since 1.0.1
     *
     * @param array $args
     *
     * @return void
     */
    public function add_life_stage() {
        global $wpdb;
        $args   = [];
        $errors = [];

        $this->verify_nonce( 'erp-life-stages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( [ 'general' => __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) ] );
        }

        $limit = apply_filters( 'erp_crm_limit_life_stage', 10 );

        if( Helpers::count_life_stages() >= $limit ) {
            $this->send_error( [ 'general' => sprintf( __( 'Maximum %d life stages can be added', 'erp-pro' ), $limit ) ] );
        }

        if ( ! empty( $_POST['title'] ) ) {
            $args['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
        } else {
            $errors['title'] = [ 'error' => __( 'Title cannot be empty', 'erp-pro' ) ];
        }

        if ( Helpers::exist_life_stage_title( $args['title'] ) ) {
            $errors['title'] = [ 'error' => __( 'Title already exists', 'erp-pro' ) ];
        }

        if ( ! empty( $_POST['title_plural'] ) ) {
            $args['title_plural'] = sanitize_text_field( wp_unslash( $_POST['title_plural'] ) );
        } else {
            $args['title_plural'] = $args['title'] . 's';
        }

        if ( Helpers::exist_life_stage_title( $args['title_plural'] ) ) {
            $errors['title_plural'] =  [ 'data' => $args['title_plural'], 'error' => __( 'Plural title already exists', 'erp-pro' ) ];
        }

        if ( ! empty( $_POST['slug'] ) ) {
            $args['slug'] = sanitize_title_with_dashes( wp_unslash( $_POST['slug'] ) );
        } else {
            $args['slug'] = sanitize_title_with_dashes( $args['title'] );
        }

        if ( ! empty( $args['slug'] ) && strlen( $args['slug'] ) > 255 ) {
            $args['slug'] = substr( $args['slug'], 0, 255 );
        }

        if ( Helpers::exist_life_stage_slug( $args['slug'] ) ) {
            $errors['slug'] = [ 'data' => $args['slug'], 'error' => __( 'Slug already exists', 'erp-pro' ) ];
        }

        if ( ! empty( $errors ) ) {
            $this->send_error( $errors );
        }

        $insert_id = Helpers::insert_life_stage( $args );

        if ( ! is_wp_error( $insert_id ) ) {
            $success =  [ 'insert' => $insert_id, 'message' => __( 'Life Stage Added', 'erp-pro' ) ];
            $this->send_success( $success );
        } else {
            $errors['general'] = $wpdb->last_error;
            $this->send_error( $errors );
        }
    }

    /**
     * Updates life stage
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function update_life_stage() {

        global $wpdb;
        $args   = [];
        $errors = [];

        $this->verify_nonce( 'erp-life-stages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        $id = intval( wp_unslash( $_POST['stage_id'] ) );

        if ( ! empty( $_POST['title'] ) ) {
            $args['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
        } else {
            $errors['title'] = [ 'error' => __( 'Title cannot be empty', 'erp-pro' ) ];
        }

        if ( Helpers::exist_life_stage_title( $args['title'], $id ) ) {
            $errors['title'] = [ 'error' => __( 'Title already exists', 'erp-pro' ) ];
        }

        if ( ! empty( $_POST['title_plural'] ) ) {
            $args['title_plural'] = sanitize_text_field( wp_unslash( $_POST['title_plural'] ) );
        } else {
            $title_plural = $args['title'] . 's';
        }

        if ( Helpers::exist_life_stage_title( $args['title_plural'], $id ) ) {
            $errors['title_plural'] =  [ 'data' => $args['title_plural'], 'error' => __( 'Plural title already exists', 'erp-pro' ) ];
        }

        if ( ! empty( $errors ) ) {
            $this->send_error( $errors );
        }

        $update_id = Helpers::update_life_stage( $id, $args );

        if ( ! is_wp_error( $update_id ) ) {
            $success =  [ 'update' => $update_id, 'message' => __( 'Life Stage Updated', 'erp-pro' ) ];
            $this->send_success( $success );
        } else {
            $errors['general'] = $wpdb->last_error;
            $this->send_error( $errors );
        }
    }

    /**
     * Updates life stage orders
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function update_life_stage_order() {
        global $wpdb;
        $updated = [];

        $this->verify_nonce( 'erp-life-stages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( $_POST['update'] ) {

            $orders = ! empty( $_POST['orders'] ) ? $_POST['orders'] : [];

            array_walk_recursive( $orders, function( &$value, $key ) {
                $value = intval( wp_unslash( $value ) );
            });

            foreach ( $orders as $order ) {
                $index        = $order[0];
                $new_order    = $order[1];
                $table        = $wpdb->prefix . 'erp_people_life_stages';
                $data         = [ 'position' => $new_order ];
                $where        = [ 'id'       => $index ];
                $data_format  = ['%d'];
                $where_format = ['%d'];

                $update_id    = $wpdb->update( $table, $data, $where, $data_format, $where_format );

                if ( ! is_wp_error( $update_id ) ) {
                    $updated[] = $update_id;
                } else {
                    $this->send_error( $wpdb->last_error );
                }
            }
        }

        $this->send_success( $updated );
    }

    /**
     * Deletes a life stage
     *
     * @since 1.0.1
     *
     * @return void
     */
    public function delete_life_stage() {

        global $wpdb;

        $this->verify_nonce( 'erp-life-stages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( __( 'You do not have sufficient permissions to do this action', 'erp-pro' ) );
        }

        if ( ! empty( $_POST['stage_id'] ) ) {
            $id = intval( wp_unslash( $_POST['stage_id'] ) );
        }

        if ( ! empty( $_POST['slug'] ) ) {
            $slug = sanitize_title_with_dashes( wp_unslash( $_POST['slug'] ) );
        }

        $delete_id = Helpers::delete_life_stage( $id, $slug );

        if ( ! is_wp_error( $delete_id ) ) {
            $this->send_success( [
                'delete' => $delete_id,
                'message' => __( 'Life Stage Deleted', 'erp-pro' )
            ] );
        } else {
            $errors['general'] = $wpdb->last_error;
            $this->send_error( $errors );
        }
    }
}
