<?php

namespace WeDevs\ERP;

use Exception;
use Mailgun\Mailgun;
use Mailgun\Message\MessageBuilder;

/**
 * Mailgun Email Class
 *
 * Send Outgoing Emai for WP ERP
 *
 * @since 1.9.1
 */
class Email_Mailgun {

    /**
     * Private API Key
     *
     * @var $private_api_key
     */
    private $private_api_key;

    /**
     * Region Host Address
     *
     * @var $region
     */
    public $region;

    /**
     * Domain Name
     *
     * @var $domain
     */
    public $domain;

    /**
     * Mailgun Instance
     *
     * @var $mailgun instance
     */
    protected $mailgun;

    /**
     * Message Builder Instance
     *
     * @var $builder instance
     */
    protected $builder;

    /**
     * Class contsructor
     *
     * @param string $private_api_key   key-xxx
     * @param string $region            api.mailgun.net
     * @param string $domain            sandboxxxx.mailgun.org
     */
    public function __construct( $private_api_key, $region, $domain ) {
        $this->private_api_key = $private_api_key;
        $this->region          = $region;
        $this->domain          = $domain;

        $this->init();
    }

    /**
     * Init Mailgun Instance
     *
     * @since 1.9.1
     *
     * @return void
     */
    public function init() {
        $this->mailgun = Mailgun::create( $this->private_api_key, "https://$this->region" );
        $this->builder = new MessageBuilder();
    }

    /**
     * Set Email Subject
     *
     * @since 1.9.1
     *
     * @param string $subject
     *
     * @return void
     */
    public function set_subject( $subject ) {
        if ( ! empty( $subject ) ) {
            $subject = sanitize_text_field( wp_unslash( $subject ) );

            $this->builder->setSubject( $subject );
        }
    }

    /**
     * Set Email Message
     *
     * @since 1.9.1
     *
     * @param string $message
     *
     * @return void
     */
    public function set_message( $message ) {
        if ( ! empty( $message ) ) {
            $message = sanitize_text_field( wp_unslash( $message ) );

            $this->builder->setTextBody( $message );
        }
    }

    /**
     * Set Email From Address
     *
     * @since 1.9.1
     *
     * @param array $from address_array example; `[ 'email' => 'test@example.com', 'name' => 'Jhon Doe' ]`
     *
     * @return void
     */
    public function set_from_address( $from = [] ) {
        if ( ! empty( $from['email'] ) ) {
            $this->builder->setFromAddress(
                $from['email'],
                [
                    'first' => $from['name']
                ]
            );
        }
    }

    /**
     * Set Email To Address
     *
     * @since 1.9.1
     *
     * @param array $to address_array example; `[ 'email' => 'test@example.com', 'name' => 'Jhon Doe' ]`
     *
     * @return void
     */
    public function set_to_address( $to = [] ) {
        if ( ! empty( $to['email'] ) ) {
            $this->builder->addToRecipient(
                $to['email'],
                [
                    'first' => $to['name']
                ]
            );
        }
    }

    /**
     * Set Email CC Address
     *
     * @since 1.9.1
     *
     * @param array $cc address_array example; `[ 'email' => 'test@example.com', 'name' => 'Jhon Doe' ]`
     *
     * @return void
     */
    public function set_cc_address( $cc = [] ) {
        if ( ! empty( $cc['email'] ) ) {
            $this->builder->addCcRecipient(
                $cc['email'],
                [
                    'first' => $cc['name']
                ]
            );
        }
    }

    /**
     * Set Email Custom Headers
     *
     * @since 1.9.1
     *
     * @param array $header address_array example; `[ 'key' => 'Custom-ID', 'name' => '123456' ]`
     *
     * @return void
     */
    public function set_custom_headers( $header = [] ) {
        if ( ! empty( $header['key'] ) && ! empty( $header['value'] ) ) {
            $this->builder->addCustomHeader( $header['key'], $header['value'] );
        }
    }

    /**
     * Set Email Attachment
     *
     * @since 1.9.1
     *
     * @param string attachment file
     *
     * @return void
     */
    public function set_attachment( $attachment ) {
        if ( ! empty( $attachment ) ) {
            $this->builder->addAttachment( $attachment );
        }
    }

    /**
     * Make Message
     *
     * Build a mailgun message with the data
     *
     * @since 1.9.1
     *
     * @param array $args
     *
     * @todo Add all supported features of mailgun if missed any
     *
     * @return void
     */
    public function make_message( $args = [] ) {
        $default = [
            'subject'         => '',
            'from_address'    => ['email' => '', 'name'  => ''],
            'to_address'      => ['email' => '', 'name'  => ''],
            'cc_address'      => ['email' => '', 'name'  => ''],
            'customer_header' => ['key'   => '', 'value' => ''],
            'attachment'      => '',
            'message'         => ''
        ];

        $data = wp_parse_args( $args, $default );

        $this->set_subject( $data['subject'] );
        $this->set_from_address( $data['from_address'] );
        $this->set_to_address( $data['to_address'] );
        $this->set_cc_address( $data['cc_address'] );
        $this->set_custom_headers( $data['customer_header'] );
        $this->set_attachment( $data['attachment'] );
        $this->set_message( $data['message'] );
    }

    /**
     * Send a Mailgun Message
     *
     * @since 1.9.1
     *
     * @param array $data A complete dataset of the message
     *
     * @return mixed
     */
    public function send_email( $data = [] ) {
        try {
            $this->make_message( $data );

            return $this->mailgun->messages()->send(
                $this->domain,
                $this->builder->getMessage()
            );
        } catch ( Exception $e ) {
            throw new Exception( $e->getMessage() );
        }
    }
}
