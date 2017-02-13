<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

GFForms::include_feed_addon_framework();

class Sliced_Invoices_GF extends GFFeedAddOn {

	protected $_version = '1.08';
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
								'required' => 1
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
								'required' => 0
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

		$post_type         = strtolower( $feed['meta']['post_type'] );
		$mapped_email      = $feed['meta']['mappedFields_email'];
		$mapped_name       = $feed['meta']['mappedFields_name'];
		$mapped_business   = $feed['meta']['mappedFields_business'];
		$mapped_address    = $feed['meta']['mappedFields_address'];
		$mapped_extra      = $feed['meta']['mappedFields_extra_info'];
		$mapped_title      = $feed['meta']['mappedFields_title'];
		$mapped_desc       = $feed['meta']['mappedFields_description'];
		$mapped_quote_num  = $feed['meta']['mappedFields_quote_number'];
		$mapped_inv_num    = $feed['meta']['mappedFields_invoice_number'];
		$mapped_order_num  = $feed['meta']['mappedFields_order_number'];
		$mapped_line_items = $feed['meta']['mappedFields_line_items'];

		$post_array = array(
			'post_content' => '',
			'post_title'   => $this->get_field_value( $form, $entry, $mapped_title ),
			'post_status'  => 'publish',
			'post_type'    => 'sliced_' . $post_type,
		);

		// insert the post
		$id = wp_insert_post( $post_array, $wp_error = false );

		// set to a draft
		$taxonomy = $post_type . '_status';
		wp_set_object_terms( $id, array( 'draft' ), $taxonomy );

		// insert the post_meta
		if ( $post_type === 'invoice' ) {
			$prefix = sliced_get_invoice_prefix();
			$number = $this->get_field_value( $form, $entry, $mapped_inv_num ) > '' ? $this->get_field_value( $form, $entry, $mapped_inv_num ) : sliced_get_next_invoice_number();
		} else {
			$prefix = sliced_get_quote_prefix();
			$number = $this->get_field_value( $form, $entry, $mapped_quote_num ) > '' ? $this->get_field_value( $form, $entry, $mapped_quote_num ) : sliced_get_next_quote_number();
		}
		update_post_meta( $id, '_sliced_description', wp_kses_post( $this->get_field_value( $form, $entry, $mapped_desc ) ) );
		update_post_meta( $id, '_sliced_' . $post_type . '_created', time() );
		update_post_meta( $id, '_sliced_' . $post_type . '_prefix', esc_html( $prefix ) );
		update_post_meta( $id, '_sliced_' . $post_type . '_number', esc_html( $number ) );
		update_post_meta( $id, '_sliced_order_number', esc_html( $this->get_field_value( $form, $entry, $mapped_order_num ) ) );
		if ( $post_type === 'invoice' ) {
			Sliced_Invoice::update_invoice_number( $id );
		} else {
			Sliced_Quote::update_quote_number( $id );
		}

		/*
		 * add the line items
		 */
		if ( ! empty( $mapped_line_items ) ) {
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
						'qty'         => $item[0] ? esc_html( $item[0] ) : '',
						'title'       => esc_html( trim( $title ) ),
						'description' => $item[3] ? wp_kses_post( $item[3] ) : '',
						'amount'      => $item[2] ? esc_html( $item[2] ) : '',
					);
				}
			}
			add_post_meta( $id, '_sliced_items', $items_array );
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

		// if client does not exist, create one
		$client_id = null;
		if ( ! email_exists( $client_array['email'] ) ) {

			// generate random password
			$password = wp_generate_password( 10, true, true );
			$name     = ! empty( $client_array['name'] ) ? $client_array['name'] : 'blank_name_' . $password; // just in case
			$business = ! empty( $client_array['business'] ) ? $client_array['business'] : $name;

			// create the user
			$client_id = wp_create_user( esc_html( $name ), esc_html( $password ), sanitize_email( $client_array['email'] ) );

			// add the user meta
			add_user_meta( $client_id, '_sliced_client_business', wp_kses_post( $business ) );
			add_user_meta( $client_id, '_sliced_client_address', wp_kses_post( $client_array['address'] ) );
			add_user_meta( $client_id, '_sliced_client_extra_info', wp_kses_post( $client_array['extra_info'] ) );

		} else {
			// get the user
			$client    = get_user_by( 'email', $client_array['email'] );
			$client_id = $client->ID;
		}

		// add the user to the post
		update_post_meta( $client_array['post_id'], '_sliced_client', (int) $client_id );

		return $client_id;

	}

	/**
	 * Target for the after_plugin_row action hook. Checks if Sliced Invoices is active and whether the current version of Gravity Forms
	 * is supported and outputs a message just below the plugin info on the plugins page.
	 */
	public function plugin_row() {
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