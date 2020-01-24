<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;
use WeDevs\ERP\Framework\Traits\Ajax;

/**
 * ERP Subscription
 */
class Subscription {

    use Hooker;
    use Ajax;

    /**
     * Current subscription page action
     *
     * @since 1.1.17
     *
     * @var string
     */
    public $page_action = 'unsubscribe';

    /**
     * Subscribed contact groups
     *
     * @since 1.1.17
     *
     * @var array
     */
    public $subscribed_groups = [];

    /**
     * Unsubscribed contact groups
     *
     * @since 1.2.2
     *
     * @var array
     */
    public $unsubscribed_groups = [];

    /**
     * Subscription page id
     *
     * @since 1.1.17
     *
     * @var integer
     */
    public $sub_page_id = 0;

    /**
     * CRM people_id for the subscriber
     *
     * @since 1.2.2
     *
     * @var integer
     */
    public  $people_id = 0;

    /**
     * Subscriber hash that stored in erp_peoplemeta table
     *
     * @since 1.2.2
     *
     * @var string
     */
    public  $hash = '';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.1.16
     *
     * @return object Class instance
     */
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @since 1.1.17
     * @since 1.2.2  Add edit subscription ajax hooks
     *
     * @return void
     */
    public function __construct() {
        $erp_db_version = get_option( 'wp_erp_version' );

        if ( version_compare( $erp_db_version, '1.1.17', '<' ) ) {
            return;
        }

        // register widget
        $this->action( 'widgets_init', 'register_widget' );

        // frontend css and js
        $this->action( 'wp_enqueue_scripts', 'wp_enqueue_scripts' );

        // register shortcode
        add_shortcode( 'erp_subscription_form', [ $this, 'shortcode' ] );

        // handle the ajax submission
        $this->action( 'wp_ajax_erp_subscript_form_save_data', 'save_form_data' );
        $this->action( 'wp_ajax_nopriv_erp_subscript_form_save_data', 'save_form_data' );
        $this->action( 'wp_ajax_erp_subscript_edit_save_data', 'save_edit_form_data' );
        $this->action( 'wp_ajax_nopriv_erp_subscript_edit_save_data', 'save_edit_form_data' );

        // frontend subscription related page
        $this->action( 'pre_get_posts', 'subscription_page_frontend' );
    }

    public function register_widget() {
        register_widget( '\WeDevs\ERP\CRM\Subscription_Widget' );
    }

    /**
     * Frontend scripts related to subscription form
     *
     * @since 1.1.17
     *
     * @return void
     */
    public function wp_enqueue_scripts() {
        wp_enqueue_style( 'erp-subscription-form', WPERP_CRM_ASSETS . '/css/erp-subscription-form.css', [], WPERP_VERSION );

        $erp_subscription_form = [
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'erp-subscription-form' ),
        ];

        wp_enqueue_script( 'erp-subscription-form', WPERP_CRM_ASSETS . '/js/erp-subscription-form.js', [ 'jquery' ], WPERP_VERSION, true );
        wp_localize_script( 'erp-subscription-form', 'erpSubscriptionForm', $erp_subscription_form );
    }

    /**
     * Shortcode Renderer
     *
     * @since 1.1.17
     *
     * @param array $attrs
     *
     * @return void
     */
    public function shortcode( $attrs ) {
        if ( empty( $attrs ) ) {
            $attrs = [];
        }

        $args = [
            'group'      => isset( $attrs['group'] ) ? $attrs['group'] : null,
            'life_stage' => isset( $attrs['life_stage'] ) ? $attrs['life_stage'] : null,
            'button_lbl' => isset( $attrs['button'] ) ? $attrs['button'] : __( 'Subscribe', 'erp' ),
            'email_lbl'  => isset( $attrs['email'] ) ? $attrs['email'] : __( 'Email', 'erp' ),
            'extra_arg'  => isset( $attrs['extra_arg'] ) ? $attrs['extra_arg'] : null,
            //placeholder support
            'email_placeholder' => isset( $attrs['email_placeholder'] ) ? $attrs['email_placeholder'] : '',
            'first_name_placeholder' => isset( $attrs['first_name_placeholder'] ) ? $attrs['first_name_placeholder'] : '',
            'last_name_placeholder' => isset( $attrs['last_name_placeholder'] ) ? $attrs['last_name_placeholder'] : '',
            'full_name_placeholder' => isset( $attrs['full_name_placeholder'] ) ? $attrs['full_name_placeholder'] : '',
        ];

        if ( ! empty( $attrs['first_name'] ) ) {
            $args['first_name_lbl'] = $attrs['first_name'];

        } else if ( in_array( 'first_name', $attrs ) ) {
            $args['first_name_lbl'] = __( 'First Name', 'erp' );
        }

        if ( ! empty( $attrs['last_name'] ) ) {
            $args['last_name_lbl'] = $attrs['last_name'];

        } else if ( in_array( 'last_name', $attrs ) ) {
            $args['last_name_lbl'] = __( 'Last Name', 'erp' );
        }

        if ( ! empty( $attrs['full_name'] ) ) {
            $args['full_name_lbl'] = $attrs['full_name'];

        } else if ( in_array( 'full_name', $attrs ) ) {
            $args['full_name_lbl'] = __( 'Full Name', 'erp' );
        }

        ob_start();
        $this->subscription_form( $args );
        return ob_get_clean();
    }

    /**
     * Subscription Form
     *
     * @since 1.1.17
     *
     * @param array $args
     *
     * @return void
     */
    public function subscription_form( $args ) {
        if ( empty( $args['group'] ) ) {
            return new \WP_Error( 'erp_subs_form_no_group_found', __( 'Group attribute is required', 'erp' ) );
        }

        $groups         = is_array( $args['group'] ) ? $args['group'] : explode( ',', $args['group'] );
        $contact_groups = [];

        foreach ( $groups as $group_id ) {
            $group_id = absint( $group_id );

            $group = Models\ContactGroup::find( $group_id );

            if ( $group ) {
                $contact_groups[] = $group_id;
            }
        }

        if ( empty( $contact_groups ) ) {
            return;
        }

        $class_names = ['erp-subscription-form'];

        if ( empty( $args['full_name_lbl'] ) && empty( $args['first_name_lbl'] ) && empty( $args['last_name_lbl'] ) ) {
            $class_names[] = 'no-optional-field';

        } else {
            $class_names[] = 'has-optional-field';
        }

        $class_names = apply_filters( 'erp_subscription_form_class_names', $class_names, $args );

        $subscription_form_tmplt = apply_filters( 'erp_subscription_form_template', WPERP_CRM_VIEWS . '/subscription-form.php', $args );

        include $subscription_form_tmplt;
    }

    /**
     * Ajax handler to save subscription form data
     *
     * @since 1.1.17
     *
     * @return void
     */
    public function save_form_data() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-subscription-form' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        // validations
        if ( empty( $_POST['form_data'] ) ) {
            $this->send_error( [ 'msg' => __( 'Invalid operation', 'erp' ) ] );

        } else {
            parse_str( $_POST['form_data'], $form_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }


        if ( empty( $form_data['contact']['email'] ) || ! is_email( $form_data['contact']['email'] ) ) {
            $this->send_error( [ 'msg' => __( 'Please provide a valid email address', 'erp' ) ] );
        }

        if ( empty( $form_data['groups'] ) || ! is_array( $form_data['groups'] ) ) {
            $this->send_error( [ 'msg' => __( 'Invalid operation', 'erp' ) ] );
        }

        $success = $this->create_subsciber( $form_data );

        if ( is_wp_error( $success ) ) {
            $this->send_error( $success->get_error_message() );

        } else if ( 'already-subscribed' === $success ) {
            $this->send_success( [ 'msg' => __( 'You are already subscribed. Thank you!', 'erp' ) ] );
        }

        $success_msg = apply_filters( 'erp_subscription_form_success_message', __( 'Thank you! Your sign-up request was successful. Please check your email inbox to confirm.', 'erp' ) );

        $this->send_success( [ 'msg' => $success_msg ] );
    }

    /**
     * Create contact group subscriber
     *
     * Send confirmation mail if we have to
     *
     * @since 1.2.0
     * @since 1.2.1 Add `force_subscribe_to` option
     * @since 1.2.2 Always subscribe when the group is private
     *
     * @param array $args
     *
     * @return mixed WP_Error, true on success, 'already-subscribed' if subscriber already subscribed to provided group
     */
    public function create_subsciber( $args ) {
        $default_owner      = erp_crm_get_default_contact_owner();
        $default_life_stage = erp_get_option( 'life_stage', 'erp_settings_erp-crm_contacts', 'subscriber' );

        if ( ! empty( $args['life_stage'] ) ) {
            $registered_life_stages = erp_crm_get_life_stages_dropdown_raw();

            if ( ! array_key_exists( $args['life_stage'], $registered_life_stages ) ) {
                $this->send_error( [ 'msg' => __( 'Invalid operation', 'erp' ) ] );
            } else {
                $life_stage = $args['life_stage'];
            }

        } else {
            $life_stage = $default_life_stage;
        }

        $contact = [
            'type'       => 'contact',
            'email'      => $args['contact']['email'],
            'life_stage' => $life_stage,
        ];

        if ( ! empty( $args['contact']['full_name'] ) ) {
            $name_arr = explode( ' ', $args['contact']['full_name'] );

            if ( count( $name_arr ) > 1 ) {
                $contact['last_name']  = array_pop( $name_arr );
                $contact['first_name'] = implode( ' ' , $name_arr );

            } else {
                $contact['first_name'] = implode( ' ' , $name_arr );
            }

        } else if ( ! empty( $args['contact']['first_name'] ) ){
            $contact['first_name'] = $args['contact']['first_name'];
            $contact['last_name']  = isset( $args['contact']['last_name'] ) ? $args['contact']['last_name'] : '';
        }

        $contact = apply_filters( 'erp_subscription_form_save_form_data_args', $contact, $args );

        // check if any people exists with this email
        $existing_contact = erp_get_people_by( 'email', $contact['email'] );

        // for existing contact override the $_POST values with existing values
        if ( ! empty( $existing_contact ) )  {
            foreach ( $contact as $prop => $value ) {
                if ( ! empty( $existing_contact->$prop ) ) {
                    $contact[ $prop ] = $existing_contact->$prop;
                }
            }

            $contact['id'] = $existing_contact->id;
        }

        $contact_id = erp_insert_people( $contact );

        if ( is_wp_error( $contact_id ) ) {
            return new \WP_Error( 'error-insert-people', __( 'Unable to save data, please try again', 'erp' ) );
        }

        $contact = new \WeDevs\ERP\CRM\Contact( absint( $contact_id ), 'contact' );

        // insert metadata for new contact
        if ( empty( $existing_contact ) ) {
            $contact->update_meta( 'source', 'optin_form' );
            $contact->update_meta( 'contact_owner', $default_owner );
        }

        $is_double_optin_enabled = erp_get_option( 'is_enabled', 'erp_settings_erp-crm_subscription', 'yes' );
        $is_double_optin_enabled = filter_var( $is_double_optin_enabled, FILTER_VALIDATE_BOOLEAN );

        // subscribe to contact group
        $subscribed_groups = [];
        foreach ( $args['groups'] as $group_id ) {
            $contact_group = Models\ContactGroup::find( $group_id );

            if ( empty( $contact_group ) ) {
                continue;
            }

            $existing_subscriber = Models\ContactSubscriber::where( [
                'user_id'  => $contact_id,
                'group_id' => $group_id
            ] )->first();

            $hash = sha1( microtime() . 'erp-subscription-form' . $group_id . $contact_id );

            if ( empty( $existing_subscriber ) ) {
                $status = $is_double_optin_enabled ? 'unconfirmed' : 'subscribe';

                if ( isset( $args['force_subscribe_to'] ) && in_array( $group_id, $args['force_subscribe_to'] ) ) {
                    $status = 'subscribe';
                }

                if ( $contact_group->private ) {
                    $status = 'subscribe';
                }

                $subs_args = [
                    'group_id' => $group_id,
                    'user_id'  => $contact_id,
                    'status'   => $status,
                    'hash'     => $hash
                ];

                $subscribed_groups[] = erp_crm_create_new_contact_subscriber( $subs_args );

            } else {
                if ( ! $existing_subscriber->hash ) {
                    $existing_subscriber->hash = $hash;
                }
            }
        }

        if ( $is_double_optin_enabled && ! empty( $subscribed_groups ) ) {
            $this->send_mail( $contact, $subscribed_groups, $args );
        }

        // when contact is existing and already subscribed to every groups given in settings
        if ( $existing_contact && empty( $subscribed_groups ) ) {
            return 'already-subscribed';
        }

        do_action( 'erp_subscription_form_save_form_data', $contact, $subscribed_groups, $args );

        return true;
    }

    /**
     * Send confirmation mail to new subscribers
     *
     * @since 1.1.17
     * @since 1.2.2  Do not send mail when confirmation page is set up
     *
     * @param object $contact           \WeDevs\ERP\CRM\Contact object
     * @param array  $subscribed_groups Array of ContactSubscriber models
     * @param array  $form_data         Submitted form data
     *
     * @return void
     */
    private function send_mail( $contact, $subscribed_groups, $args ) {
        $groups = array_filter( $subscribed_groups, function ( $group ) {
            return ! ( 'subscribe' === $group->status );
        } );

        if ( empty( $groups ) ) {
            return;
        }

        $confirmation_page_url = $this->get_confirmation_page_url( $groups );

        if ( empty( $confirmation_page_url ) ) {
            return;
        }

        $subject_default = sprintf( __( 'Confirm your subscription to %s', 'erp' ), get_bloginfo( 'name' ) );
        $content_default = sprintf(
            __( "Hello!\n\nThanks so much for signing up for our newsletter.\nWe need you to activate your subscription to the list(s): [contact_groups_to_confirm] by clicking the link below: \n\n[activation_link]Click here to confirm your subscription.[/activation_link]\n\nThank you,\n\n%s", 'erp' ),
            get_bloginfo( 'name' )
        );

        $to      = $contact->data->email;
        $subject = erp_get_option( 'email_subject', 'erp_settings_erp-crm_subscription', $subject_default );
        $content = erp_get_option( 'email_content', 'erp_settings_erp-crm_subscription', $content_default );

        if ( preg_match( '/\[contact_groups_to_confirm\]/', $content ) ) {
            $group_names = array_map( function ( $group ) {
                return $group->groups->name;
            }, $groups );

            $group_names =  '<strong>' . implode( '</strong>, <strong>', $group_names ) . '</strong>';

            $content = preg_replace( '/\[contact_groups_to_confirm\]/', $group_names, $content );
        }

        if ( preg_match( '/\[activation_link\](.+?)\[\/activation_link\]/' , $content, $match ) ) {
            $anchor = '<a href="' . $confirmation_page_url . '">' . $match[1] . '</a>';

            $content = str_replace( $match[0], $anchor, $content );
        }

        $content = wpautop( $content, true );
        $content = apply_filters( 'erp_subscription_confirmation_mail_content', $content, $subscribed_groups, $args );

        erp_mail( $to, $subject, $content );
    }

    /**
     * Confirmation page URL
     *
     * @since 1.1.17
     * @since 1.2.2  Exclude hashes for the private groups
     *
     * @param array $groups Array of ContactSubscriber models
     *
     * @return string
     */
    public function get_confirmation_page_url( $groups ) {
        $page_id = erp_get_option( 'page_id', 'erp_settings_erp-crm_subscription', 0 );

        $url = get_permalink( $page_id );

        if ( ! $url ) {
            return '';
        }

        $groups = array_filter( $groups, function ( $group ) {
            return ! $group->groups->private;
        } );

        if ( ! empty( $groups ) ) {
            $hashes  = wp_list_pluck( $groups, 'hash' );

            $url .= '?erp-subscription-action=confirm&subscription-id=' . implode( ':', $hashes );

            return $url;
        }

        return '';
    }

    /**
     * Manage subscription and confirmation page
     *
     * $_GET['subscription-id'] could contain multiple hashes separated
     * by colon. Multiple hashes are required when an user confirms his/her
     * subscription to multiple groups triggered from a single erp subscription
     * form. In case of editing or managing subscription, a single hash is enough.
     *
     * subscription-id=SINGLEHASH
     * subscription-id=FIRSTHASH:SECONDHASH:THIRDHASH:ETC
     *
     * @since 1.1.17
     * @since 1.2.2  Add edit subscription page
     *
     * @param object $query
     *
     * @return void
     */
    public function subscription_page_frontend( $query ) {
        if ( $query->is_main_query() && ! empty( $_GET['erp-subscription-action'] ) ) {
            $page              = $query->get_queried_object();
            $this->sub_page_id = absint( erp_get_option( 'page_id', 'erp_settings_erp-crm_subscription', 0 ) );

            if ( ! is_object( $page ) ) {
                return;
            }

            if ( ! empty( $this->sub_page_id ) && absint( $page->ID ) === $this->sub_page_id ) {

                if ( ! empty( $_GET['subscription-id'] ) ) {
                    $subscription_ids = explode( ':', sanitize_text_field( wp_unslash( $_GET['subscription-id'] ) ) );

                    $this->subscribed_groups = Models\ContactSubscriber::whereIn( 'hash', $subscription_ids )->get();

                    if ( ! count( $this->subscribed_groups ) ) {
                        return;
                    }

                    $this->page_action = 'confirm';
                    $this->confirm_subscription();

                } else if ( ! empty( $_GET['id'] ) ) {
                    $meta = \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'meta_value', sanitize_text_field( wp_unslash( $_GET['id'] ) ) )
                                ->where( 'meta_key', 'hash' )
                                ->first();

                    if ( empty( $meta ) ) {
                        return;
                    }

                    $this->people_id = $meta->erp_people_id;
                    $this->hash      = $meta->meta_value;

                    switch ( $_GET['erp-subscription-action'] ) {
                        case 'edit':
                            $this->page_action = 'edit';
                            $erp_subscription_edit = [
                                'ajaxurl'  => admin_url( 'admin-ajax.php' ),
                                'nonce'    => wp_create_nonce( 'erp-subscription-edit' ),
                            ];

                            wp_enqueue_script( 'erp-subscription-edit', WPERP_CRM_ASSETS . '/js/erp-subscription-edit.js', [ 'jquery' ], WPERP_VERSION, true );
                            wp_localize_script( 'erp-subscription-edit', 'erpSubscriptionEdit', $erp_subscription_edit );
                            break;

                        case 'unsubscribe':
                        default:
                            $this->page_action = 'unsubscribe';
                            $this->unsubscribe_contact();
                            break;
                    }
                }

                $this->action( 'the_title', 'subscription_page_title', 10, 2 );
                $this->filter( 'the_content', 'subscription_page_content' );
            }
        }
    }

    /**
     * Confirm subscription
     *
     * @since 1.1.17
     * @since 1.2.3  Add hook after subscriber confirmation
     *
     * @return void
     */
    private function confirm_subscription() {
        foreach ( $this->subscribed_groups as $group ) {
            $group->status = 'subscribe';
            $group->subscribe_at    = $group->unsubscribe_at ? $group->unsubscribe_at : current_time( 'mysql' );
            $group->unsubscribe_at  = null;
            $group->save();

            do_action( 'erp_crm_edit_contact_subscriber', $group );
        }

        $people_id = $this->subscribed_groups->first()->user_id;

        $contact = new Contact( $people_id );
        if ( ! $contact->hash ) {
            $hash = sha1( microtime() . 'erp-confirm-subscription' . $people_id );
            $contact->update_contact_hash( $hash );
        }
    }

    /**
     * Unsubscribe contact
     *
     * URL stucture: http://example.com/ERP_SUBSCRIPTION_PG_SLUG/?erp-subscription-action=unsubscribe&id=PEOPLE_META_HASH&g=GRP_ID1:GRP_ID2:GRP_ID3
     *
     * @since 1.1.17
     * @since 1.2.2 Check for private group and use group ids instead of hashes
     *              Add `erp_subscription_unsubscribe` hook
     * @since 1.2.3 Check if $subscriber is empty or not before save its data
     *
     * @return void
     */
    private function unsubscribe_contact() {
        if ( ! empty( $_GET['g'] ) ) {
            $group_ids = explode( ':', sanitize_text_field( wp_unslash( $_GET['g'] ) ) );

            $groups = Models\ContactGroup::whereIn( 'id', $group_ids )->get();

            if ( $groups->count() ) {
                $groups->each( function ( $group ) {
                    if ( empty( $group->private ) ) {
                        $subscriber = $group->contact_subscriber()->where( 'user_id', $this->people_id )->first();

                        if ($subscriber) {
                            $subscriber->status          = 'unsubscribe';
                            $subscriber->subscribe_at    = null;
                            $subscriber->unsubscribe_at  = current_time( 'mysql' );
                            $subscriber->save();

                            $this->unsubscribed_groups[] = $group;
                        }
                    }
                } );
            }
        }

        do_action( 'erp_subscription_unsubscribe', $_GET, $this );
    }

    /**
     * Method to filter subscription page title
     *
     * @since 1.1.17
     * @since 1.2.2  Add edit subscription page
     *
     * @param string $title
     * @param int    $id
     *
     * @return string
     */
    public function subscription_page_title( $title, $id ) {
        if ( absint( $id ) !== $this->sub_page_id ) {
            return $title;
        }

        switch ( $this->page_action ) {
            case 'confirm':
                $title = erp_get_option( 'confirm_page_title', 'erp_settings_erp-crm_subscription', __( 'You are now subscribed!', 'erp' ) );
                break;

            case 'edit':
                $title = erp_get_option( 'edit_sub_page_title', 'erp_settings_erp-crm_subscription', __( 'Edit Your Subscription', 'erp' ) );
                break;

            case 'unsubscribe':
            default:
                $title = erp_get_option( 'unsubs_page_title', 'erp_settings_erp-crm_subscription', __( 'You are now unsubscribed', 'erp' ) );
                break;
        }

        return apply_filters( 'erp_subscription_page_title', $title, $this->page_action, $id, $this->subscribed_groups );
    }

    /**
     * Method to filter subscription page content
     *
     * @since 1.1.17
     * @since 1.2.2  Add edit subscription page
     *
     * @param string $content
     *
     * @return string
     */
    public function subscription_page_content( $content ) {
        global $post;

        if ( $post->ID !== $this->sub_page_id ) {
            return $content;
        }

        switch ( $this->page_action ) {
            case 'confirm':
                $content = erp_get_option( 'confirm_page_content', 'erp_settings_erp-crm_subscription', __( "We've added you to our email list. You'll hear from us shortly.", 'erp' ) );
                break;

            case 'edit':
                $page_content   = erp_get_option( 'edit_sub_page_content', 'erp_settings_erp-crm_subscription', __( 'Update your preferences', 'erp' ) );
                $template       = apply_filters( 'erp_subscription_edit_page_template', WPERP_CRM_VIEWS . '/edit-subscription-form.php' );
                $class_names    = apply_filters( 'erp_subscription_edit_page_template_class_names', ['erp-subscription-edit'] );
                $contact_lists  = $this->get_lists_subscriber_belongs_to( $this->people_id );
                $hash           = $this->hash;

                ob_start();
                include $template;
                $content = ob_get_clean();
                break;

            case 'unsubscribe':
            default:
                $content = erp_get_option( 'unsubs_page_content', 'erp_settings_erp-crm_subscription', __( 'You are successfully unsubscribed from list(s):', 'erp' ) );
                if ( ! empty( $this->unsubscribed_groups ) ) {
                    $group_names = [];

                    foreach ( $this->unsubscribed_groups as $unsubscribed_group ) {
                        $group_names[] = $unsubscribed_group->name;
                    }

                    $group_names = apply_filters( 'erp_subscription_unsubscribe_group_list', $group_names, $_GET, $this );

                    $content .= '<ul><li>' . implode( '</li><li>', $group_names ) . '</li></ul>';
                }

                break;
        }

        return apply_filters( 'erp_subscription_page_content', $content, $this->page_action, $this->subscribed_groups, $this->unsubscribed_groups );
    }

    /**
     * Get lists that a subscriber belongs to
     *
     * @since 1.2.2
     *
     * @param integer $people_id
     *
     * @return object Eloquent collection object of related models
     */
    public function get_lists_subscriber_belongs_to( $people_id, $with_private = false ) {
        $lists = [
            'contact_group' => Models\ContactGroup::getGroupSubscriber( $people_id, $with_private )->get()
        ];

        return apply_filters( 'erp_subscription_lists_subscriber_belongs_to', $lists, $with_private, $this );
    }


    /**
     * Save edit subscription page data
     *
     * @since 1.2.2
     *
     * @return void
     */
    public function save_edit_form_data() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-subscription-edit' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
        }

        // validations
        if ( empty( $_POST['form_data'] ) ) {
            $this->send_error( [ 'msg' => __( 'Invalid operation', 'erp' ) ] );

        } else {
            parse_str( sanitize_text_field( wp_unslash( $_POST['form_data'] ) ), $form_data );
        }

        if ( empty( $form_data['id'] ) ) {
            $this->send_error( [ 'msg' => __( 'Invalid subscriber', 'erp' ) ] );
        }

        $meta = \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'meta_value', $form_data['id'] )
                    ->where( 'meta_key', 'hash' )
                    ->first();

        if ( empty( $meta ) ) {
            $this->send_error( [ 'msg' => __( 'Subscriber does not exists', 'erp' ) ] );
        }

        $this->people_id = $meta->erp_people_id;
        $this->hash      = $meta->meta_value;

        $contact_lists = $this->get_lists_subscriber_belongs_to( $this->people_id, true );

        $contact_groups = $contact_lists['contact_group'];
        $subscribed_groups = [];

        $contact_groups->each( function ( $contact_group ) use ( $form_data, &$subscribed_groups ) {
            if ( $contact_group->private || ! empty( $form_data['contact_group'][ $contact_group->id ] ) ) {
                $subscribed_groups[] = $contact_group->id;
            }
        } );

        erp_crm_edit_contact_subscriber( $subscribed_groups, $this->people_id );

        do_action( 'erp_subscription_edit', $form_data, $contact_lists, $this );

        $success_msg = apply_filters( 'erp_subscription_edit_success_message', __( 'Thank you! Your subscription preference has been updated.', 'erp' ) );

        $this->send_success( [ 'msg' => $success_msg ] );
    }

}
