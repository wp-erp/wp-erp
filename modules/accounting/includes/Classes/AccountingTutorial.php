<?php

namespace WeDevs\ERP\Accounting\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AccountingTutorial Class
 *
 * Make a Quick tour tutorial to demonstrate accounting module
 *
 * @since 1.9.0
 */
class AccountingTutorial {

    /**
     * Default URL for accounting module tutorial
     */
    public $default_url;

    /**
     * Processed tutorial mode URL for accounting module tutorial
     */
    public $base_url;

    /**
     * Admin URL of default URL
     */
    public $default_admin_url;

    /**
     * Constructor
     */
	public function __construct() {
        $this->init_default_setup();

        add_action( 'admin_enqueue_scripts', [ $this, 'setup_pointers_for_screen' ] );
	}

    /**
	 * Initializaiton of URL's
     *
     * @since 1.9.0
     *
     * @return void
	 */
    public function init_default_setup() {
        $this->default_url       = 'admin.php?page=erp-accounting';
        $this->base_url          = "$this->default_url&tutorial=true&";
        $this->default_admin_url = 'admin.php?page=erp-accounting';
    }

	/**
	 * Setup pointers for screen.
     *
     * @since 1.9.0
     *
     * @return void
	 */
	public function setup_pointers_for_screen() {
		if ( ! $screen = get_current_screen() ) {
            return;
		}

		if ( 'wp-erp_page_erp-accounting' === $screen->id ) {
			$tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard';

            wp_enqueue_style( 'wp-pointer' );
            wp_enqueue_script( 'wp-pointer' );

            $this->get_tutorial_pointer( $tab );
		}
	}

	/**
     * Get tutorial pointer
     *
     * Get any single tabs tutorial pointer and enqueue it using wp_pointer
     *
     * @since 1.9.0
     *
     * @param string $tab Tabs will be used as like a single page. eg; dashboard, customer
     *
     * @return void
     */
	public function get_tutorial_pointer( $tab = 'dashboard' ) {
        $pointers     = [];
        $tab_pointers = [];

        switch ( $tab ) {
            case 'dashboard':
                $tab_pointers = [
                    'dashboard' => [
                        'target'       => '#erp-accounting #btn-tutorial-start',
                        'next'         => '',  // If you want to make same page
                        'next_url'     => admin_url( $this->base_url . 'tab=customer#users' ),  // If you want to make pointer in another page
                        'next_trigger' => [],
                        'options'      => [
                            'content'  => '<h3>' . esc_html__( 'Accounting Tutorial', 'erp' ) . '</h3>' .
                                        '<p>' . esc_html__( 'To make it easy to understand, we\'ve added step by step guides on how to get started with the Accounting system. Press Next to start', 'erp' ) . '</p>',
                            'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                        ]
                    ]
                ];
                break;

            case 'customer':
                $tab_pointers = [
                    'customer' => [
                        'target'       => '#erp-accounting #erp-customer-new',
                        'next'         => '',
                        'next_url'     => admin_url( $this->base_url . 'tab=vendor#users/vendors' ),
                        'next_trigger' => [],
                        'options'      => [
                            'content'  => '<h3>' . esc_html__( 'Add Customers', 'erp' ) . '</h3>' .
                                            '<p>' . esc_html__( 'Create your customers profile before creating an invoice for them. You can also get the customer list here from the CRM directly if you have enabled sync from the Settings.', 'erp' ) . '</p>',
                            'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                        ],
                    ]
                ];
                break;

            case 'vendor':
                $tab_pointers = [
                    'vendor' => [
                        'target'       => '#erp-accounting #erp-customer-new',
                        'next'         => '',
                        'next_url'     => admin_url( $this->base_url . 'tab=tax-rates#settings/taxes/tax-rates' ),
                        'next_trigger' => [],
                        'options'      => [
                            'content'  => '<h3>' . esc_html__( 'Add Vendors', 'erp' ) . '</h3>' .
                                            '<p>' . esc_html__( 'Create a vendor profile before creating a purchase from them. Vendors are required before creating a purchase or purchase order.', 'erp' ) . '</p>',
                            'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                        ],
                    ]
                ];
                break;

            case 'tax-rates':
                    $tab_pointers = [
                        'tax_rate_menu' => [
                            'target'       => '#erp-accounting .tax-section',
                            'next'         => 'tax_rate',
                            'next_url'     => '',
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Configuring Tax', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'Creating Tax Zones, Tax Category and agency are required before adding Tax Rates', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'right', 'align' => 'left' ]
                            ],
                        ],
                        'tax_rate' => [
                            'target'       => '#erp-accounting #add-tax-rate',
                            'next'         => '',
                            'next_url'     => admin_url( $this->base_url . 'tab=product#products/product-service' ),
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Configuring Tax', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'After adding Tax Zones, Tax Category and Agency, you need to set the Tax Rates based on your country\'s Tax policy.', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ]
                    ];
                    break;

                case 'product':
                    $tab_pointers = [
                        'product' => [
                            'target'       => '#erp-accounting #erp-product-new',
                            'next'         => '',
                            'next_url'     => admin_url( $this->base_url . 'tab=chart_of_account#settings/charts' ),
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Add Products or Service', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'Create the product or service that you sell having product or service is  required before creating transactions.', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ]
                    ];
                    break;

                case 'chart_of_account':
                    $tab_pointers = [
                        'chart_of_account' => [
                            'target'       => '#erp-accounting #erp-add-chart-of-account',
                            'next'         => '',
                            'next_url'     => admin_url( $this->base_url . 'tab=opening_balance#opening-balance' ),
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Add Bank Accounts', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'Add your bank account(s) where you want to deposit your sales amounts and from where you want to pay for the purchases.', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ]
                    ];
                    break;

                case 'opening_balance':
                    $tab_pointers = [
                        'opening_balance' => [
                            'target'       => '#wp-admin-bar-wp-erp-acct',
                            'next'         => 'transaction',
                            'next_url'     => '',
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Add Opening Balance', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'Add your business opening balance & add initial balance in your Bank accounts.', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ],
                        'transaction' => [
                            'target'       => '#erp-accounting #erp-act-menu-transactions',
                            'next'         => '',
                            'next_url'     => admin_url( $this->base_url . 'tab=journal#transactions/journals' ),
                            'next_trigger' => [],
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Add Transactions', 'erp' ) . '</h3>' .
                                                '<p>' . esc_html__( 'Now you are ready to have your transactions including sales, purchase and expenses.', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ]
                    ];
                    break;


                case 'journal':
                    $tab_pointers = [
                        'journal' => [
                            'target'       => '#erp-accounting .erp-journal-new',
                            'next'         => '',
                            'next_url'     => admin_url( $this->default_url ),
                            'last_step'    => true,
                            'next_trigger' => [],
                            'last_step'    => true,
                            'options'      => [
                                'content'  => '<h3>' . esc_html__( 'Journal Entry', 'erp' ) . '</h3>' .
                                                '<p>' . __( 'Journal entry is available under the Transactions as well. <br /><br />You are done and best of luck!', 'erp' ) . '</p>',
                                'position' => [ 'edge'  => 'top', 'align' => 'left' ]
                            ],
                        ]
                    ];
                    break;

            default:
                break;
        }

        $pointers = [
            'pointers' => $tab_pointers
        ];

        $this->enqueue_pointers( $pointers );
	}

	/**
	 * Enqueue pointers and add script to that page.
     *
	 * @since 1.9.0
     *
	 * @param array $pointers
     *
     * @return void
	 */
	public function enqueue_pointers( $pointers ) {
		$pointers = rawurlencode( wp_json_encode( $pointers ) );

		erp_enqueue_js("jQuery( function( $ ) {
				var erp_pointers = JSON.parse( decodeURIComponent( '{$pointers}' ) );
				setTimeout( init_erp_pointers, 800 );

				function init_erp_pointers() {
					$.each( erp_pointers.pointers, function( i ) {
						show_erp_pointer( i );
						return false;
					});
				}

				function show_erp_pointer( id ) {
					var pointer = erp_pointers.pointers[ id ];
					var options = $.extend( pointer.options, {
						pointerClass: 'wp-pointer erp-pointer',
						next_url: '',
						close: function() {
							if ( pointer.hasOwnProperty( 'next_url' ) && pointer.next_url.length ) {
								window.location.href = pointer.next_url;
							} else if ( pointer.next ) {
								show_erp_pointer( pointer.next );
							}
						},
						open: function( e, t ) {
							t.pointer.get(0).scrollIntoView( { behavior: 'smooth', marginTop: 50 } );
						},
						buttons: function( event, t ) {
							var close   = '" . esc_js( __( 'Dismiss', 'erp' ) ) . "',
								next    = '" . esc_js( __( 'Next', 'erp' ) ) . "',
								button  = $( '<a class=\"close\" href=\"{$this->default_admin_url}\">' + close + '</a>' ),
								button2 = $( '<a class=\"button button-primary\" href=\"#\">' + next + '</a>' ),
								wrapper = $( '<div class=\"erp-pointer-buttons\" />' ),
								nextUrl = '';
							if ( pointer.hasOwnProperty( 'last_step' ) && pointer.last_step ) {
								next    = '" . esc_js( __( 'Complete & Close Tutorial', 'erp' ) ) . "';
							}
							if ( pointer.hasOwnProperty( 'next_url' ) && pointer.next_url.length ) {
								nextUrl = pointer.next_url;
								button2 = $( '<a class=\"button button-primary\" href=\"' + pointer.next_url + '\">' + next + '</a>' );
							}
							button2.bind( 'click.pointer', function(e) {
								e.preventDefault();
								t.element.pointer('close');
							});
							wrapper.append( button );
							if ( pointer.next.length || nextUrl.length ) {
								wrapper.append( button2 );
							}
							return wrapper;
						},
					} );

					var this_pointer = $( pointer.target ).pointer( options );
					this_pointer.pointer( 'open' );

					if ( pointer.next_trigger ) {
						$( pointer.next_trigger.target ).on( pointer.next_trigger.event, function() {
							setTimeout( function() { this_pointer.pointer( 'close' ); }, 400 );
						});
					}
				}
			});"
        );
	}
}

new AccountingTutorial();
