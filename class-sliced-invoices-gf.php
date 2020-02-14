<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

GFForms::include_feed_addon_framework();

class Sliced_Invoices_GF extends GFFeedAddOn {

	protected $_version = '1.12.5';
	protected $_min_gravityforms_version = '1.9.10';
	protected $_slug = 'slicedinvoices';
	protected $_path = 'sliced-invoices-gravity-forms/sliced-invoices-gravity-forms.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Sliced Invoices';
	protected $_short_title = 'Sliced Invoices';
	
	protected $_capabilities_settings_page = 'gravityforms_slicedinvoices';
	protected $_capabilities_form_settings = 'gravityforms_slicedinvoices';
	protected $_capabilities_uninstall = 'gravityforms_slicedinvoices_uninstall';
	protected $_capabilities = array( 'gravityforms_slicedinvoices', 'gravityforms_slicedinvoices_uninstall' );

	private static $_instance = null;

	private $_select_choices = array();

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'sliced-line-items',
				'src'     => $this->get_base_url() . '/includes/js/line-items.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array( array( 'field_types' => array( 'list' ) ) ),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}
	
	public function init() {
		parent::init();

		add_action( 'gform_field_css_class', array( $this, 'add_custom_class' ), 10, 3 );
		add_filter( 'gform_field_value_sliced_line_items', array( $this, 'sliced_populate_list_with_line_items' ), 10, 2 );
		add_action( 'sliced_client_head', array( $this, 'sliced_add_gravity_js_to_client_area' ) );
	}

	/**
	 * Add the custom class to the List field.
	 *
	 * @param string $classes The CSS classes to be filtered, separated by empty spaces.
	 * @param GF_Field $field The field currently being processed.
	 * @param array $form The form currently being processed.
	 *
	 * @return string
	 */
	public function add_custom_class( $classes, $field, $form ) {
		if ( $field->get_input_type() == 'list' && $field->inputName == 'sliced_line_items' ) {
			$classes .= ' sliced_line_items ';
		}

		return $classes;
	}

	public function sliced_populate_list_with_line_items( $value, $field ) {
		if ( $field->get_input_type() == 'list' ) {

			$options     = get_option( 'sliced_general' );
			$pre_defined = $options['pre_defined'];

			$items = explode( "\n", $pre_defined );
			$items = array_filter( $items ); // remove any empty items

			$value   = array();
			$choices = array();

			if ( is_array( $items ) ) {
				foreach ( $items as $item ) {
					$row_values = array_map( 'trim', explode( '|', $item ) );
					$value      = array_merge( $value, $row_values );
					$choices[]  = array( 'text' => esc_html( rgar( $row_values, 1 ) ), 'value' => esc_html( $item ) );
				}
			}

			$this->_select_choices = $choices;

			$form_id = absint( $field->formId );
			add_filter( 'gform_column_input_' . $form_id . '_' . $field->id . '_2', array( $this, 'sliced_change_column2_content' ), 10, 6 );
		}

		return $value;
	}

	public function sliced_change_column2_content( $input, $input_info, $field, $text, $value, $form_id ) {
		return array(
			'type'    => 'select',
			'choices' => $this->_select_choices,
		);
	}

	/**
	 * Add Gravity forms js to client area (if exists).
	 *
	 * @since 1.02
	 */
	function sliced_add_gravity_js_to_client_area() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$client_area = is_plugin_active( 'sliced-invoices-client-area/sliced-invoices-client-area.php' );

		if ( ! $client_area ) {
			return;
		}

		wp_print_scripts( 'gform_gravityforms' );

	}

	/**
	 * Settings fields
	 * @since  1.0.0
	 */
	public function feed_settings_fields() {
		
		$translate = get_option( 'sliced_translate' );
	
		$quote_status_options = array(
			array(
				'label' => ( ( isset( $translate['draft'] ) && class_exists( 'Sliced_Translate' ) ) ? $translate['draft'] : __( 'Draft', 'sliced-invoices' ) ) . ' ' . __( '(default)', 'sliced-invoices-gravity-forms' ),
				'value' => 'draft',
			),
		);
		$quote_statuses = get_terms( 'quote_status', array( 'hide_empty' => 0 ) );
		foreach ( $quote_statuses as $quote_status ) {
			if ( $quote_status->slug === 'draft' ) { continue; }
			$quote_status_options[] = array(
				'label' => ( ( isset( $translate[$quote_status->slug] ) && class_exists( 'Sliced_Translate' ) ) ? $translate[$quote_status->slug] : __( ucfirst( $quote_status->name ), 'sliced-invoices' ) ),
				'value' => esc_attr( $quote_status->slug ),
			);
		}
		
		$invoice_status_options = array(
			array(
				'label' => ( ( isset( $translate['draft'] ) && class_exists( 'Sliced_Translate' ) ) ? $translate['draft'] : __( 'Draft', 'sliced-invoices' ) ) . ' ' . __( '(default)', 'sliced-invoices-gravity-forms' ),
				'value' => 'draft',
			),
		);
		$invoice_statuses = get_terms( 'invoice_status', array( 'hide_empty' => 0 ) );
		foreach ( $invoice_statuses as $invoice_status ) {
			if ( $invoice_status->slug === 'draft' ) { continue; }
			$invoice_status_options[] = array(
				'label' => ( ( isset( $translate[$invoice_status->slug] ) && class_exists( 'Sliced_Translate' ) ) ? $translate[$invoice_status->slug] : __( ucfirst( $invoice_status->name ), 'sliced-invoices' ) ),
				'value' => esc_attr( $invoice_status->slug ),
			);
		}
		
		return array(
			array(
				'title'  => __( 'Configure your feed settings', 'sliced-invoices-gravity-forms' ),
				'fields' => array(
					array(
						'label'   => __( 'Feed Name', 'sliced-invoices-gravity-forms' ),
						'type'    => 'text',
						'name'    => 'feedName',
						'tooltip' => __( 'This can be anything, such as Quote Request Feed', 'sliced-invoices-gravity-forms' ),
						'class'   => 'small'
					),
					array(
						'label'      => __( 'Type', 'sliced-invoices-gravity-forms' ),
						'type'       => 'radio',
						'name'       => 'post_type',
						'horizontal' => true,
						'tooltip'    => __( 'Choose whether to create a Quote or an Invoice', 'sliced-invoices-gravity-forms' ),
						'choices'    => array(
							array(
								'label' => sliced_get_quote_label(),
								'value' => 'quote',
							),
							array(
								'label' => sliced_get_invoice_label(),
								'value' => 'invoice',
							),
						),
						'onchange' => 'jQuery(this).parents("form").submit();',
					),
					array(
						'name'      => 'mappedFields',
						'label'     => __( 'Map Fields', 'sliced-invoices-gravity-forms' ),
						'type'      => 'field_map',
						'dependency' => 'post_type',
						'field_map' => array(
							array(
								'name'     => 'name',
								'label'    => __( 'Client Name', 'sliced-invoices-gravity-forms' ),
								'required' => 1
							),
							array(
								'name'     => 'email',
								'label'    => __( 'Client Email', 'sliced-invoices-gravity-forms' ),
								'required' => 1,
								'tooltip'  => sprintf( __( 'If the form is submitted using an email address that already exists in your WP Users, then for security reasons any user details including the Client Name, Business Name, Address, and Extra Info will NOT be updated. <a href="%s" target="_blank">See FAQ</a>', 'sliced-invoices-gravity-forms' ), 'https://slicedinvoices.com/question/client-namebusiness-nameaddressextra-info-fields-not-updating-form-submitted/' ),
							),
							array(
								'name'     => 'business',
								'label'    => __( 'Business Name', 'sliced-invoices-gravity-forms' ),
								'required' => 0
							),
							array(
								'name'     => 'address',
								'label'    => __( 'Business Address', 'sliced-invoices-gravity-forms' ),
								'required' => 0
							),
							array(
								'name'     => 'extra_info',
								'label'    => __( 'Extra Info', 'sliced-invoices-gravity-forms' ),
								'required' => 0
							),
							array(
								'name'     => 'title',
								'label'    => __( 'Title', 'sliced-invoices-gravity-forms' ),
								'required' => 1
							),
							array(
								'name'     => 'description',
								'label'    => __( 'Description', 'sliced-invoices-gravity-forms' ),
								'required' => 0
							),
							array(
								'name'     => 'line_items',
								'label'    => __( 'Line Items', 'sliced-invoices-gravity-forms' ),
								'required' => 0,
								'tooltip'  => __( 'This must map to a "List" field in your form.  If you are using "Product" fields instead, leave this setting blank and check the "Use Product field(s) for Line Items" box below.', 'sliced-invoices-gravity-forms' ),
							),
							array(
								'name'       => 'quote_number',
								'label'      => __( 'Quote Number', 'sliced-invoices-gravity-forms' ),
								'required'   => 0,
								'dependency' => array(
									'field'  => 'post_type',
									'values' => 'quote',
								)
							),
							array(
								'name'       => 'invoice_number',
								'label'      => __( 'Invoice Number', 'sliced-invoices-gravity-forms' ),
								'required'   => 0,
								'dependency' => array(
									'field'  => 'post_type',
									'values' => 'invoice',
								)
							),
							array(
								'name'       => 'order_number',
								'label'      => __( 'Order Number', 'sliced-invoices-gravity-forms' ),
								'required'   => 0,
								'dependency' => array(
									'field'  => 'post_type',
									'values' => 'invoice',
								)
							),
						)
					),
					array(
						'label'     => __( 'Use Product field(s) for Line Items', 'sliced-invoices-gravity-forms' ),
						'type'      => 'checkbox',
						'name'      => 'product_fields',
						'choices'   => array(
							array(
								'label'         => '',
								'name'          => 'use_product_fields',
								'default_value' => 0,
								'tooltip'   => __( 'If this box is checked, Sliced Invoices will search your form for any Product fields and create line items from them.  This will override anything else in your "Line Items" setting above.', 'sliced-invoices-gravity-forms' ),
							),
						),
					),
					array(
						'label'      => __( sprintf( 'Set %s status', sliced_get_quote_label() ), 'sliced-invoices-gravity-forms' ),
						'type'       => 'select',
						'name'       => 'set_quote_status',
						'horizontal' => true,
						'choices'    => $quote_status_options,
						'dependency' => array(
							'field'  => 'post_type',
							'values' => 'quote',
						),
					),
					array(
						'label'      => __( sprintf( 'Set %s status', sliced_get_invoice_label() ), 'sliced-invoices-gravity-forms' ),
						'type'       => 'select',
						'name'       => 'set_invoice_status',
						'horizontal' => true,
						'choices'    => $invoice_status_options,
						'dependency' => array(
							'field'  => 'post_type',
							'values' => 'invoice',
						),
					),
					array(
						'label'      => __( 'Send to client?', 'sliced-invoices-gravity-forms' ),
						'type'       => 'radio',
						'name'       => 'send_to_client',
						'horizontal' => true,
						'tooltip'    => __( sprintf( 'If set to "Yes", the %s/%s will automatically be emailed to the client as soon as it is created. (Default is "No")', sliced_get_quote_label(), sliced_get_invoice_label() ), 'sliced-invoices-gravity-forms' ),
						'default_value' => 'no',
						'choices'    => array(
							array(
								'label' => __( 'Yes', 'sliced-invoices-gravity-forms' ),
								'value' => 'yes',
							),
							array(
								'label' => __( 'No', 'sliced-invoices-gravity-forms' ),
								'value' => 'no',
							),
						),
					),
					array(
						'name'           => 'condition',
						'label'          => __( 'Condition', 'sliced-invoices-gravity-forms' ),
						'type'           => 'feed_condition',
						'checkbox_label' => __( 'Enable Condition', 'sliced-invoices-gravity-forms' ),
						'instructions'   => __( 'Process this feed if ', 'sliced-invoices-gravity-forms' ),
						'dependency'     => 'post_type',
					),
				)
			)
		);
	}


	/**
	 * Columns on feed setup
	 * @since  1.0.0
	 */
	public function feed_list_columns() {
		return array(
			'feedName'  => __( 'Name', 'sliced-invoices-gravity-forms' ),
			'post_type' => __( 'Post Type', 'sliced-invoices-gravity-forms' )
		);
	}

	// customize the value of post_type before it's rendered to the list
	public function get_column_value_mytextbox( $feed ) {
		return '<b>' . $feed['meta']['post_type'] . '</b>';
	}


	/**
	 * Process the data coming from the form
	 * @since  1.0.0
	 */
	public function process_feed( $feed, $entry, $form ) {

		$post_type          = strtolower( $feed['meta']['post_type'] );
		$mapped_email       = isset( $feed['meta']['mappedFields_email'] )          ? $feed['meta']['mappedFields_email']          : false;
		$mapped_name        = isset( $feed['meta']['mappedFields_name'] )           ? $feed['meta']['mappedFields_name']           : false;
		$mapped_business    = isset( $feed['meta']['mappedFields_business'] )       ? $feed['meta']['mappedFields_business']       : false;
		$mapped_address     = isset( $feed['meta']['mappedFields_address'] )        ? $feed['meta']['mappedFields_address']        : false;
		$mapped_extra       = isset( $feed['meta']['mappedFields_extra_info'] )     ? $feed['meta']['mappedFields_extra_info']     : false;
		$mapped_title       = isset( $feed['meta']['mappedFields_title'] )          ? $feed['meta']['mappedFields_title']          : false;
		$mapped_desc        = isset( $feed['meta']['mappedFields_description'] )    ? $feed['meta']['mappedFields_description']    : false;
		$mapped_quote_num   = isset( $feed['meta']['mappedFields_quote_number'] )   ? $feed['meta']['mappedFields_quote_number']   : false;
		$mapped_inv_num     = isset( $feed['meta']['mappedFields_invoice_number'] ) ? $feed['meta']['mappedFields_invoice_number'] : false;
		$mapped_order_num   = isset( $feed['meta']['mappedFields_order_number'] )   ? $feed['meta']['mappedFields_order_number']   : false;
		$mapped_line_items  = isset( $feed['meta']['mappedFields_line_items'] )     ? $feed['meta']['mappedFields_line_items']     : false;
		$use_product_fields = isset( $feed['meta']['use_product_fields'] )          ? $feed['meta']['use_product_fields']          : false;
		$set_quote_status   = isset( $feed['meta']['set_quote_status'] )            ? $feed['meta']['set_quote_status']            : false;
		$set_invoice_status = isset( $feed['meta']['set_invoice_status'] )          ? $feed['meta']['set_invoice_status']          : false;
		$send_to_client     = isset( $feed['meta']['send_to_client'] )              ? $feed['meta']['send_to_client']              : false;
		
		$post_array = array(
			'post_content' => '',
			'post_title'   => $this->get_field_value( $form, $entry, $mapped_title ),
			'post_status'  => 'publish',
			'post_type'    => 'sliced_' . $post_type,
		);

		// insert the post
		$id = wp_insert_post( $post_array, $wp_error = false );

		// set status
		$status = 'draft'; // default
		$taxonomy = $post_type . '_status';
		if ( $post_type === 'quote' && $set_quote_status ) {
			$status = $set_quote_status;
		} elseif ( $post_type === 'invoice' && $set_invoice_status ) {
			$status = $set_invoice_status;
		}
		wp_set_object_terms( $id, array( $status ), $taxonomy );

		/*
		 * add the postmetas
		 */
		// invoice/quote number
		if ( $post_type === 'invoice' ) {
			$prefix = sliced_get_invoice_prefix();
			$suffix = sliced_get_invoice_suffix();
			$number = $this->get_field_value( $form, $entry, $mapped_inv_num ) > '' ? $this->get_field_value( $form, $entry, $mapped_inv_num ) : sliced_get_next_invoice_number();
		} else {
			$prefix = sliced_get_quote_prefix();
			$suffix = sliced_get_quote_suffix();
			$number = $this->get_field_value( $form, $entry, $mapped_quote_num ) > '' ? $this->get_field_value( $form, $entry, $mapped_quote_num ) : sliced_get_next_quote_number();
		}
		update_post_meta( $id, '_sliced_description', wp_kses_post( $this->get_field_value( $form, $entry, $mapped_desc ) ) );
		update_post_meta( $id, '_sliced_' . $post_type . '_created', time() );
		update_post_meta( $id, '_sliced_' . $post_type . '_prefix', esc_html( $prefix ) );
		update_post_meta( $id, '_sliced_' . $post_type . '_number', esc_html( $number ) );
		update_post_meta( $id, '_sliced_' . $post_type . '_suffix', $suffix );
		update_post_meta( $id, '_sliced_number', $prefix . $number . $suffix );
		update_post_meta( $id, '_sliced_order_number', esc_html( $this->get_field_value( $form, $entry, $mapped_order_num ) ) );
		
		// update quote/invoice numbers for next time
		if ( $post_type === 'invoice' ) {
			Sliced_Invoice::update_invoice_number( $id );
		} else {
			Sliced_Quote::update_quote_number( $id );
		}
		
		// tax
		$tax = get_option( 'sliced_tax' );
		update_post_meta( $id, '_sliced_tax_calc_method', Sliced_Shared::get_tax_calc_method( $id ) );
		update_post_meta( $id, '_sliced_tax', sliced_get_tax_amount_formatted( $id ) );
		update_post_meta( $id, '_sliced_tax_name', sliced_get_tax_name( $id ) );
		update_post_meta( $id, '_sliced_additional_tax_name', isset( $tax['sliced_additional_tax_name'] ) ? $tax['sliced_additional_tax_name'] : '' );
		update_post_meta( $id, '_sliced_additional_tax_rate', isset( $tax['sliced_additional_tax_rate'] ) ? $tax['sliced_additional_tax_rate'] : '' );
		update_post_meta( $id, '_sliced_additional_tax_type', isset( $tax['sliced_additional_tax_type'] ) ? $tax['sliced_additional_tax_type'] : '' );
		
		// payment methods
		if ( $post_type === 'invoice' && function_exists( 'sliced_get_accepted_payment_methods' ) ) {
			$payment = sliced_get_accepted_payment_methods();
			update_post_meta( $id, '_sliced_payment_methods', array_keys($payment) );
		}
		
		// terms
		$terms = false;
		if ( $post_type === 'invoice' ) {
			$invoices = get_option( 'sliced_invoices' );
			$terms    = isset( $invoices['terms'] ) ? $invoices['terms'] : '';
		} elseif ( $post_type === 'quote' ) {
			$quotes   = get_option( 'sliced_quotes' );
			$terms    = isset( $quotes['terms'] ) ? $quotes['terms'] : '';
		}
		if ( $terms ) {
			update_post_meta( $id, '_sliced_' . $post_type . '_terms', $terms );
		}

		/*
		 * add the line items
		 */
		if ( $use_product_fields ) {
		
			$products = GFCommon::get_product_fields( $form, $entry );
			if ( ! empty( $products['products'] ) ) {
				
				$line_items = array();
				foreach ( $products['products'] as $product ) {
					$options = array();
					if ( is_array( rgar( $product, 'options' ) ) ) {
						foreach ( $product['options'] as $option ) {
							$options[] = $option['option_name'];
						}
					}
					$description = '';
					if ( ! empty( $options ) ) {
						$description = esc_html__( 'options: ', 'sliced-invoices-gravity-forms' ) . ' ' . implode( ', ', $options );
					}
					$line_items[] = array(
						'qty'            => esc_html( rgar( $product, 'quantity' ) ),
						'title'          => esc_html( rgar( $product, 'name' ) ),
						'description'    => wp_kses_post( $description ),
						'amount'         => Sliced_Shared::get_formatted_number( GFCommon::to_number( rgar( $product, 'price', 0 ), $entry['currency'] ) ),
						'taxable'        => 'on',
						'second_taxable' => 'on',
					);
				}
				if ( ! empty( $products['shipping']['name'] ) ) {
					$line_items[] = array(
						'qty'         => 1,
						'title'       => esc_html( $products['shipping']['name'] ),
						'description' => '',
						'amount'      => Sliced_Shared::get_formatted_number( GFCommon::to_number( rgar( $products['shipping'], 'price', 0 ), $entry['currency'] ) ),
					);
				}
				if ( ! empty( $line_items ) ) {
					update_post_meta( $id, '_sliced_items', apply_filters( 'sliced_gravityforms_line_items', $line_items ) );
				}
				
			}
			
		} elseif ( ! empty( $mapped_line_items ) ) {
		
			// loop through the line items and put into our format
			$items       = unserialize( rgar( $entry, $mapped_line_items ) ); // unserialize the array
			$items_array = array();
			if ( $items ) {
				foreach ( $items as $index => $item ) {
					$item = array_values( $item ); // change to indexed array to allow different naming conventions of items
					$exploded_item = explode( '|', $item[1] );
					if ( count( $exploded_item ) > 1 ) {
						$title = $exploded_item[1]; // must be from dynamically populated drop down
					} else {
						$title = $exploded_item[0]; // must be from a plain text field
					}
					$items_array[] = array(
						'qty'            => $item[0] ? esc_html( $item[0] ) : '',
						'title'          => esc_html( trim( $title ) ),
						'description'    => $item[3] ? wp_kses_post( $item[3] ) : '',
						'amount'         => $item[2] ? esc_html( $item[2] ) : '',
						'taxable'        => 'on',
						'second_taxable' => 'on',
					);
				}
			}
			update_post_meta( $id, '_sliced_items', apply_filters( 'sliced_gravityforms_line_items', $items_array ) );
			
		}

		// put the client details into array
		$client_array = array(
			'name'       => $this->get_field_value( $form, $entry, $mapped_name ),
			'email'      => $this->get_field_value( $form, $entry, $mapped_email ),
			'business'   => $this->get_field_value( $form, $entry, $mapped_business ),
			'address'    => $this->get_field_value( $form, $entry, $mapped_address ),
			'extra_info' => $this->get_field_value( $form, $entry, $mapped_extra ),
			'post_id'    => $id,

		);

		$client_id = $this->maybe_add_client( $client_array );
		
		if ( $send_to_client === 'yes' ) {
			$send = new Sliced_Notifications;
			if ( $post_type === 'invoice' ) {
				$send->send_the_invoice( $id );
			} else {
				$send->send_the_quote( $id );
			}
			// set the status again, because send_the_invoice() and send_the_quote() change the status to "sent"
			wp_set_object_terms( $id, array( $status ), $taxonomy );
		}
		
		do_action( 'sliced_gravityforms_feed_processed', $id, $feed, $entry, $form );

	}

	/**
	 * Returns the combined value of the specified Address field.
	 *
	 * @param array $entry The entry currently being processed.
	 * @param string $field_id The Address field ID.
	 *
	 * @return string
	 */
	public function get_full_address( $entry, $field_id ) {
		$street  = str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.1' ) ) );
		$street2 = str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.2' ) ) );
		$city    = str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.3' ) ) );
		$state   = str_replace( '  ', ' ', trim( rgar( $entry, $field_id . '.4' ) ) );
		$zip     = trim( rgar( $entry, $field_id . '.5' ) );
		$country = trim( rgar( $entry, $field_id . '.6' ) );
		$address = '';

		$form  = GFFormsModel::get_form_meta( $entry['form_id'] );
		$field = GFFormsModel::get_field( $form, $field_id );

		switch ( $field->addressType ) {
			case 'international':
				if ( ! empty( $street ) ) {
					$address .= $street . '<br>';
				}
				if ( ! empty( $street2 ) ) {
					$address .= $street2 . '<br>';
				}
				if ( ! empty( $city ) ) {
					$address .= $city . ', ';
				}
				if ( ! empty( $state ) ) {
					$address .= $state . ' ';
				}
				if ( ! empty( $zip ) ) {
					$address .= $zip . '<br>';
				}
				if ( ! empty( $country ) ) {
					$address .= $country;
				}
				break;
			case 'us':
				if ( ! empty( $street ) ) {
					$address .= $street . '<br>';
				}
				if ( ! empty( $street2 ) ) {
					$address .= $street2 . '<br>';
				}
				if ( ! empty( $city ) ) {
					$address .= $city . ', ';
				}
				if ( ! empty( $state ) ) {
					$address .= $state . ' ';
				}
				if ( ! empty( $zip ) ) {
					$address .= $zip . '<br>';
				}
				break;
			case 'canadian':
				if ( ! empty( $street ) ) {
					$address .= $street . '<br>';
				}
				if ( ! empty( $street2 ) ) {
					$address .= $street2 . '<br>';
				}
				if ( ! empty( $city ) ) {
					$address .= $city . ', ';
				}
				if ( ! empty( $state ) ) {
					$address .= $state . ' ';
				}
				if ( ! empty( $zip ) ) {
					$address .= $zip . '<br>';
				}
				break;

			default:
				if ( ! empty( $street ) ) {
					$address .= $street . '<br>';
				}
				if ( ! empty( $street2 ) ) {
					$address .= $street2 . '<br>';
				}
				if ( ! empty( $city ) ) {
					$address .= $city . ', ';
				}
				if ( ! empty( $state ) ) {
					$address .= $state . ' ';
				}
				if ( ! empty( $zip ) ) {
					$address .= $zip . '<br>';
				}
				if ( ! empty( $country ) ) {
					$address .= $country;
				}
				break;
		}

		return $address;
	}


	/**
	 * Check for existing client and add new one if does not exist.
	 * @since  1.0.0
	 */
	public function maybe_add_client( $client_array ) {

		$client_id = null;
		
		if ( ! email_exists( $client_array['email'] ) ) {
			/* if client does not exist, create one */
			
			// get the name
			if ( ! empty( $client_array['name'] ) ) {
				$name = esc_html( $client_array['name'] );
			} else {
				$name = 'sliced_client_' . wp_generate_password( 8, false ); // just in case
			}
			
			// make sure $user_name is unique
			$user_name = $name;
			$index = 0;
			while ( username_exists( $user_name ) ) {
				$index++;
				$user_name = $name . '_' . $index;
			}
			
			// generate random password
			$password = wp_generate_password( 10, true, true );
			
			// get the business name
			$business = ! empty( $client_array['business'] ) ? esc_html( $client_array['business'] ) : $name;

			// create the user
			$client_id = wp_create_user( $user_name, $password, sanitize_email( $client_array['email'] ) );

			// add the user meta
			add_user_meta( $client_id, '_sliced_client_business', wp_kses_post( $business ) );
			add_user_meta( $client_id, '_sliced_client_address', wp_kses_post( $client_array['address'] ) );
			add_user_meta( $client_id, '_sliced_client_extra_info', wp_kses_post( $client_array['extra_info'] ) );

		} else {
			// get the user
			$client    = get_user_by( 'email', $client_array['email'] );
			$client_id = $client->ID;
		}

		if ( ! is_wp_error( $client_id ) ) {
			// add the user to the post
			update_post_meta( $client_array['post_id'], '_sliced_client', (int) $client_id );
		}

		return $client_id;

	}
	

	/**
	 * Target for the after_plugin_row action hook. Checks if Sliced Invoices is active and whether the current version of Gravity Forms
	 * is supported and outputs a message just below the plugin info on the plugins page.
	 */
	public function plugin_row( $plugin_name = '', $plugin_data = array() ) {
		if ( ! self::is_gravityforms_supported( $this->_min_gravityforms_version ) ) {
			$message = $this->plugin_message();
			self::display_plugin_message( $message, true );
		}
	}

	/**
	 * Checks whether Sliced Invoices is active and that the current version of Gravity Forms is supported.
	 *
	 * @param string $min_gravityforms_version
	 *
	 * @return bool
	 */
	public function is_gravityforms_supported( $min_gravityforms_version = '' ) {
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			return false;
		}
		
		return parent::is_gravityforms_supported( $min_gravityforms_version );
	}


	/**
	 * Returns the message that will be displayed if the current version of Gravity Forms is not supported or Sliced Invoices is not active.
	 *
	 * @return string
	 */
	public function plugin_message() {
		if ( ! class_exists( 'Sliced_Invoices' ) ) {
			return sprintf( esc_html__( '%sSliced Invoices%s is required.', 'sliced-invoices-gravity-forms' ), "<a href='https://slicedinvoices.com/'>", '</a>' );
		}

		return parent::plugin_message();
	}

}